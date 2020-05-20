<?php
require_once('../Models/Tarea.php');
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

// host/tareas
if(empty($_GET) || isset($_GET['categoria_id'])){
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        {
            $sql = 'SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id FROM tareas';

            // Se filtran por categoria
            // GET host/tareas?categoria_id={id}
            if(isset($_GET['categoria_id'])){
                if($_GET['categoria_id'] == '' || !is_numeric($_GET['categoria_id'])){
                    $response = new Response();
                    $response->setHttpStatusCode(400);
                    $response->setSuccess(false);
                    $response->addMessage("El campo de categoria no puede estar vacio o ser diferente de un número");
                    $response->send();
                    exit();
                }

                $sql = $sql . ' WHERE categoria_id = :categoria_id';
            }

            $query = $connection->prepare($sql);

            if(isset($_GET['categoria_id'])){
                $query->bindParam(':categoria_id', $_GET['categoria_id'], PDO::PARAM_INT);
            }

            $query->execute();

            $rowCount = $query->rowCount();

            $tareas = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id'] );

                $tareas[] = $tarea->getArray();
            }
            $returnData = array();
            $returnData['tareas'] = $tareas;
            $returnData['total_registros'] = $rowCount;

            $response = new Response();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setToCache(true);
            $response->setData($returnData);
            $response->send();
            exit();
        }/*
        catch(TareaException $e){
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch(PDOException $e){
            error_log('Error de BD: '. $e);
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Error en consulta de tareas");
            $response->send();
            exit();
        }*/
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