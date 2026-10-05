<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
define('BASE_URL', $scriptDir);

date_default_timezone_set('America/Bogota');

ini_set('display_errors', '1');
error_reporting(E_ALL);
