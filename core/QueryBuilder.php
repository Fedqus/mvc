<?php

namespace core;

use PDO;

class QueryBuilder {
    protected PDO $connection;

    protected $tableName;
    protected $statementParams;

    protected $primaryPart;
    protected $wherePart;
    protected $orderByPart;
    
    public function __construct(PDO $connection, $tableName) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->statementParams = [];
    }

    private function bindParams($params, $statementKey, $selector, $separator) : string {
        foreach ($params as $key => $value) {
            $paramKey = $statementKey($key);
            $this->statementParams[$paramKey] = $value;
            $valuesParts[] = $selector($key, $paramKey);
        }
        return implode($separator, $valuesParts);
    }

    public function select($fields = "*") {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }
        $this->primaryPart = "SELECT $fields FROM $this->tableName ";
        return $this;
    }
    public function insert($params) {
        $fields = implode(", ", array_keys($params));
        $values = $this->bindParams(
            $params, 
            fn($key) => ":insert$key", 
            fn($key, $statementKey) => $statementKey, 
            ", "
        );
        $this->primaryPart = "INSERT INTO $this->tableName ($fields) VALUES ($values) ";
        return $this;
    }
    public function update($params) {
        $values = $this->bindParams(
            $params, 
            fn($key) => ":update$key", 
            fn($key, $statementKey) => "$key = $statementKey", 
            ", "
        );
        $this->primaryPart = "UPDATE $this->tableName SET $values ";
        return $this;
    }
    public function delete() {
        $this->primaryPart = "DELETE FROM $this->tableName ";
        return $this;
    }

    public function where($params, $condition = null) {
        if ($condition) {
            $this->statementParams = array_merge($this->statementParams, $params);
        }
        else {
            $condition = $this->bindParams(
                $params, 
                fn($key) => ":where$key", 
                fn($key, $statementKey) => "$key = $statementKey", 
                " AND "
            );
        }
        $this->wherePart = "WHERE $condition ";
        return $this;
    }
    public function orderBy($fields) {
        if (is_array($fields)) {
            $fields = implode(', ', $fields);
        }
        $this->orderByPart = "ORDER BY $fields ";
        return $this;
    }
    
    public function execute() {
        $sql = $this->primaryPart.$this->wherePart.$this->orderByPart;
        $result = $this->connection->prepare($sql);
        
        $execResult = $result->execute($this->statementParams);
        $fetchResult = $result->fetchAll(PDO::FETCH_ASSOC);

        if (str_contains($this->primaryPart, "SELECT")) {
            return $fetchResult;
        }
        return $execResult;
    }
}