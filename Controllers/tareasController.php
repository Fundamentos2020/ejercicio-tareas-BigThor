<?php
require_once('../Models/Tarea.php');
require_once('../Models/DB.php');
require_once('../Models/Response.php');


try {
    $connection = DB::init();
    $sql = "SELECT * FROM tareas";

    // Se filtran por categoria
    if(isset($_GET['categoria_id'])){
        $sql = $sql . ' WHERE categoria_id = :categoria_id';
    }

    $query = $connection->prepare($sql);

    if(isset($_GET['categoria_id'])){
        $query->bindParam(':categoria_id', $_GET['categoria_id'], PDO::PARAM_INT);
    }

    $query->execute();

    $tareas = array();
    while($row = $query->fetch(PDO::FETCH_ASSOC)){
        $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id'] );

        $tareas[] = $tarea->getArray();
    }

    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->setData($tareas);
    $response->send();
    exit();
}
catch(PDOException $e){
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Algo falló");
    $response->send();
    exit();
}

?>