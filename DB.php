<?php

class DB
{
    private static $instance = null;
    public $pdo;

    private function __construct()
    {
        $this->pdo = new PDO('sqlite:./database.sqlite');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new DB();
        }
        return self::$instance;
    }
}
