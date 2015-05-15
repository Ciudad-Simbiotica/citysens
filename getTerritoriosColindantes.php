<?php
include_once "db.php";
error_reporting(0);

$territoriosColindantes=getTerritoriosColindantes($_GET["lugarOriginal"],$_GET["tipo"],$_GET["xmin"],$_GET["xmax"],$_GET["ymin"],$_GET["ymax"]);
echo json_encode($territoriosColindantes);

//	echo "addPolygonToMap('shp/geoJSON/8/{$fila["id"]}.geojson','{$fila["nombre"]}','#aaaaff');".PHP_EOL;

  
?>