<?php

    class Address{
        public static function getProvincias(){

            $provincias = query("SELECT * FROM province", 'ALL');

            return $provincias;
        }
        public static function getProvincia($id){

            $provincia = query("SELECT * FROM provinceWHERE id = '$id'");

            return $provincia;
        }
        public static function getCity(){

            $provincias = query("SELECT * FROM city", 'ALL');

            return $provincias;
        }
    }