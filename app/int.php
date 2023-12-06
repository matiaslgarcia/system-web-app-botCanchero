<?php
	date_default_timezone_set('America/Argentina/Buenos_Aires');

    define('VERSION', '1.0.9');
    
    require 'config.php';
    require 'lib/function.php';
    require 'lib/ClassConexion.php';
    require 'lib/ClassTheme.php';
    require 'lib/ClassUsers.php';
    require 'lib/ClassCanchas.php';
    require 'lib/ClassBooking.php';
    require 'lib/ClassPayment.php';
    require 'lib/ClassCustomers.php';
    require 'lib/ClassApi.php';
    require 'lib/ClassAddress.php';
    require 'lib/ClassSchedules.php';
    require 'lib/ClassInvoices.php';
    require 'lib/ClassMobex.php';
    require 'lib/ClassMercadoPago.php';
