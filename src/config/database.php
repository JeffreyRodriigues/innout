<?php

class Database {

    public static function getConnection() { //conectando ao banco
        $envPath = realpath(dirname(__FILE__) . '/../env.ini'); //caminho absoluto
        $env = parse_ini_file($envPath); //Pega as informações da configuração
        $conn = new mysqli($env['host'], $env['username'], 
            $env['password'], $env['database']);

            if($conn->connect_error) {
                die("Erro: " . $conn->connect_error);
        }

        return $conn;
    }

    public static function getResultFromQuery($sql) { //coletando informações do banco
        $conn = self::getConnection();
        $result = $conn->query($sql);
        $conn->close();
        return $result;
    }
}