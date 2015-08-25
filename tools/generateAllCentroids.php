<?php
set_time_limit(0);
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once "../db.php";

// Script is unactive unless it is required
// For use  select id an direcotories targeted.
exit();

$link=connect();
echo "<pre>";

$nivel='9';

//$sql="SELECT * FROM territorios WHERE idPadre like '601100046%' and (xcentroid =0 OR xcentroid is NULL)";  
$sql="SELECT * FROM territorios WHERE id in ('1001282672','1001282434','1001282431','1001282414','1001282380','1001282361','1001282360') and nivel='$nivel'";
$sql="select * from territorios where id like '90128%' and idPadre='0'";

$result=mysqli_query($link, $sql);
$data=array();
while($fila=mysqli_fetch_assoc($result))
    array_push($data,$fila["id"]);



foreach($data as $idTerritorio)
{
	 //$idFichero=str_pad($fila[4],5,0,STR_PAD_LEFT);
     // This failed for Islas Baleares 601040007 and Santa Cruz de Tenerife 38 Girona 17 A coruña 15
	 $polygon = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$idTerritorio.geojson"),'json');
	 $area = $polygon->getArea();
	 $centroid = $polygon->getCentroid();
	 $centX = $centroid->getX();
	 $centY = $centroid->getY();
	 $bounds= $polygon->getBBox();
	 echo $fila[0]."\t".$centX."\t".$centY.PHP_EOL;
	 print_r($bounds);
	 $sql="UPDATE territorios SET 
	  xcentroid='$centX', ycentroid='$centY',
	  xmin='{$bounds["minx"]}',ymin='{$bounds["miny"]}',xmax='{$bounds["maxx"]}',ymax='{$bounds["maxy"]}'
	  WHERE id='$idTerritorio'";
	  echo $sql, PHP_EOL;
	 mysqli_query($link, $sql);
} 
echo "Finish!";

?>