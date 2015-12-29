<?php
set_time_limit(0);
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once "../db.php";

// Script is unactive unless it is required
// For use  select id an direcotories targeted.
exit();

$link=connect();
echo "<pre>";

$nivel='6';

//$sql="SELECT * FROM territorios WHERE idPadre like '601100046%' and (xcentroid =0 OR xcentroid is NULL)";  
//$sql="SELECT * FROM territorios WHERE id in ('1001282672','1001282434','1001282431','1001282414','1001282380','1001282361','1001282360') and nivel='$nivel'";
//$sql="select * from territorios where id like '90128%' and idPadre='0'";
$sql="select * from territorios where nivel=6 and xcentroid is null";

$result=mysqli_query($link, $sql);
$data=array();
while($fila=mysqli_fetch_assoc($result))
{
	 //$idFichero=str_pad($fila[4],5,0,STR_PAD_LEFT);
     // This failed for Islas Baleares 601040007 and Santa Cruz de Tenerife 38 Girona 17 A coruña 15
   
    try {
      $polygon = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/{$fila["id"]}.geojson"), 'json');
      $area = $polygon->getArea();
      $centroid = $polygon->getCentroid();
      $centX = $centroid->getX();
      $centY = $centroid->getY();
      $bounds = $polygon->getBBox();
      $minx = $bounds["minx"];
      $miny = $bounds["miny"];
      $maxx = $bounds["maxx"];
      $maxy = $bounds["maxy"];
      if ($centX == null) { //For cases where it failed, make a simple calculation (as for provinces we have the max, min coordinates
         // In fact this didn't worked, because failure happened when loading the poligon, and never reached here. But we outcommented the failing code.
         $centX = ($fila["xmin"] + $fila["xmax"]) / 2;
         $centY = ($fila["ymin"] + $fila["ymax"]) / 2;
         $minx = $fila["xmin"];
         $miny = $fila["ymin"];
         $maxx = $fila["xmax"];
         $maxy = $fila["ymax"];
      }
   }
	catch (Exception $e) {
      echo 'Excepción' . $e . PHP_EOL;
      //For cases where it failed, make a simple calculation (as for provinces we have the max, min coordinates
      // In fact this didn't worked, because failure happened when loading the poligon, and never reached here. But we outcommented the failing code.
      $centX = ($fila["xmin"] + $fila["xmax"]) / 2;
      $centY = ($fila["ymin"] + $fila["ymax"]) / 2;
      $minx = $fila["xmin"];
      $miny = $fila["ymin"];
      $maxx = $fila["xmax"];
      $maxy = $fila["ymax"];
   }
	 
	 echo $fila[0]."\t".$centX."\t".$centY.PHP_EOL;
	 //print_r($bounds);
	 $sql="UPDATE territorios SET 
	  xcentroid='$centX', ycentroid='$centY',
	  xmin='$minx',ymin='$miny',xmax='$maxx',ymax='$maxy'
	  WHERE id='{$fila["id"]}'";
	  echo $sql, PHP_EOL;
	 mysqli_query($link, $sql);
} 
echo "Finish!";

?>