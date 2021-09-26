<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

ini_set('display_errors', true);
error_reporting(E_ALL);

define('APP_NAME', 'api');
define('DEBUG_MODE', false);

// Database connection

$dbHost = 'localhost';
$dbName = 'teste_db';
$dbUser = 'php';
$dbPass = 'root';

try {
    $db = new PDO("mysql:dbname=$dbName;host=$dbHost", $dbUser, $dbPass);
} catch(PDOException $e) {
    echo "Failed connecting to the database.<br>";
    echo $e;
    die();
}

// Autoloading
require 'autoload.php';

// Router
$router = new System\Router();

require 'routes.php';

$router->init();