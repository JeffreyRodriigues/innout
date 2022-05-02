<?php

class Database {
    
    public static function getConnection() {
        $envPath = realpath(dirname(__FILE__) . '/../env.ini'); //caminho absoluto
        $env = parse_ini_file($envPath); //Pega as informações da configuração
        $conn = new mysqli($env['host'], $env['username'], 
            $env['password'], $env['database']);

            if($conn->connect_error) {
                die("Erro: " . $conn->connect_error);
        }

        return $conn;
    }
}