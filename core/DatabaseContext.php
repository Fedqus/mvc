<?php

namespace core;

use PDO;

class DatabaseContext {
    protected PDO $connection;

    public function __construct($host, $dbname, $login, $password) {
        $this->connection = new PDO("mysql: host=$host;dbname=$dbname", $login, $password);
    }

    public function lastInsertId($name = null){
        return $this->connection->lastInsertId($name);
    }

    public function table($tableName){
        $query = new QueryBuilder($this->connection, $tableName);
        return $query;
    }
}