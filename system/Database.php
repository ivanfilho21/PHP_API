<?php

namespace System;

class Database {
    static $instance;
    private $connection;

    private function __construct() {}

    private static function getInstance() {
        global $instance;
        if ($instance == null) {
            $instance = new Database();
        }
        return $instance;
    }

    static function getConnection() {
        return Database::getInstance()->connection;
    }

    static function init($dbName, $dbHost, $dbUser, $dbPass, $debug = false) {
        Database::getInstance()->connect($dbName, $dbHost, $dbUser, $dbPass, $debug);
    }

    private function connect($dbName, $dbHost, $dbUser, $dbPass, $debug) {
        try {
            $this->connection = new \PDO("mysql:dbname=$dbName;host=$dbHost", $dbUser, $dbPass);
            if ($debug) {
                $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            }
        } catch(PDOException $e) {
            if ($debug) {
                echo "Failed connecting to the database.<br>";
                echo $e;
            }
            die();
        }
    }
}