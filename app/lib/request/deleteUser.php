<?php

    require '../../int.php';

    Users::requireSuperAdmin(true);
    Users::delete(obj($_POST));
