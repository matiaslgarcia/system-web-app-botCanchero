<?php

    require '../../int.php';
    if (session_status() === PHP_SESSION_NONE) session_start();
    Booking::cerrarPago(obj($_POST));