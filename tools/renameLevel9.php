<?php
include "../db.php";
$link=connect();
$sql="SELECT *  
      FROM lugares_shp
      WHERE nivel='8'";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$newID=$fila["id"]+888000000;
	$data=file_get_contents("../shp/geoJSON/8/".$fila["id"].".geoJSON");
	file_put_contents("../shp/geoJSON/8/".$newID.".geoJSON",$data);
	echo $newID.PHP_EOL;
	//exit();
	//print_r();
}
?>