<?php

    require '../../int.php';
    session_start();
    Booking::cerrarPago(obj($_POST));