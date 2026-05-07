<?php

    require '../../int.php';

    Users::requireSuperAdmin(true);

    Canchas::add(obj($_POST));
