<?php
include_once "db.php";
error_reporting(0);

$lugaresColindantes=getColindantes($_GET["lugarOriginal"],$_GET["tipo"],$_GET["xmin"],$_GET["xmax"],$_GET["ymin"],$_GET["ymax"]);
echo json_encode($lugaresColindantes);

//	echo "addPolygonToMap('shp/geoJSON/8/{$fila["id"]}.geojson','{$fila["nombre"]}','#aaaaff');".PHP_EOL;

  
?>