<?php

    class Customers{
        private static $tableExistsCache = [];
        private static $columnExistsCache = [];
        private static $privacyReadyCache = null;
        private static $privacyScopeReadyCache = null;

        private static function tableExists($tableName){
            $tableName = trim((string) $tableName);
            if ($tableName === '') return false;
            if (array_key_exists($tableName, self::$tableExistsCache)) {
                return self::$tableExistsCache[$tableName];
            }
            $row = query(
                "SELECT 1
                   FROM information_schema.tables
                  WHERE table_schema = DATABASE()
                    AND table_name = ?
                  LIMIT 1",
                'ARRAY',
                [$tableName]
            );
            self::$tableExistsCache[$tableName] = !empty($row);
            return self::$tableExistsCache[$tableName];
        }

        private static function columnExists($tableName, $columnName){
            $tableName = trim((string) $tableName);
            $columnName = trim((string) $columnName);
            if ($tableName === '' || $columnName === '') return false;
            $cacheKey = $tableName . '.' . $columnName;
            if (array_key_exists($cacheKey, self::$columnExistsCache)) {
                return self::$columnExistsCache[$cacheKey];
            }
            $row = query(
                "SELECT 1
                   FROM information_schema.columns
                  WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND column_name = ?
                  LIMIT 1",
                'ARRAY',
                [$tableName, $columnName]
            );
            self::$columnExistsCache[$cacheKey] = !empty($row);
            return self::$columnExistsCache[$cacheKey];
        }

        public static function isPrivacyInfrastructureReady(){
            if (self::$privacyReadyCache !== null) {
                return self::$privacyReadyCache;
            }
            self::$privacyReadyCache =
                self::tableExists('customers')
                && self::columnExists('customers', 'privacy_status')
                && self::columnExists('customers', 'anonymized_at')
                && self::columnExists('customers', 'anonymized_by_user_id')
                && self::columnExists('customers', 'anonymization_reason');
            return self::$privacyReadyCache;
        }

        public static function isPrivacyScopeInfrastructureReady(){
            if (self::$privacyScopeReadyCache !== null) {
                return self::$privacyScopeReadyCache;
            }
            self::$privacyScopeReadyCache =
                self::tableExists('customer_privacy_scope')
                && self::columnExists('customer_privacy_scope', 'customer_id')
                && self::columnExists('customer_privacy_scope', 'establishment_id')
                && self::columnExists('customer_privacy_scope', 'status')
                && self::columnExists('customer_privacy_scope', 'anonymized_at')
                && self::columnExists('customer_privacy_scope', 'anonymized_by_user_id')
                && self::columnExists('customer_privacy_scope', 'reason');
            return self::$privacyScopeReadyCache;
        }

        public static function getGlobalActiveWhereClause($alias = 'customers'){
            $alias = trim((string) $alias);
            if ($alias === '') $alias = 'customers';
            if (!self::isPrivacyInfrastructureReady()) {
                return '1=1';
            }
            return "COALESCE({$alias}.privacy_status, 'active') = 'active'";
        }

        public static function getActiveWhereClause($customerAlias = 'customers', $establishmentAlias = ''){
            $customerAlias = trim((string) $customerAlias);
            if ($customerAlias === '') $customerAlias = 'customers';

            $clauses = [self::getGlobalActiveWhereClause($customerAlias)];

            $establishmentAlias = trim((string) $establishmentAlias);
            if ($establishmentAlias !== '' && self::isPrivacyScopeInfrastructureReady()) {
                $clauses[] = "NOT EXISTS (
                    SELECT 1
                      FROM customer_privacy_scope cps
                     WHERE cps.customer_id = {$customerAlias}.id
                       AND cps.establishment_id = {$establishmentAlias}.establishment_id
                       AND cps.status IN ('inactive', 'anonymized')
                )";
            }

            return implode(' AND ', $clauses);
        }

        public static function getNotAnonymizedWhereClause($customerAlias = 'customers', $establishmentAlias = ''){
            $customerAlias = trim((string) $customerAlias);
            if ($customerAlias === '') $customerAlias = 'customers';

            $clauses = [self::getGlobalActiveWhereClause($customerAlias)];

            $establishmentAlias = trim((string) $establishmentAlias);
            if ($establishmentAlias !== '' && self::isPrivacyScopeInfrastructureReady()) {
                $clauses[] = "NOT EXISTS (
                    SELECT 1
                      FROM customer_privacy_scope cps
                     WHERE cps.customer_id = {$customerAlias}.id
                       AND cps.establishment_id = {$establishmentAlias}.establishment_id
                       AND cps.status = 'anonymized'
                )";
            }

            return implode(' AND ', $clauses);
        }

        public static function getScopeStatus($customerId, $establishmentId = 0){
            $customerId = (int) $customerId;
            $establishmentId = (int) $establishmentId;
            if ($customerId <= 0) return 'active';

            if (self::isPrivacyInfrastructureReady()) {
                $row = query(
                    "SELECT privacy_status
                       FROM customers
                      WHERE id = ?
                      LIMIT 1",
                    'ARRAY',
                    [$customerId]
                );
                if (($row['privacy_status'] ?? 'active') === 'anonymized') {
                    return 'anonymized';
                }
            }

            if ($establishmentId > 0 && self::isPrivacyScopeInfrastructureReady()) {
                $scopeRow = query(
                    "SELECT status
                       FROM customer_privacy_scope
                      WHERE customer_id = ?
                        AND establishment_id = ?
                      LIMIT 1",
                    'ARRAY',
                    [$customerId, $establishmentId]
                );
                $status = (string) ($scopeRow['status'] ?? 'active');
                if (in_array($status, ['inactive', 'anonymized'], true)) {
                    return $status;
                }
            }

            return 'active';
        }

        public static function isAnonymized($customerId, $establishmentId = 0){
            return self::getScopeStatus($customerId, $establishmentId) === 'anonymized';
        }

        public static function isInactive($customerId, $establishmentId = 0){
            return self::getScopeStatus($customerId, $establishmentId) === 'inactive';
        }

        public static function buildAnonymizedIdentity($customerId){
            $customerId = max(1, (int) $customerId);
            return [
                'full_name' => 'Cliente anonimizado #' . $customerId,
                'phone' => 'anon-' . $customerId,
                'email' => 'anon+' . $customerId . '@privacy.local',
            ];
        }

        public static function checkExitCustomerOCreate($data){
            $result = self::getByNumeroTelefono($data->phone);

            if($result){
                self::update($data, $result->id);
                $customer = $result;
            }else{
                $customer = self::add($data);
            }

            return $customer;
        }
        private static function update($data, $id){
            $email = $data->email ?? '';
            query("UPDATE customers SET full_name = ?, phone = ?, email = ? WHERE id = ?", '', [$data->full_name, $data->phone, $email, $id]);
        }
        public static function getByNumeroTelefono($phone){
            $where = ["phone = ?"];
            if (self::isPrivacyInfrastructureReady()) {
                $where[] = self::getGlobalActiveWhereClause('customers');
            }
            $customer = query("SELECT * FROM customers WHERE " . implode(' AND ', $where) . " LIMIT 1", '', [$phone]);

            return $customer;
        }
        public static function add($data){
            $data->id = self::getIDNewCustomer();
            $email = $data->email ?? '';
            query("INSERT INTO customers(
                    id, full_name, phone, email
                ) VALUES (
                    ?, ?, ?, ?)", '', [$data->id, $data->full_name, $data->phone, $email]);
            return self::getCustomerById($data->id);
        }

        /** Métodos para API v2 **/
        public static function get(){
            $phone = $_GET['phone'] ?? null;
            if (!$phone) {
                Api::ApiError(['error' => 'phone is required'], 400);
            }
            $phone = str_replace(['+','-',' '], '', $phone);
            $customer = self::getByNumeroTelefono($phone);
            if ($customer) {
                JSON($customer);
            } else {
                JSON([], 404);
            }
        }

        public static function register(){
            $data = Api::getData();
            if (!isset($data->name) || !isset($data->phone)) {
                Api::ApiError(['error' => 'name and phone are required'], 400);
            }
            
            $phone = str_replace(['+','-',' '], '', $data->phone);
            $email = $data->email ?? '';
            
            $newCustomer = self::add((object) [
                'full_name' => $data->name,
                'phone'     => $phone,
                'email'     => $email
            ]);
            
            JSON($newCustomer);
        }

        public static function getCustomerById($id){
            $where = ["id = ?"];
            if (self::isPrivacyInfrastructureReady()) {
                $where[] = self::getGlobalActiveWhereClause('customers');
            }
            $customer = query("SELECT * FROM customers WHERE " . implode(' AND ', $where), '', [$id]);
            return $customer;
        }

        private static function getIDNewCustomer(){
            $result = query("SELECT id + 1 AS id FROM customers ORDER BY id DESC LIMIT 1");
            return ($result && isset($result->id)) ? $result->id : 1;
        }

        /**
         * GET /api/v2/?action=customers_listAll
         * Devuelve customers DISTINCT que tienen al menos una reserva en el
         * establishment del token bound. Usado por el cron de notificaciones.
         */
        public static function listAll() {
            $boundEst = class_exists('Auth') ? Auth::getEstablishmentId() : null;
            $params = [];
            $where = ['1=1'];
            if ($boundEst) {
                $where[] = 'sf.establishment_id = :est';
                $params[':est'] = (int) $boundEst;
            }
            if (self::isPrivacyInfrastructureReady()) {
                $where[] = self::getActiveWhereClause('c', 'sf');
            }

            $rows = query(
                "SELECT DISTINCT c.id, c.full_name, c.phone, c.email
                   FROM customers c
                   INNER JOIN booking b ON b.id_customer = c.id
                   INNER JOIN soccer_field sf ON sf.id = b.id_field
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY c.id DESC",
                'ARRAY_ALL',
                $params
            );
            JSON($rows ?: []);
        }
    }
