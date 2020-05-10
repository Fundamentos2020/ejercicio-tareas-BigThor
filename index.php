<?php
    include __DIR__.'\Controllers\tareasController.php';

    $tC = new TareasController();
    if(!isset($_GET['categoria_id']) || $_GET['categoria_id']=="")
    {
        $tC->index();
    }
    else
    {
        $tC->filtradasPorCategoria($_GET['categoria_id']);
    }
?>