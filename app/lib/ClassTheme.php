<?php

    class Theme{
        private static function appUrl($path = ''){
            return rtrim(URL, '/') . '/' . ltrim((string) $path, '/');
        }
        public static function header($arr = []){
            $base   = self::base($arr);
            $title  = self::title($arr);
            $csrf   = htmlspecialchars(Users::getCsrfToken(), ENT_QUOTES);
            $requestId = htmlspecialchars(getRequestId(), ENT_QUOTES);
            $css    = self::css($arr);
            $extra_css = self::extra_css($arr);
            $favicon = self::appUrl('assets/img/logos/favicon.ico');

            // Heredoc evita el quote-hell del concat con HTML+JS.
            echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    {$base}{$title}
    <meta charset="utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{$csrf}">
    <meta name="request-id" content="{$requestId}">
    <link rel="shortcut icon" href="{$favicon}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" />
    <style>body { font-family: "Outfit", sans-serif !important; }</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        \$.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': \$('meta[name="csrf-token"]').attr('content'),
                'X-Request-ID': \$('meta[name="request-id"]').attr('content')
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    {$css}
    {$extra_css}
</head>
<body id="kt_body" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled toolbar-fixed aside-enabled aside-fixed" style="--kt-toolbar-height:55px;--kt-toolbar-height-tablet-and-mobile:55px">
HTML;
        }
        private static function extra_css($arr){
            $css = '';
            if(isset($arr['extra_css'])){
                foreach($arr['extra_css'] as $val){
                    $css .= '<link rel="stylesheet" type="text/css" href="' . $val . '" >';
                }
            }
            return $css;
        }
        private static function title($arr){
            $title = (isset($arr['title']) and !empty($arr['title'])) ? '<title>'.COMPANY.' | '.$arr['title'].' </title>' : '<title>'.COMPANY.'</title>';
            return $title;
        }
        private static function css($arr){
            $css = '';
            if(isset($arr['css'])){
                foreach($arr['css'] as $val){
                    $css .= '<link rel="stylesheet" id="'.$val.'" type="text/css" href="' . self::appUrl('assets/css/' . $val . '.css?ver=' . VERSION) . '" >';
                }
            }

            return $css;
        }

        public static function footer($arr = []){
            $footer = self::JS($arr) .
                        self::extra_js($arr) .
                        self::dataJS($arr) .
                '</body>
            </html>';

            echo $footer;
        }
        private static function JS($arr){
            $js = '';
            if(isset($arr['js']) or isset($arr['JS']) and is_array($arr['js'])){
                foreach($arr['js'] as $val){
                    $js .= '<script type="text/javascript" src="' . self::appUrl('assets/js/' . $val . '.js?ver=' . VERSION) . '"></script>';
                }
            }
            return $js;
        }
        private static function extra_js($arr){
            $js = '';
            if(isset($arr['extra_js'])){
                foreach($arr['extra_js'] as $val){
                    $js .= '<script type="text/javascript" src="' .$val . '"></script>';
                }
            }
            return $js;
        }
        private static function dataJS($arr){
            if(isset($arr['dataJS'])){
                $js = '';
                foreach($arr['dataJS'] as $val){
                    $js .= '<script type="module" src="' . self::appUrl('lib/dataJS/' . $val . '.js?ver=' . VERSION) . '"></script>';
                }
                return $js;
            }
        }
        private static function base($arr){
            $baseHref = URL;
            if (isset($arr['base'])) {
                $candidate = trim((string) $arr['base']);
                if ($candidate !== '') {
                    $baseHref = $candidate;
                }
            }
            return '<base href="' . htmlspecialchars($baseHref, ENT_QUOTES) . '">';
        }

        public static function sidebar(){
            $sidebar = array(
                ['name' => 'cancheros', 'dir' => 'seccion_sidebar/cancheros', 'rol' => ''],
                ['name' => 'administrativo', 'dir' => 'seccion_sidebar/admin', 'rol' => 'superAdmin'],
                
            );
            return $sidebar;
        }
    }
