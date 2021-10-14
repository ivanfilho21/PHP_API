<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}

require 'autoload.php';

System\DotEnv::load('.env');

$timezone = getenv('TIME_ZONE');
if ($timezone) {
    date_default_timezone_set($timezone);
}

System\Database::init();

$router = new System\Router();

include 'routes.php';

$router->initialize();