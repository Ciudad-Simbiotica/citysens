<?php
include "../db.php";
$link=connect();
$sql="SELECT *  
      FROM lugares_shp
      WHERE nivel='9'";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$data=file_get_contents("../shp/geoJSON/9/".str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT).".geoJSON");
	file_put_contents("../shp/geoJSON/9/".$fila["id"].".geoJSON",$data);
	//print_r();
}
?>