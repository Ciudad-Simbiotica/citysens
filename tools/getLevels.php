<?php
include_once "../db.php";
error_reporting(0);

$lugaresColindantes=getLevels($_GET["provincia"],$_GET["tipo"]);
echo json_encode($lugaresColindantes);

//	echo "addPolygonToMap('shp/geoJSON/8/{$fila["id"]}.geojson','{$fila["nombre"]}','#aaaaff');".PHP_EOL;

  
?>