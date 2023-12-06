<?php

    class Theme{
        public static function header($arr = []){
            $header = '<!DOCTYPE html>
            <html lang="es">
                <!--begin::Head-->
                <head>
                    
                    ' .self::base($arr) .self::title($arr).'
                    <meta charset="utf-8" />
                    <meta name="description" content="" />
                    <meta name="keywords" content="" />
                    <meta name="viewport" content="width=device-width, initial-scale=1" />
                    <link rel="shortcut icon" href="assets/img/logos/favicon.ico" />
                    <!--begin::Fonts-->
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
                    '.self::css($arr).'
                    <!--end::Global Stylesheets Bundle-->
                </head>
                <!--end::Head-->
                <!--begin::Body-->
                <body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
                    <!--begin::Main-->
                    <!--begin::Root-->';

            echo $header;
        }
        private static function title($arr){
            $title = (isset($arr['title']) and !empty($arr['title'])) ? '<title>'.COMPANY.' | '.$arr['title'].' </title>' : '<title>'.COMPANY.'</title>';
            return $title;
        }
        private static function css($arr){
            $css = '';
            if(isset($arr['css'])){
                foreach($arr['css'] as $val){
                    $css .= '<link rel="stylesheet" id="'.$val.'" type="text/css" href="assets/css/' . $val . '.css?ver='.VERSION.'" >';
                }
            }

            return $css;
        }

        public static function footer($arr = []){
            $footer = self::JS($arr) .
                        self::dataJS($arr) .
                '</body>
            </html>';

            echo $footer;
        }
        private static function JS($arr){
            $js = '';
            if(isset($arr['js']) or isset($arr['JS']) and is_array($arr['js'])){
                foreach($arr['js'] as $val){
                    $js .= '<script type="text/javascript" src="assets/js/' .$val . '.js?ver='.VERSION.'"></script>';
                }
            }
            return $js;
        }
        private static function dataJS($arr){
            if(isset($arr['dataJS'])){
                $js = '';
                foreach($arr['dataJS'] as $val){
                    $js .= '<script type="module" src="lib/dataJS/' .$val . '.js?ver='.VERSION.'"></script>';
                }
                return $js;
            }
        }
        private static function base($arr){
            if(isset($arr['base'])){
                return '<base href="' . $arr['base'] . '">';
            }
        }

        public static function sidebar(){
            $sidebar = array(
                ['name' => 'cancheros', 'dir' => 'seccion_sidebar/cancheros', 'rol' => ''],
                ['name' => 'administrativo', 'dir' => 'seccion_sidebar/admin', 'rol' => 'superAdmin'],
                
            );
            return $sidebar;
        }
    }