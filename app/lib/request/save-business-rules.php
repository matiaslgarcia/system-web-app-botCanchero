<?php

require '../../int.php';

Users::loginCheck();
BusinessRules::save(obj($_POST));
