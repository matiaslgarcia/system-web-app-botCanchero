<?php

    require '../../int.php';

    Users::requireSuperAdmin(true);
    Canchas::deleteCancha(obj($_POST));
