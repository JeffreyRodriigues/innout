<?php

error_reporting(0);
class Model {
    protected static $tableName = '';
    protected static $columns = [];
    protected $values = [];

    function __construct($arr)
    {
        $this->loadFromArray($arr);
    }

    public function loadFromArray($arr) {
        if($arr) {
            foreach($arr as $key => $value) { 
                $this-> $key = $value; //se a chave não existe, ele chama o set;
            }
        }
    }

    public function __get($key) {
        return $this->values[$key];
    }

    public function __set($key, $value) {
        $this->values[$key] = $value;
    }

    public static function getOne($filters = [], $columns = '*') {
        $class = get_called_class();
        $result = static::getResultSetFromSelect($filters, $columns = '*');
        return $result ? new $class($result->fetch_assoc()) : null;
    }

    public static function get($filters = [], $columns = '*') {
        $objects = [];
        $result = static::getResultSetFromSelect($filters, $columns = '*');
        if($result) {
            $class = get_called_class();
            while($row = $result->fetch_assoc()) {
               array_push($objects, new $class($row)); 
            }
        }
        return $objects;
    }

    public static function getResultSetFromSelect($filters = [],  $columns = '*') {//Recebe os filtros, e a lista de colunas que quero obter no banco
        $sql = "SELECT ${columns} FROM "
        . static::$tableName
        . static::getFilters($filters);
        $result = Database::getResultFromQuery($sql);
        if($result->num_rows === 0) {
            return null;
        } else {
            return $result;
        }
    }

    public function insert() { //Inserir dados no banco
        $sql = "INSERT INTO " . static::$tableName . " ("
            . implode(",", static::$columns) . ") VALUES (";
        foreach(static::$columns as $col) {
            $sql .= static::getFormatedValue($this->$col) . ",";
        }
        $sql[strlen($sql) - 1] = ')';
        $id = Database::executeSQL($sql);
        $this->id = $id;
    }

    public function update() { //atualizar dados no banco
        $sql = "UPDATE " . static::$tableName . " SET ";
        foreach(static::$columns as $col) {
            $sql .= " ${col} = " .static::getFormatedValue($this->$col) . ",";
        }
        $sql[strlen($sql) - 1] = ' ';
        $sql .= "WHERE id = {$this->id}";
        Database::executeSQL($sql);
    }

    public static function getCount($filters = []) { //contando infos do banco
        $result = static::getResultSetFromSelect(
            $filters, 'count(*) as count');
        return $result->fetch_assoc()['count'];
    }

    private static function getFilters($filters) { //Responsável pelo Where - Faz um filtro do que foi passado, e retorna o valor
        $sql = '';
        if(count($filters) > 0) {
            $sql .= " WHERE 1 = 1";
            foreach($filters as $column => $value) {
                if($column == 'raw') { //sempre que colocar raw...
                    $sql .= " AND {$value}";//...Insere o valor no Where
                }else {
                    $sql .= " AND ${column} = " . static::getFormatedValue($value);
                }
            }
        }
        return $sql;
    }

    private static function getFormatedValue($value) {
        if(is_null($value)) {
            return "null";
        } elseif(gettype($value) === 'string') {
            return "'${value}'";
        } else {
            return $value;
        }
    }
}