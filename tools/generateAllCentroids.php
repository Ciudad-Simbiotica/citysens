<?php
set_time_limit(0);
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once "../db.php";

// Script is unactive unless it is required
// exit();

$link=connect();

$sql="SELECT * FROM lugares_shp WHERE id >='601150031' AND id <='601190052' and (xcentroid =0 OR xcentroid is NULL)";       
$result=mysqli_query($link, $sql);
$data=array();
while($fila=mysqli_fetch_assoc($result))
    array_push($data,$fila["id"]);



foreach($data as $idRegion)
{
	 //$idFichero=str_pad($fila[4],5,0,STR_PAD_LEFT);
     // This failed for Islas Baleares 601040007 and Santa Cruz de Tenerife 38 Girona 17 A coruña 15
	 $polygon = geoPHP::load(file_get_contents("../shp/geoJSON/6/$idRegion.geojson"),'json');
	 $area = $polygon->getArea();
	 $centroid = $polygon->getCentroid();
	 $centX = $centroid->getX();
	 $centY = $centroid->getY();
	 $bounds= $polygon->getBBox();
	 echo $fila[0]."\t".$centX."\t".$centY.PHP_EOL;
	 print_r($bounds);
	 $sql="UPDATE lugares_shp SET 
	  xcentroid='$centX', ycentroid='$centY',
	  xmin='{$bounds["minx"]}',ymin='{$bounds["miny"]}',xmax='{$bounds["maxx"]}',ymax='{$bounds["maxy"]}'
	  WHERE id='$idRegion'";
	 //echo $sql;
	 mysqli_query($link, $sql);
} 


?>