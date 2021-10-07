<?php

namespace System;

class Database {
    static $instance;
    private $connection;

    private function __construct() {}

    static function getConnection() {
        return Database::getInstance()->connection;
    }

    static function init() {
        Database::getInstance()->connect();
    }

    private static function getInstance() {
        global $instance;
        if ($instance == null) {
            $instance = new Database();
        }
        return $instance;
    }

    private function connect() {
        $dbName = getenv('DB_NAME');
        $dbHost = getenv('DB_HOST');
        $dbPort = getenv('DB_PORT');
        $dbUser = getenv('DB_USERNAME');
        $dbPass = getenv('DB_PASSWORD');
        $dbCharset = Utils::getFromArray('DB_CHARSET', $_ENV, 'utf8');
        $debug = getenv('DEBUG_MODE');

        try {
            $dsn = "mysql:host=$dbHost;";
            $dsn .= empty($dbPort) ? '' : "port=$dbPort;";
            $dsn .= empty($dbCharset) ? '' : "charset=$dbCharset;";
            $dsn .= "dbname=$dbName;";

            $this->connection = new \PDO("mysql:host=$dbHost;port=$dbPort;charset=$dbCharset;dbname=$dbName;", $dbUser, $dbPass);
            if ($debug) {
                $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            }
        } catch(\PDOException $e) {
            if ($debug) {
                echo "Failed connecting to the database.<br>";
                echo $e;
            }
            die();
        }
    }
}