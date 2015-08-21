<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();
// Links an address to the neighborhood it lies within (lugares_shp with level 10).

$nivel=10;  // Neighborhoods are level 10
$barrios=array();
$link=connect();
$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND idPadre like '90128%'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($barrios,$fila["id"]);
}

$places=array();
$link=connect();
$sql="SELECT * FROM places WHERE idBarrio<>'0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($places,$fila);
	$asociados[$fila["idPlace"]]="";
}

foreach($barrios as $barrio)
{
	//echo $barrio.PHP_EOL;
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$barrio.geojson"),'json');	

//print_r($poligono->asArray());
//.PHP_EOL;

	foreach($places as $place)
	{
		$punto = geoPHP::load("POINT({$place['lng']} {$place['lat']})","wkt");
		if($poligono->contains($punto))
		{
			$asociados[$place["idPlace"]]=$barrio;
		}
	}
}

foreach($asociados as $id=>$barrio)
{
	mysqli_query($link,"UPDATE places SET idBarrio='$barrio' WHERE idPlace='$id'");
	echo $barrio."\t".$id.PHP_EOL;
}



?>