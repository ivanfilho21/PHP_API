<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define('APP_NAME', 'api');
define('DEBUG_MODE', 0);

//if (defined('DEBUG_MODE') && DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
//}

// Autoloading
require 'autoload.php';

// Database connection
$dbHost = 'localhost';
$dbName = 'teste_db';
$dbUser = 'php';
$dbPass = 'root';

System\Database::init($dbName, $dbHost, $dbUser, $dbPass, true);

// Router
$router = new System\Router();

require 'routes.php';

$router->init();