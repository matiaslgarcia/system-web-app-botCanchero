<?php


    require '../../app/config.php';
    require '../../app/lib/function.php';
    initRequestContext();
    require '../../app/lib/ClassConexion.php';
    require '../../app/lib/ClassDomainEvents.php';
    require '../../app/lib/ClassCustomers.php';
    require '../../app/lib/ClassFeatureGate.php';
    require '../../app/lib/ClassBusinessRules.php';
    require '../../app/lib/ClassSchedules.php';
    require 'lib/ClassApi.php';
    require 'lib/ClassAut.php';
    require 'lib/ClassPayment.php';
    require 'lib/ClassCanchas.php';
    require 'lib/ClassBooking.php';
    require 'lib/ClassServices.php';
    require 'lib/ClassRecurring.php';
    require 'lib/ClassCalendar.php';
    require 'lib/ClassRules.php';
