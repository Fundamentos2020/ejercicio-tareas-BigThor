<?php

class Db {
    private static $db;

    public static function init(){
        if (!self::$db)
        {
            try {
                $dns = 'mysql:host=localhost; dbname=lista_tarea;';
                $username = 'root';
                $password = '';
            
                self::$db = new PDO($dns, $username, $password);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                } catch (PDOException $e) {
                die('Connection error: ' . $e->getMessage());
            }
        }   
        return self::$db;
    }
}

?>
