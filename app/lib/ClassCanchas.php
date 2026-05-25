<?php

    class Canchas{
        private static function getDefaultProvinceCityIds(){
            $default = query(
                "SELECT c.id AS city_id, c.id_provincia AS province_id
                 FROM city c
                 ORDER BY c.id ASC
                 LIMIT 1",
                ''
            );

            if (!$default) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'No hay provincias/ciudades configuradas para crear canchas'], 500);
            }

            return [
                'id_province' => (int) $default->province_id,
                'id_city' => (int) $default->city_id,
            ];
        }
        private static function resolveLogoPath($logo){
            $defaultLogo = 'assets/img/cancha.png';
            $logo = trim((string) $logo);
            if ($logo === '') return $defaultLogo;

            // URL externa o data-uri
            if (preg_match('/^(https?:)?\/\//i', $logo) || str_starts_with($logo, 'data:')) {
                return $logo;
            }

            // Si ya es asset local, devolver directo
            if (str_starts_with($logo, 'assets/')) {
                return $logo;
            }

            // Normaliza distintos formatos guardados en DB
            $fileName = $logo;
            if (str_starts_with($logo, 'upload/cancha/')) {
                $fileName = substr($logo, strlen('upload/cancha/'));
            } elseif (str_starts_with($logo, 'upload/')) {
                $fileName = basename($logo);
            }

            $fileName = trim((string) $fileName);
            if ($fileName === '') return $defaultLogo;

            $dir = __DIR__ . '/../upload/cancha/';
            $pathRaw = $dir . $fileName;
            $pathDecoded = $dir . urldecode($fileName);

            if (is_file($pathRaw)) {
                return 'upload/cancha/' . $fileName;
            }
            if (is_file($pathDecoded)) {
                return 'upload/cancha/' . urldecode($fileName);
            }

            return $defaultLogo;
        }
        private static function ensurePriceRangesTable(){
            query("CREATE TABLE IF NOT EXISTS price_ranges (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_field INT NOT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                INDEX idx_price_ranges_field_time (id_field, start_time, end_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
        public static function ensurePriceRangesStorage(){
            self::ensurePriceRangesTable();
        }
        private static function canManageField($fieldId){
            $fieldId = (int) $fieldId;
            if ($fieldId <= 0 || empty($_SESSION['canchero'])) return false;

            $user = query("SELECT id_field, rol FROM users WHERE id = ?", '', [$_SESSION['canchero']]);
            if (!$user) return false;
            if (($user->rol ?? '') === 'superAdmin') return true;

            return (int) ($user->id_field ?? 0) === $fieldId;
        }
        public static function getPriceRangesByField($idField){
            self::ensurePriceRangesTable();
            $idField = (int) $idField;
            if ($idField <= 0) return [];

            $ranges = query(
                "SELECT id, id_field, start_time, end_time, price
                 FROM price_ranges
                 WHERE id_field = ?
                 ORDER BY start_time ASC, end_time ASC",
                'ALL',
                [$idField]
            );

            return is_array($ranges) ? $ranges : [];
        }
        public static function savePriceRanges($data){
            self::ensurePriceRangesTable();

            $fieldId = (int) ($data->id_field ?? 0);
            if ($fieldId <= 0) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'Cancha inválida'], 400);
            }
            if (!self::canManageField($fieldId)) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'No tenés permisos para editar estas franjas'], 403);
            }

            $raw = (string) ($data->ranges_json ?? '[]');
            $ranges = json_decode($raw, true);
            if (!is_array($ranges)) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'Formato de franjas inválido'], 400);
            }

            $normalized = [];
            foreach ($ranges as $range) {
                $startRaw = trim((string) ($range['start_time'] ?? ''));
                $endRaw = trim((string) ($range['end_time'] ?? ''));
                $priceRaw = str_replace(',', '.', trim((string) ($range['price'] ?? '0')));

                if (!preg_match('/^\d{2}:\d{2}$/', $startRaw) || !preg_match('/^\d{2}:\d{2}$/', $endRaw)) {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'Cada franja debe tener hora inicio y fin válidas (HH:MM)'], 400);
                }

                $startMin = ((int) substr($startRaw, 0, 2)) * 60 + (int) substr($startRaw, 3, 2);
                $endMin = ((int) substr($endRaw, 0, 2)) * 60 + (int) substr($endRaw, 3, 2);
                if ($startMin >= $endMin) {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'La hora fin debe ser mayor a la hora inicio en cada franja'], 400);
                }

                $price = (float) $priceRaw;
                if ($price <= 0) {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'El precio de cada franja debe ser mayor a 0'], 400);
                }

                $normalized[] = [
                    'start_min' => $startMin,
                    'end_min' => $endMin,
                    'start_time' => $startRaw . ':00',
                    'end_time' => $endRaw . ':00',
                    'price' => number_format($price, 2, '.', ''),
                ];
            }

            usort($normalized, function($a, $b){
                return $a['start_min'] <=> $b['start_min'];
            });

            for ($i = 1; $i < count($normalized); $i++) {
                if ($normalized[$i]['start_min'] < $normalized[$i - 1]['end_min']) {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'Las franjas horarias no pueden superponerse'], 400);
                }
            }

            $pdo = Db::pdo();
            try {
                $pdo->beginTransaction();
                query("DELETE FROM price_ranges WHERE id_field = ?", '', [$fieldId]);
                foreach ($normalized as $range) {
                    query(
                        "INSERT INTO price_ranges (id_field, start_time, end_time, price) VALUES (?, ?, ?, ?)",
                        '',
                        [$fieldId, $range['start_time'], $range['end_time'], $range['price']]
                    );
                }
                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'No se pudieron guardar las franjas horarias'], 500);
            }

            JSON(['success' => true, 'icon' => 'success', 'msg' => 'Franjas horarias guardadas correctamente']);
        }
        public static function getAll(){
            $canchas = query("SELECT id, full_name AS name, phone, latitude, length, logo, price_hour, tax_id FROM soccer_field WHERE status = 1", 'ALL');

            foreach($canchas AS $cancha){
               $cancha->logo = self::resolveLogoPath($cancha->logo ?? '');
            }
            

            return $canchas;
        }
        public static function add($data){
            $id = self::getIdNewCancha();
            $idProvince = (int) ($data->id_province ?? 0);
            $idCity = (int) ($data->id_city ?? 0);
            if ($idProvince <= 0 || $idCity <= 0) {
                $defaults = self::getDefaultProvinceCityIds();
                $idProvince = $defaults['id_province'];
                $idCity = $defaults['id_city'];
            }
            query("INSERT INTO soccer_field
                (full_name, phone, address, latitude, length, price_hour, threshold, id_province, id_city)
                    VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?)", '', [$data->full_name, $data->phone, $data->address, $data->latitude, $data->length, $data->price_hour, $data->limit, $idProvince, $idCity]);
            
            $data->logo = self::setLogo($id);
            JSON(['icon' => 'success', 'msg' => 'Cancha Agregada Correctamente']);
        }
        private static function getIdNewCancha(){
            $result = query("SELECT  AUTO_INCREMENT AS value FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'soccer_field'");
            
            return $result->value;
        }
        public static function getById($id){
            $cancha = query("SELECT id, full_name AS name, latitude, length, logo,  phone, address, price_hour, threshold, id_province, id_city  FROM soccer_field AS f WHERE id = ?", '', [$id]);
            if (!$cancha) {
                return null;
            }
            $cancha->logo = self::resolveLogoPath($cancha->logo ?? '');

            return $cancha;
        }
        public static function edit($data){
            if (empty($data->id)) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'Cancha inválida'], 400);
            }
            if (!self::canManageField($data->id)) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'No tenés permisos para editar esta cancha'], 403);
            }

            $fullName = trim((string) ($data->full_name ?? ''));
            if ($fullName === '') {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'El nombre de la cancha es obligatorio'], 400);
            }

            $phoneDigits = preg_replace('/\D+/', '', (string) ($data->phone ?? ''));
            if (strlen($phoneDigits) < 8) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'Teléfono inválido (mínimo 8 dígitos)'], 400);
            }

            $price = (float) str_replace(',', '.', (string) ($data->price_hour ?? 0));
            if ($price <= 0) {
                JSON(['success' => false, 'icon' => 'error', 'msg' => 'El precio por hora debe ser mayor a 0'], 400);
            }

            self::setLogo($data->id);
            $data->phone = $phoneDigits;
            $data->price_hour = $price;
            foreach($data AS $key => $value){
                if($key != 'id' && $key != 'logo'){
                   query("UPDATE soccer_field SET $key = ? WHERE id = ?", '', [$value, $data->id]); 
                }
            }
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'Guardado correctamente']);
        }

        private static function setLogo($id){
            if(!empty($_FILES['logo']['name'])){
                $file = $_FILES['logo'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                $maxSize = 3 * 1024 * 1024; // 3MB

                if(!in_array($extension, $allowed)){
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'Formato de imagen no permitido. Solo JPG o PNG.'], 400);
                }

                if ((int) ($file['size'] ?? 0) > $maxSize) {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'La imagen supera el tamaño máximo de 3MB'], 400);
                }

                $newName = md5(time() . $file['name']) . '.' . $extension;
                $tmp  = $file['tmp_name'];
                
                // Usar ruta relativa al archivo actual para mayor seguridad
                $dir  = __DIR__ . '/../upload/cancha/';
                
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }

                if(move_uploaded_file($tmp, $dir . $newName)){
                    query("UPDATE soccer_field SET logo = ? WHERE id = ?", '', [$newName, $id]); 
                } else {
                    JSON(['success' => false, 'icon' => 'error', 'msg' => 'No se pudo guardar la imagen en el servidor'], 500);
                }
            }
        }
        public static function getMyCancha(){
            $id = $_SESSION['canchero'];

            $cancha = query("SELECT 
                    f.id,
                    f.logo,
                    f.full_name AS name,
                    f.price_hour,
                    f.phone,
                    f.latitude,
                    f.length,
                    f.address,
                    f.threshold
                FROM users AS u
                INNER JOIN soccer_field AS f 
                    ON u.id_field = f.id
                WHERE u.id = ? LIMIT 1", '', [$id]);
            $cancha->logo = self::resolveLogoPath($cancha->logo ?? '');
            return $cancha;
        }
        public static function deleteCancha($data){
            Users::requireSuperAdmin(true);
            query("UPDATE soccer_field SET status = 0 WHERE id = ?", '', [$data->id]);
            JSON(['success' => true, 'icon' => 'success', 'msg' => 'Cancha eliminada']);
        }
        public static function getByIdUser(){
            $id_user = $_SESSION['canchero'];
            $user = query("SELECT id_field, rol FROM users WHERE id = ?", '', [$id_user]);
            
            if ($user->rol == 'superAdmin') {
                return query("SELECT id, full_name AS name FROM soccer_field WHERE status = 1", 'ALL');
            }

            // Obtener el establishment_id de la cancha del usuario
            $field = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
            $estId = $field->establishment_id ?? 0;

            if ($estId > 0) {
                // Devolver todas las canchas del mismo establecimiento
                return query("SELECT id, full_name AS name FROM soccer_field WHERE establishment_id = ? AND status = 1", 'ALL', [$estId]);
            } else {
                // Fallback a solo su cancha si no hay establishment_id
                return query("SELECT id, full_name AS name FROM soccer_field WHERE id = ? AND status = 1", 'ALL', [$user->id_field]);
            }
        }
        public static function getBookingFieldSummariesByUser(){
            if (empty($_SESSION['canchero'])) {
                return [];
            }

            $idUser = (int) $_SESSION['canchero'];
            $user = query("SELECT id_field, rol FROM users WHERE id = ?", '', [$idUser]);
            if (!$user) {
                return [];
            }

            if (($user->rol ?? '') === 'superAdmin') {
                $fields = query(
                    "SELECT id,
                            full_name AS name,
                            COALESCE(NULLIF(threshold, 0), 1) AS threshold
                       FROM soccer_field
                      WHERE status = 1
                      ORDER BY id ASC",
                    'ALL'
                );
            } else {
                $field = query("SELECT establishment_id FROM soccer_field WHERE id = ?", '', [$user->id_field]);
                $estId = (int) ($field->establishment_id ?? 0);

                if ($estId > 0) {
                    $fields = query(
                        "SELECT id,
                                full_name AS name,
                                COALESCE(NULLIF(threshold, 0), 1) AS threshold
                           FROM soccer_field
                          WHERE establishment_id = ?
                            AND status = 1
                          ORDER BY id ASC",
                        'ALL',
                        [$estId]
                    );
                } else {
                    $fields = query(
                        "SELECT id,
                                full_name AS name,
                                COALESCE(NULLIF(threshold, 0), 1) AS threshold
                           FROM soccer_field
                          WHERE id = ?
                            AND status = 1
                          ORDER BY id ASC",
                        'ALL',
                        [$user->id_field]
                    );
                }
            }

            if (!is_array($fields)) {
                return [];
            }

            foreach ($fields as $fieldItem) {
                $fieldItem->threshold = max(1, (int) ($fieldItem->threshold ?? 1));
            }

            return $fields;
        }
    }
