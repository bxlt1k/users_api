<?php

namespace Conn;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        try {
            if (self::$connection === null) {
                $config = require_once 'config.php';
                self::$connection = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['db_name'], $config['username'], $config['password']);
            }
            return self::$connection;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}