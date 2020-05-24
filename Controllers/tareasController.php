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
    // Se está haciendo un request GET
    if($_SERVER['REQUEST_METHOD'] === 'GET') {
        try{
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
        }
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
        }
    }
    // Se está haciendo un request POST
    else if($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // El formato está incorrecto
            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Encabezado "Content type" no es un JSON');
                $response->send();
                exit();
            }
            // El formato es correcto
            $postData = file_get_contents('php://input');

            // No se puede convertir a JSON
            if(!$json_data = json_decode($postData)){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('El cuerpo de la solicitud no es un json válido');
                $response->send();
                exit();
            }

            // Se verifica que los campos requeridos existan
            if(!isset($json_data->titulo) || !isset($json_data->completada) || !isset($json_data->categoria_id) ){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Hace falta un campo necesario');
                if(!isset($json_data->titulo))
                    $response->addMessage('El título es obligatorio');
                if(!isset($json_data->completada))
                    $response->addMessage('El campo de completada es obligatorio');
                if(!isset($json_data->categoria_id))
                    $response->addMessage('El id de la categoria es obligatorio');
                $response->send();
                exit();
            }

            // Después de las validaciones, se crea la tarea
            $tarea = new Tarea(
                null, 
                $json_data->titulo,
                isset($json_data->descripcion) ? $json_data->descripcion : null,
                isset($json_data->fecha_limite) ? $json_data->fecha_limite : null,
                $json_data->completada,
                $json_data->categoria_id
            );


            $titulo = $tarea->getTitulo();
            $descripcion = $tarea->getDescripcion();
            $fecha_limite = $tarea->getFechaLimite();
            $completada = $tarea->getCompletada();
            $categoria_id = $tarea->getCategoriaId();

            $sql = 'INSERT INTO tareas (titulo, descripcion, fecha_limite, completada, categoria_id)
                    VALUES (:titulo, :descripcion, STR_TO_DATE(:fecha_limite, \'%Y-%m-%d %H:%i\'), :completada, :categoria_id)';
            $query = $connection->prepare($sql);

            $query->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $query->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
            $query->bindParam(':fecha_limite', $fecha_limite, PDO::PARAM_STR);
            $query->bindParam(':completada', $completada, PDO::PARAM_STR);
            $query->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
            
            $query->execute();

            $rowCount = $query->rowCount();
            // El registro no se insertó
            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al crear la tarea");
                $response->send();
                exit();
            }

            $ultimo_ID = $connection->lastInsertId();

            $sql = 'SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id 
                    FROM tareas WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->bindParam(':id', $ultimo_ID, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();
            // El registro no se insertó
            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al obtener la tarea recién creada");
                $response->send();
                exit();
            }

            $tareas = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id'] );

                $tareas[] = $tarea->getArray();
            }
            $returnData = array();
            $returnData['tareas'] = $tareas;
            $returnData['total_registros'] = $rowCount;

            $response = new Response();
            $response->setHttpStatusCode(201);
            $response->setSuccess(true);
            $response->setToCache(false);
            $response->addMessage("Tarea creada");
            $response->setData($returnData);
            $response->send();
            exit();
        }
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
        }
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
else if(array_key_exists("id_tarea", $_GET)) {
    $id_tarea = $_GET['id_tarea'];

    if($id_tarea == '' || !is_numeric($id_tarea)){
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("El id de la tarea no es válido");
        $response->send();
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        try {
            // El formato está incorrecto
            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('Encabezado "Content type" no es un JSON');
                $response->send();
                exit();
            }
            // El formato es correcto
            $postData = file_get_contents('php://input');

            // No se puede convertir a JSON
            if(!$json_data = json_decode($postData)){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage('El cuerpo de la solicitud no es un json válido');
                $response->send();
                exit();
            }
            
            $actualiza_titulo = isset($json_data->titulo);
            $actualiza_descripcion = isset($json_data->descripcion);
            $actualiza_fechaLimite = isset($json_data->fecha_limite);
            $actualiza_completada = isset($json_data->completada);
            $actualiza_categoriaId = isset($json_data->categoria_id);

            $campos_query = "";
            if($actualiza_titulo)
                $campos_query .= "titulo = :titulo, ";
            if($actualiza_descripcion)
                $campos_query .= "descripcion = :descripcion, ";
            if($actualiza_fechaLimite)
                $campos_query .= 'fecha_limite = STR_TO_DATE(:fecha_limite, \'%Y-%m-%d %H:%i\'), ';
            if($actualiza_completada)
                $campos_query .= "completada = :completada, ";
            if($actualiza_categoriaId)
                $campos_query .= "categoria_id = :categoria_id, ";

            $campos_query = rtrim($campos_query, ", ");

            // No hay ningun campo para actualizar
            if(!$actualiza_titulo && !$actualiza_descripcion && 
               !$actualiza_fechaLimite && !$actualiza_completada && !$actualiza_categoriaId){
                $response = new Response();
                $response->setHttpStatusCode(400);
                $response->setSuccess(false);
                $response->addMessage("No hay campos para actualizar");
                $response->send();
                exit();
            }

            $sql = 'SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id 
                    FROM tareas WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);

            $query->execute();

            $rowCount = $query->rowCount();
            // No se encontró la tarea
            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No se encontró la tarea con el ID especificado");
                $response->send();
                exit();
            }

            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $tarea = new Tarea($row['id'], $row['titulo'], $row['descripcion'], $row['fecha_limite'], $row['completada'], $row['categoria_id'] );
            }

            $sql = 'UPDATE tareas SET '. $campos_query . ' WHERE id = :id';
            $query = $connection->prepare($sql);
            if($actualiza_titulo){
                $tarea->setTitulo($json_data->titulo);
                $query->bindParam(':titulo', $tarea->getTitulo(), PDO::PARAM_STR);
            }
            if($actualiza_descripcion){
                $tarea->setDescripcion($json_data->descripcion);
                $query->bindParam(':descripcion', $tarea->getDescripcion(), PDO::PARAM_STR);
            }
            if($actualiza_fechaLimite){
                $tarea->setFechaLimite($json_data->fecha_limite);
                $query->bindParam(':fecha_limite', $tarea->getFechaLimite(), PDO::PARAM_STR);
            }
            if($actualiza_completada){
                $tarea->setCompletada($json_data->completada);
                $query->bindParam(':completada', $tarea->getCompletada(), PDO::PARAM_STR);
            }
            if($actualiza_categoriaId){
                $tarea->setCategoriaId($json_data->categoria_id);
                $query->bindParam(':categoria_id', $tarea->getCategoriaId(), PDO::PARAM_INT);
            }
            $query->bindParam(':id', $tarea->getId(), PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();
            // No se actualizó la tarea
            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error al actualizar la tarea");
                $response->send();
                exit();
            }

            $sql = 'SELECT id, titulo, descripcion, DATE_FORMAT(fecha_limite, "%Y-%m-%d %H:%i") fecha_limite, completada, categoria_id 
                    FROM tareas WHERE id = :id';
            $query = $connection->prepare($sql);
            $query->bindParam(':id', $id_tarea, PDO::PARAM_INT);

            $query->execute();

            $rowCount = $query->rowCount();
            // No se encontró la tarea después de actualizar
            if($rowCount === 0){
                $response = new Response();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("No se encontró la tarea después de actualizar");
                $response->send();
                exit();
            }

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
            $response->setToCache(false);
            $response->addMessage("Tarea actualizada");
            $response->setData($returnData);
            $response->send();
            exit();

        }
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
            $response->addMessage("Error al actualizar la tarea");
            $response->send();
            exit();
        }
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