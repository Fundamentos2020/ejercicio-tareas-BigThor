<?php
include_once 'DB.php';

class TareaModelo
{
    protected $_db;

    public function __construct()
    {
        $this->_db = Db::init();
    }

    public function obtenerTareas($data = null)
    {
        $sql = "SELECT * FROM tareas";
        $sth = $this->_db->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }

    public function obtenerTareasPorCategoria($id_categoria = null)
    {
        $sql = "SELECT * FROM tareas WHERE categoria_id= :id_categoria";

        $sth = $this->_db->prepare($sql);
        $sth->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);

        $sth->execute();
        return $sth->fetchAll();
    }
}

?>