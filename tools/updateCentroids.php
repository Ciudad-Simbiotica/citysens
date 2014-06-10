<?php
set_time_limit(0);
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once "../db.php";

exit();


$data=getColindantes(-1,9,-19,5,0,44);

$link=connect();
foreach($data as $fila)
{
	 $idFichero=str_pad($fila[4],5,0,STR_PAD_LEFT);
	 $polygon = geoPHP::load(file_get_contents("../shp/geoJSON/9/$idFichero.geojson"),'json');
	 $area = $polygon->getArea();
	 $centroid = $polygon->getCentroid();
	 $centX = $centroid->getX();
	 $centY = $centroid->getY();
	 echo $fila[0]."\t".$centX."\t".$centY.PHP_EOL;
	 $sql="UPDATE lugares_shp SET xcentroid='$centX', ycentroid='$centY' WHERE id='{$fila[0]}'";
	 mysql_query($sql,$link);
} 


?>