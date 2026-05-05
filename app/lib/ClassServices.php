<?php

    class Services{

        public static function getServicesField(){
            $services = query("SELECT * FROM services_soccer ORDER BY id_service", 'ALL');

            return $services;
        }
        public static function getAllServicesCancha($id_field){
            $schedules = query("SELECT 
                    ss.id_service,
                    ss.name_service,
                    CASE WHEN sfs.id_service_field IS NOT NULL THEN 'checked' ELSE NULL END AS checked
                    FROM services_soccer AS ss 
                    LEFT JOIN soccer_field_services AS sfs ON ss.id_service = sfs.id_service_field
                    AND
                    sfs.id_field = ?
                    ORDER BY ss.id_service ASC",
            'ALL', [$id_field]);

            return $schedules;
        }
        public static function deleteAllServicesField($id_field){
            query("DELETE FROM soccer_field_services WHERE id_field = ?", '', [$id_field]);
        }
        
        public static function addServiceField($id_field, $id_service){
            query("INSERT INTO soccer_field_services(
                id_field,
                id_service_field
                ) VALUES (
                    ?, 
                    ?
                    )
            ", '', [$id_field, $id_service]);
        }
        
    }