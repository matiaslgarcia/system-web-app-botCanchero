<?php

    class Users{
        private static function resolveAvatarPath($avatar, $default = 'assets/img/avatars/blank.png'){
            $avatar = trim((string) $avatar);
            if ($avatar === '') return $default;

            // Already absolute URL or data URI
            if (preg_match('/^(https?:)?\/\//i', $avatar) || str_starts_with($avatar, 'data:')) {
                return $avatar;
            }

            // Already app-relative asset
            if (str_starts_with($avatar, 'assets/')) {
                return $avatar;
            }

            // upload path persisted in DB
            if (str_starts_with($avatar, 'upload/')) {
                $relative = ltrim($avatar, '/');
                $absPath = __DIR__ . '/../' . $relative;
                if (is_file($absPath)) {
                    return $relative;
                }
                $decoded = urldecode($relative);
                $absPathDecoded = __DIR__ . '/../' . $decoded;
                if (is_file($absPathDecoded)) {
                    return $decoded;
                }
                return $default;
            }

            // Legacy DB format storing only filename
            $candidate = 'upload/avatar/' . $avatar;
            $candidatePath = __DIR__ . '/../' . $candidate;
            if (is_file($candidatePath)) {
                return $candidate;
            }
            $decoded = 'upload/avatar/' . urldecode($avatar);
            $decodedPath = __DIR__ . '/../' . $decoded;
            if (is_file($decodedPath)) {
                return $decoded;
            }
            return $default;
        }
        public static function loginCheck($arr = []){
            if (session_status() === PHP_SESSION_NONE) session_start();
            if(!isset($_SESSION['canchero'])){
                self::showLogin($arr);
                die();
            }
        }

        public static function openSession($id){
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['canchero'] = $id;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        public static function getCsrfToken(){
            if (session_status() === PHP_SESSION_NONE) session_start();
            return $_SESSION['csrf_token'] ?? '';
        }
        public static function isSuperAdmin(){
            return self::infoUser('rol') === 'superAdmin';
        }
        public static function requireSuperAdmin($asJson = false){
            if (self::isSuperAdmin()) return;
            if ($asJson) {
                JSON(['error' => 'No tenés permiso para esta acción'], 403, true);
            }
            header('Location: ' . URL);
            die();
        }

        public static function login($email, $password){
            $user = query("SELECT * FROM users WHERE email = ? LIMIT 1;", '', [$email]);
            if($user){
                // PHP-B5: Throttle login (Max 5 attempts, 15 min lockout)
                if (isset($user->login_attempts) && $user->login_attempts >= 5) {
                    $last = strtotime($user->last_attempt ?? '2000-01-01');
                    if (time() - $last < 900) {
                        audit('login_lockout', 'user', $user->id, ['email' => $email]);
                        JSON(['login_fail' => true, 'msg' => 'Demasiados intentos fallidos. Bloqueado por 15 minutos.', 'icon' => 'error'], 403);
                    } else {
                        query("UPDATE users SET login_attempts = 0 WHERE id = ?", '', [$user->id]);
                    }
                }

                if($user->status == 1){
                    if(password_verify($password, $user->password)){
                        query("UPDATE users SET login_attempts = 0 WHERE id = ?", '', [$user->id]);
                        self::openSession($user->id);
                        audit('login_success', 'user', $user->id);
                        JSON(['success' => true]);
                    }else{
                        query("UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = ?", '', [$user->id]);
                        audit('login_fail', 'user', $user->id, ['email' => $email]);
                        self::loginFail();
                    }
                }else{
                    JSON(['login_fail' => true, 'msg' => 'Su cuenta se encuentra inactiva favor contacte a soporte', 'icon' => 'error'], 400);
                }
                
            }else{
                self::loginFail();
            }
        }
        public static function out(){
            if (session_status() === PHP_SESSION_NONE) session_start();
            session_destroy();
            header('Location: ./');

        }
        public static function infoUser($param){
            // PHP-B11: bug latente — query devuelve array assoc pero abajo usaba object access.
            $id = $_SESSION['canchero'] ?? null;
            if (!$id) return null;
            $user = query(
                "SELECT u.id, u.full_name AS name, u.email, u.avatar, u.rol, u.id_field AS id_field,
                        RIGHT(s.token_mercadopago, 5) AS token_id
                   FROM users u
                   LEFT JOIN soccer_field s ON u.id_field = s.id
                  WHERE u.id = ? LIMIT 1",
                'ARRAY',
                [$id]
            );
            if (!$user) return null;
            $user['token_id'] = !empty($user['token_id']) ? '******' . $user['token_id'] : '';
            $user['avatar']   = self::resolveAvatarPath($user['avatar'] ?? '', 'assets/img/avatars/avatar.png');
            return $user[$param] ?? null;
        }
        public static function edit($data){
            if (!isset($_SESSION['canchero'])) {
                JSON(['error' => 'Sesión inválida'], 401, true);
            }
            $sessionId = (int) $_SESSION['canchero'];
            $targetId = (int) ($data->id ?? 0);
            
            if ($targetId === 0) {
                JSON(['error' => 'ID de usuario no proporcionado'], 400, true);
            }
            $sessionUser = self::getById($sessionId);
            $isSuperAdmin = ($sessionUser && ($sessionUser->rol ?? '') === 'superAdmin');

            // Seguridad: Solo el propio usuario o admin puede editar
            if ($sessionId !== $targetId) {
                if (!$isSuperAdmin) {
                    JSON(['error' => 'No tenés permiso para editar este usuario'], 403, true);
                }
            }
            $targetUser = self::getById($targetId);
            if (!$targetUser) {
                JSON(['error' => 'Usuario no encontrado'], 404, true);
            }

            // Procesar Avatar
            self::uploadAvatarUser($targetId);

            // Actualizar campos permitidos
            $full_name = trim((string) ($data->full_name ?? ''));
            $phone = trim((string) ($data->phone ?? ''));
            $email = trim((string) ($data->email ?? ''));

            if (empty($full_name) || empty($email)) {
                JSON(['error' => 'Nombre y Email son obligatorios'], 400, true);
            }

            if ($isSuperAdmin) {
                $allowedRoles = ['canchero', 'superAdmin'];
                $rol = trim((string) ($data->rol ?? $targetUser->rol ?? 'canchero'));
                if (!in_array($rol, $allowedRoles, true)) {
                    JSON(['error' => 'Rol inválido'], 400, true);
                }
                $idField = (int) ($data->id_field ?? $targetUser->id_field ?? 0);
                if ($idField <= 0) {
                    JSON(['error' => 'Cancha inválida para el usuario'], 400, true);
                }
                query(
                    "UPDATE users SET full_name = ?, phone = ?, email = ?, rol = ?, id_field = ? WHERE id = ?",
                    '',
                    [$full_name, $phone, $email, $rol, $idField, $targetId]
                );
            } else {
                query(
                    "UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?",
                    '',
                    [$full_name, $phone, $email, $targetId]
                );
            }
            
            JSON([
                'success' => true, 
                'icon' => 'success', 
                'msg' => 'Perfil actualizado correctamente'
            ]);
        }
        public static function getById($id){
            $user = query("SELECT id, full_name AS name, email, avatar, rol, phone, id_field FROM users WHERE id = ?;", '', [$id]);
            if (!$user) {
                return null;
            }
            $user->avatar = self::resolveAvatarPath($user->avatar ?? '', 'assets/img/avatars/blank.png');
            return $user;
        }
        public static function add($user){
            $user->CanchaAsignada = (empty($user->CanchaAsignada)) ? '0' : $user->CanchaAsignada;
            $fullName = trim((string) ($user->full_name ?? ''));
            $phone = trim((string) ($user->phone ?? ''));
            $email = trim((string) ($user->email ?? ''));
            $idField = (int) ($user->id_field ?? 0);
            $role = trim((string) ($user->rol ?? 'canchero'));
            $passwordRaw = (string) ($user->password ?? '');
            $allowedRoles = ['canchero', 'superAdmin'];

            if ($fullName === '' || $email === '' || $passwordRaw === '' || $idField <= 0 || !in_array($role, $allowedRoles, true)) {
                JSON(['add_fail' => true, 'icon' => 'error', 'msg' => 'Datos de usuario inválidos'], 400, true);
            }
            $user->password = self::emcrytePassword($passwordRaw);

            query("INSERT INTO users
            (full_name, phone, id_field, password, email, rol)
                VALUES
            (?, ?, ?, ?, ?, ?)", '', [$fullName, $phone, $idField, $user->password, $email, $role]);
            $newUserId = (int) Db::pdo()->lastInsertId();
            if ($newUserId <= 0) {
                JSON(['add_fail' => true, 'icon' => 'error', 'msg' => 'No se pudo crear el usuario'], 500, true);
            }
            self::uploadAvatarUser($newUserId);

            JSON(['success' => true, 'succes' => true, 'user_id' => $newUserId]);
        }
        public static function getAll(){
            $users = query("SELECT u.id, u.full_name AS name, u.avatar, f.full_name AS cancha, f.id as id_cancha FROM users as u INNER JOIN soccer_field AS f ON f.id = u.id_field WHERE u.full_name != 'botCanchero' ", 'all');

            foreach($users as $user){
                $user->avatar = self::resolveAvatarPath($user->avatar ?? '', 'assets/img/avatars/avatar.png');
            }
            
            return $users;
        }
        public static function delete($data){
            self::requireSuperAdmin(true);
            audit('user_delete', 'user', $data->id);
            query("DELETE FROM users WHERE id = ?", '', [$data->id]);
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'Usuario eliminado']);
        }
        private static function showLogin($arr = []){
            $base = (isset($arr['base'])) ? $arr['base'] : '';
            Theme::header([
                'title' => 'Login',
                'css'   => [
                    'plugins.bundle',
                    'style.bundle',
                    'FontAwesome',
                    'theme'
                ],
                'base' => $base
            ]);
            inc('login');
            Theme::footer([
                'js' => [
                    'plugins.bundle',
                    'scripts.bundle'
                ],
                'dataJS' => ['login'],
                'base' => $base
            ]);
        }
        private static function loginFail(){
            JSON(['login_fail' => true, 'msg' => 'Email o contraseña incorrecta', 'icon' => 'error'], 400);
        }
        private static function emcrytePassword($password){
            return password_hash($password, PASSWORD_BCRYPT);
        }
        private static function uploadAvatarUser($id){
            if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0){
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $newName = $id . '_' . time() . '.' . $ext;
                
                $dir = __DIR__ . '/../upload/avatar/';
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        error_log("No se pudo crear el directorio: $dir");
                        return false;
                    }
                }

                if(move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $newName)){
                    query("UPDATE users SET avatar = ? WHERE id = ?", '', [$newName, $id]);
                    return true;
                } else {
                    error_log("Error al mover el archivo a: " . $dir . $newName);
                }
            }
            return false;
        }
        public static function changePassword($password, $id){
            if (!isset($_SESSION['canchero'])) {
                JSON(['error' => 'Sesión inválida'], 401, true);
            }
            $sessionId = (int) $_SESSION['canchero'];
            $targetId = (int) $id;
            $isOwnPassword = $sessionId === $targetId;
            if (!$isOwnPassword && !self::isSuperAdmin()) {
                JSON(['error' => 'No tenés permiso para cambiar esta contraseña'], 403, true);
            }
            if (strlen(trim((string) $password)) < 6) {
                JSON(['error' => 'La contraseña debe tener al menos 6 caracteres'], 400, true);
            }
            $password = self::emcrytePassword($password);
            query("UPDATE users SET password = ? WHERE id = ?", '', [$password, $targetId]);
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'Contraseña actualizada']);
        }
        public static function validateByEmail($email){
            $result = query("SELECT * FROM users WHERE email = ?", '', [$email]);
            
            if($result){
                return true;
            }else{
                return false;
            }
        }
    }
