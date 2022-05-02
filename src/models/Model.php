<?php

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
}