<?php
include_once "db.php";
error_reporting(0);

$eventosCoordenadas=getEventosCoordenadas($_GET["xmin"],$_GET["xmax"],$_GET["ymin"],$_GET["ymax"]);
echo json_encode($eventosCoordenadas);

?>