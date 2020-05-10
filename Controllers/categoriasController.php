<?php
include dirname(__DIR__) . '/Models/Categoria.php';

class CategoriasController
{
    protected $_modeloCategorias;

    public function __construct()
    {
        $this->_modeloCategorias = new CategoriaModelo();
    }

    public function index(){
        return $this->_modeloCategorias->obtenerCategorias();
    }
}

?>