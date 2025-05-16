<?php
require_once 'path/to/config.php';
require_once 'path/to/header.php';


define('BASE_URL', 'http://localhost/consultorio_odontologico/');


define('APP_ROOT', dirname(dirname(__FILE__)));

define('SITE_NAME', 'Consultorio Odontológico');


define('SESSION_NAME', 'consultorio_odontologico');
define('SESSION_EXPIRY', 1800); // 30 minutos

date_default_timezone_set('America/Bogota');
?>

