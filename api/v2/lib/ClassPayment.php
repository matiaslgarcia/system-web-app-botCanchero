<?php

    class Payment{
        public static function created(){
           $data = Api::getData();
           $data->date_created = date("Y-m-d H:i:s");
           query("INSERT INTO vouchers (
            data_id, id_notification, id_canchero, method_name,  date_create
            ) VALUES (
            '$data->data_id', '$data->id_notification', '$data->id_canchero', '$data->method_name', '$data->date_created')");

            JSON(self::getVaucherByDataID($data->data_id));
        }

        private static function  getVaucherByDataID($data_id){
            $vaucher = query("SELECT * FROM vouchers WHERE data_id = '$data_id' ORDER BY id DESC LIMIT 1");
            
            return $vaucher;
        }
        public static function getVaucher(){
            $data = Api::getData();
            JSON(self::getVaucherByDataID($data->data_id));
        }
        public static function result(){
            $data = $data = Api::getData();
            query("INSERT INTO payment (
                    payment_id, ip_address, cardholder_identification_number, cardholder_identification_type, card_name,
                    date_created, date_last_updated, expiration_month, expiration_year, first_six_digits, last_four_digits,
                    charges_details_mounts_original, charges_details_mounts_refunded, charges_details_mounts_amounts_original_1,
                    charges_details_mounts_amounts_refunded_1, charges_details_mounts_amounts_original_2, charges_details_mounts_amounts_refunded_2,
                    metadata_mov_detail, metadata_mov_financial_entity, metadata_mov_type, metadata_tax_id, metadata_tax_status,
                    metadata_user_id, metadata_type, coupon_amount, currency_id, date_approved, date_of_expiration, marketplace_owner,
                    notification_url, order_id, order_type, payment_method_id, payment_type_id, statement_descriptor, status, status_detail,
                    taxes_amount, transaction_amount, transaction_amount_refunded
                )
                VALUES (
                    '$data->payment_id', '$data->ip_address', '$data->cardholder_identification_number', '$data->cardholder_identification_type', '$data->card_name',
                    '$data->date_created', '$data->date_last_updated', '$data->expiration_month', '$data->expiration_year', '$data->first_six_digits', '$data->last_four_digits',
                    '$data->charges_details_mounts_original', '$data->charges_details_mounts_refunded', '$data->charges_details_mounts_amounts_original_1', '$data->charges_details_mounts_amounts_refunded_1',
                    '$data->charges_details_mounts_amounts_original_2', '$data->charges_details_mounts_amounts_refunded_2', '$data->metadata_mov_detail', '$data->metadata_mov_financial_entity', '$data->metadata_mov_type',
                    '$data->metadata_tax_id', '$data->metadata_tax_status', '$data->metadata_user_id', '$data->metadata_type', '$data->coupon_amount', '$data->currency_id',
                    '$data->date_approved', '$data->date_of_expiration', '$data->marketplace_owner', '$data->notification_url', '$data->order_id', '$data->order_type', '$data->payment_method_id',
                    '$data->payment_type_id', '$data->statement_descriptor', '$data->status', '$data->status_detail', '$data->taxes_amount', '$data->transaction_amount', '$data->transaction_amount_refunded'
                )"
            );
            JSON([]);
        }

        public static function updateCheckOut($data_id, $id_booking){
            query("UPDATE vouchers SET id_booking = '$id_booking' WHERE data_id = '$data_id'");
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
             ) 
             VALUES (
                '$data->client_id',
                '$data->collector_id',
                '$data->coupon_code',
                '$data->coupon_labels',
                '$data->date_created',
                '$data->date_of_expiration',
                '$data->expiration_date_from',
                '$data->expiration_date_to',
                '$data->expires',
                '$data->external_reference',
                '$data->id',
                '$data->init_point',
                '$data->internal_metadata',
                '$data->items_id',
                '$data->items_category_id',
                '$data->items_currency_id',
                '$data->items_description',
                '$data->items_picture_url',
                '$data->items_title',
                '$data->items_quantity',
                '$data->items_unit_price',
                '$data->marketplace',
                '$data->marketplace_fee',
                '$data->notification_url',
                '$data->operation_type',
                '$data->payer_area_code',
                '$data->payer_number',
                '$data->payer_address_zip_code',
                '$data->payer_address_street_name',
                '$data->payer_address_street_number',
                '$data->email',
                '$data->identification_number',
                '$data->identification_type',
                '$data->name',
                '$data->surname',
                '$data->last_purchase',
                '$data->statement_descriptor',
                '$data->total_amount',
                '$data->last_updated'
             )");
            JSON($data);
        }
    }