<?php
include_once dirname(__DIR__) . '/Models/Tarea.php';
include_once dirname(__DIR__) . '/Models/Categoria.php';
include_once dirname(__DIR__) . '/view.php';

class TareasController
{
    protected $_modeloTareas;
    protected $_vista;

    public function __construct()
    {
        $this->_modeloTareas = new TareaModelo();
        $this->_vista = new View(dirname(__DIR__).'/index.html');
        $modeloCategorias = new CategoriaModelo();
        $categorias = $modeloCategorias->obtenerCategorias();
        $this->_vista->set('categorias', json_encode( $categorias ));
    }

    public function index(){
        $tareas = $this->_modeloTareas->obtenerTareas();
        $this->_vista->set('tareas', json_encode( $tareas ));
        return $this->_vista->output();
    }

    public function filtradasPorCategoria($categoria_id){
        $tareas = $this->_modeloTareas->obtenerTareasPorCategoria($categoria_id);
        $this->_vista->set('tareas', json_encode( $tareas ));
        return $this->_vista->output();
    }
}

?>