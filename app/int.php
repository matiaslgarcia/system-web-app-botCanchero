<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
    define('VERSION', '1.0.8');

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
