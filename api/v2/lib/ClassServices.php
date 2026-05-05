<?php

    class Services{

        public static function getServicesField(){
            // Catálogo global de servicios disponibles — no requiere scoping.
            $services = query("SELECT * FROM services_soccer ORDER BY id_service", 'ALL');
            JSON($services);
        }

        public static function getAllServicesCancha(){
            $data = Api::getData();
            if (empty($data->id_field)) {
                Api::ApiError(['error' => 'id_field es obligatorio'], 400);
            }

            // BOT-TENANT: verificar que la cancha pertenezca al establishment
            // del token bound antes de exponer sus servicios.
            $boundEst = Auth::getEstablishmentId();
            if ($boundEst) {
                $field = query(
                    "SELECT establishment_id FROM soccer_field WHERE id = ?",
                    'ARRAY',
                    [(int) $data->id_field]
                );
                if (!$field || (int) ($field['establishment_id'] ?? 0) !== (int) $boundEst) {
                    Api::ApiError(['error' => 'Forbidden access to field'], 403);
                }
            }

            $schedules = query("SELECT
                                    ss.name_service,
                                    CASE WHEN sfs.id_service_field IS NOT NULL THEN 'si' ELSE 'no' END AS posee
                                FROM services_soccer AS ss
                                LEFT JOIN soccer_field_services AS sfs ON ss.id_service = sfs.id_service_field
                                AND sfs.id_field = ?
                                ORDER BY ss.id_service ASC;",
                                'ALL', [(int) $data->id_field]);

            JSON($schedules);
        }
    }