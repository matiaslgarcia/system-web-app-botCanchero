<?php

    class Payment{    
        private static function getProp($data, $key, $default = null) {
            return (is_object($data) && property_exists($data, $key)) ? $data->{$key} : $default;
        }

        public static function created(){
           $data = Api::getData();
           $data->date_created = date("Y-m-d H:i:s");
           query("INSERT INTO vouchers (
            data_id, id_notification, id_canchero, method_name,  date_create
            ) VALUES (?, ?, ?, ?, ?)", '', [$data->data_id, $data->id_notification, $data->id_canchero, $data->method_name, $data->date_created]);

            JSON(self::getVaucherByDataID($data->data_id));
        }

        private static function  getVaucherByDataID($data_id){
            $vaucher = query("SELECT * FROM vouchers WHERE data_id = ? ORDER BY id DESC LIMIT 1", '', [$data_id]);
            
            return $vaucher;
        }
        public static function getVaucher(){
            $data = Api::getData();
            JSON(self::getVaucherByDataID($data->data_id));
        }
        public static function result(){
            $data = Api::getData();
            query("INSERT INTO payment (
                    payment_id, ip_address,
                    date_created, date_last_updated, expiration_month, expiration_year, first_six_digits, last_four_digits,
                    charges_details_mounts_original, charges_details_mounts_refunded, charges_details_mounts_amounts_original_1,
                    charges_details_mounts_amounts_refunded_1, charges_details_mounts_amounts_original_2, charges_details_mounts_amounts_refunded_2,
                    metadata_mov_detail, metadata_mov_financial_entity, metadata_mov_type, metadata_tax_id, metadata_tax_status,
                    metadata_user_id, metadata_type, coupon_amount, currency_id, date_approved, date_of_expiration, marketplace_owner,
                    notification_url, order_id, order_type, payment_method_id, payment_type_id, statement_descriptor, status, status_detail,
                    taxes_amount, transaction_amount, transaction_amount_refunded, external_reference
                )
                VALUES (
                    ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )", '', [
                    self::getProp($data, 'payment_id'), self::getProp($data, 'ip_address'),
                    self::getProp($data, 'date_created'), self::getProp($data, 'date_last_updated'), self::getProp($data, 'expiration_month'), self::getProp($data, 'expiration_year'), self::getProp($data, 'first_six_digits'), self::getProp($data, 'last_four_digits'),
                    self::getProp($data, 'charges_details_mounts_original'), self::getProp($data, 'charges_details_mounts_refunded'), self::getProp($data, 'charges_details_mounts_amounts_original_1'),
                    self::getProp($data, 'charges_details_mounts_amounts_refunded_1'), self::getProp($data, 'charges_details_mounts_amounts_original_2'), self::getProp($data, 'charges_details_mounts_amounts_refunded_2'),
                    self::getProp($data, 'metadata_mov_detail'), self::getProp($data, 'metadata_mov_financial_entity'), self::getProp($data, 'metadata_mov_type'), self::getProp($data, 'metadata_tax_id'), self::getProp($data, 'metadata_tax_status'),
                    self::getProp($data, 'metadata_user_id'), self::getProp($data, 'metadata_type'), self::getProp($data, 'coupon_amount'), self::getProp($data, 'currency_id'), self::getProp($data, 'date_approved'), self::getProp($data, 'date_of_expiration'), self::getProp($data, 'marketplace_owner'),
                    self::getProp($data, 'notification_url'), self::getProp($data, 'order_id'), self::getProp($data, 'order_type'), self::getProp($data, 'payment_method_id'), self::getProp($data, 'payment_type_id'), self::getProp($data, 'statement_descriptor'), self::getProp($data, 'status'), self::getProp($data, 'status_detail'),
                    self::getProp($data, 'taxes_amount'), self::getProp($data, 'transaction_amount'), self::getProp($data, 'transaction_amount_refunded'), self::getProp($data, 'external_reference')
                ]
            );
            JSON($data);
        }

        
        public static function setPreferencia(){
            $data = Api::getData();

            query("INSERT INTO payment_preference(
                client_id,
                collector_id,
                coupon_code,
                coupon_labels,
                date_created,
                date_of_expiration,
                expiration_date_from,
                expiration_date_to,
                expires,
                external_reference,
                id,
                init_point,
                internal_metadata,
                items_id,
                items_category_id,
                items_currency_id,
                items_description,
                items_picture_url,
                items_title,
                items_quantity,
                items_unit_price,
                marketplace,
                marketplace_fee,
                notification_url,
                operation_type,
                payer_area_code,
                payer_number,
                payer_address_zip_code,
                payer_address_street_name,
                payer_address_street_number,
                email,
                identification_number,
                identification_type,
                name,
                surname,
                last_purchase,
                statement_descriptor,
                total_amount,
                last_updated
             )              VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?
             )", '', [
                $data->client_id, $data->collector_id, $data->coupon_code, $data->coupon_labels, $data->date_created,
                $data->date_of_expiration, $data->expiration_date_from, $data->expiration_date_to, $data->expires, $data->external_reference,
                $data->id, $data->init_point, $data->internal_metadata, $data->items_id, $data->items_category_id,
                $data->items_currency_id, $data->items_description, $data->items_picture_url, $data->items_title, $data->items_quantity,
                $data->items_unit_price, $data->marketplace, $data->marketplace_fee, $data->notification_url, $data->operation_type,
                $data->payer_area_code, $data->payer_number, $data->payer_address_zip_code, $data->payer_address_street_name, $data->payer_address_street_number,
                $data->email, $data->identification_number, $data->identification_type, $data->name, $data->surname,
                $data->last_purchase, $data->statement_descriptor, $data->total_amount, $data->last_updated
             ]);
            JSON($data);
        }
        public static function get(){
            $data = Api::getData();

            $result = query("SELECT * FROM payment WHERE external_reference = ? ORDER BY id DESC LIMIT 1", '', [$data->external_reference]);

            JSON($result);
        }

        /**
         * GET /api/v2/?action=payment_getCredentials
         * Devuelve credenciales MP del establishment bound al token API.
         * Fallback temporal: soccer_field.token_mercadopago.
         */
        public static function getCredentials() {
            $boundEst = Auth::getEstablishmentId();
            if (!$boundEst) {
                Api::ApiError(['error' => 'Unauthorized'], 403);
            }

            $row = query(
                "SELECT
                    e.id AS establishment_id,
                    e.mp_access_token,
                    e.mp_public_key,
                    e.mp_user_id,
                    e.mp_refresh_token,
                    e.mp_token_expires_at,
                    (
                        SELECT sf.token_mercadopago
                          FROM soccer_field sf
                         WHERE sf.establishment_id = e.id
                           AND sf.token_mercadopago IS NOT NULL
                           AND sf.token_mercadopago <> ''
                         ORDER BY sf.id ASC
                         LIMIT 1
                    ) AS fallback_access_token
                 FROM establishment e
                 WHERE e.id = ?
                 LIMIT 1",
                'ARRAY',
                [(int) $boundEst]
            );

            if (!$row) {
                Api::ApiError(['error' => 'Establishment not found'], 404);
            }

            $accessToken = $row['mp_access_token'] ?: $row['fallback_access_token'];
            JSON([
                'establishment_id' => (int) $row['establishment_id'],
                'access_token' => $accessToken ?: null,
                'public_key' => $row['mp_public_key'] ?: null,
                'user_id' => $row['mp_user_id'] ?: null,
                'refresh_token' => $row['mp_refresh_token'] ?: null,
                'token_expires_at' => $row['mp_token_expires_at'] ?: null,
                'source' => !empty($row['mp_access_token']) ? 'establishment' : (!empty($row['fallback_access_token']) ? 'soccer_field_fallback' : 'none')
            ]);
        }

        /**
         * POST /api/v2/?action=payment_registerCash
         * Body: { booking_id, amount, employee_user_id, note }
         *
         * Registra un pago en efectivo en cancha. Suma al paid_amount; si llega
         * al total, marca payment_status='paid' y paid_in_cash_at.
         */
        public static function registerCash() {
            $d = Api::getData();
            $bookingId = (int) $d->booking_id;
            $amount = (float) $d->amount;
            $employee = isset($d->employee_user_id) ? (int) $d->employee_user_id : null;
            $boundEst = Auth::getEstablishmentId();

            if ($bookingId <= 0 || $amount <= 0) {
                Api::ApiError(['error' => 'booking_id y amount son obligatorios y > 0'], 400);
            }

            if ($boundEst) {
                $owner = query(
                    "SELECT sf.establishment_id
                       FROM booking b
                       JOIN soccer_field sf ON sf.id = b.id_field
                      WHERE b.id = ?",
                    "ARRAY",
                    [$bookingId]
                );
                if (!$owner || (int) ($owner['establishment_id'] ?? 0) !== (int) $boundEst) {
                    Api::ApiError(['error' => 'Forbidden'], 403);
                }
            }

            // PHP-11: transacción + SELECT ... FOR UPDATE para evitar race con dos cobros simultáneos.
            $pdo = Db::pdo();
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "SELECT id, total_amount, paid_amount, payment_status
                       FROM booking WHERE id = :id FOR UPDATE"
                );
                $stmt->execute([':id' => $bookingId]);
                $b = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$b) {
                    $pdo->rollBack();
                    Api::ApiError(['error' => 'Booking not found'], 404);
                }
                if ($b['payment_status'] === 'paid') {
                    $pdo->rollBack();
                    Api::ApiError(['error' => 'Booking ya está pago'], 409);
                }

                $newPaid = (float) $b['paid_amount'] + $amount;
                $total = (float) $b['total_amount'];
                $newStatus = $newPaid >= $total ? 'paid' : 'partial';
                $paidInCashAt = $newStatus === 'paid' ? date('Y-m-d H:i:s') : null;

                $upd = $pdo->prepare(
                    "UPDATE booking
                        SET paid_amount = :paid,
                            payment_status = :status,
                            paid_in_cash_at = COALESCE(paid_in_cash_at, :pca),
                            paid_in_cash_by_user_id = :emp
                      WHERE id = :id"
                );
                $upd->execute([
                    ':id'     => $bookingId,
                    ':paid'   => $newPaid,
                    ':status' => $newStatus,
                    ':pca'    => $paidInCashAt,
                    ':emp'    => $employee,
                ]);

                $log = $pdo->prepare(
                    "INSERT INTO booking_logs (id_booking, id_user, action, note, created_at)
                     VALUES (:bid, :uid, 'cash_payment', :note, NOW())"
                );
                $log->execute([
                    ':bid'  => $bookingId,
                    ':uid'  => $employee,
                    ':note' => sprintf('Cash payment: $%.2f (running total $%.2f / $%.2f)', $amount, $newPaid, $total) . (isset($d->note) ? ' — ' . $d->note : ''),
                ]);

                $pdo->commit();
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('registerCash error: ' . $e->getMessage());
                Api::ApiError(['error' => 'Internal error'], 500);
            }

            JSON([
                'ok' => true,
                'booking_id' => $bookingId,
                'paid_amount' => $newPaid,
                'balance_due' => max(0, $total - $newPaid),
                'payment_status' => $newStatus,
            ]);
        }
    }
