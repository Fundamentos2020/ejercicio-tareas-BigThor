<?php
include_once 'DB.php';

class CategoriaModelo
{
    protected $_db;

    public function __construct()
    {
        $this->_db = Db::init();
    }

    public function obtenerCategorias($data = null)
    {
        $sql = "SELECT * FROM categorias";
        $sth = $this->_db->prepare($sql);
        $sth->execute($data);

        $categorias = array();
        while($row = $sth->fetch(PDO::FETCH_ASSOC)){
            $categorias[$row['id']] = $row;
        }

        return $categorias;
    }

    public function obtenerCategoriaPorId($id_categoria = null)
    {
        $sql = "SELECT * FROM categorias WHERE id :id";

        $sth = $this->_db->prepare($sql);
        $sth->bindParam(':id', $id_categoria, PDO::PARAM_INT);

        $sth->execute($data);
        return $sth->fetch();
    }
}

?>