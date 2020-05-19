<?php
require_once('../Models/Categoria.php');
require_once('../Models/DB.php');
require_once('../Models/Response.php');

try {
    $connection = DB::init();
}
catch(PDOException $e){
    error_log('Error de conexión: '. $e);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Error en la conexión a Base de datos");
    $response->send();
    exit();
}

//GET host/categorias
if(empty($_GET)){
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        $sql = "SELECT * FROM categorias";
        $query = $connection->prepare($sql);
        $query->execute();

        $categorias = array();
        /*
        while($row = $sth->fetch(PDO::FETCH_ASSOC)){
            $categorias[$row['id']] = $row;
        }*/
        while($row = $query->fetch(PDO::FETCH_ASSOC)){
            $categoria = new Categoria($row['id'], $row['nombre']);

            $categorias[] = $categoria->getArray();
        }

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($categorias);
        $response->send();
        exit();
    }
    else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Método no permitido");
        $response->send();
        exit();
    }
}
else {
    $response = new Response();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Ruta no encontrada");
    $response->send();
    exit();
}


?>