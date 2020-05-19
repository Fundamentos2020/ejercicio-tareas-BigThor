<?php

class Db {
    private static $db;

    public static function init(){
        if (!self::$db)
        {
            $dns = 'mysql:host=localhost; dbname=lista_tarea;';
            $username = 'root';
            $password = '';
        
            self::$db = new PDO($dns, $username, $password);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }   
        return self::$db;
    }
}

?>
