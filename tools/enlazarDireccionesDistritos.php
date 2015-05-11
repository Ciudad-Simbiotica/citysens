<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();
// Links an address to the district it lies within (lugares_shp with level 9).

$nivel=9;  // Districts are level 9
$distritos=array();
$link=connect();
$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel' AND idPadre=801280005";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($distritos,$fila["id"]);
}

$direcciones=array();
$link=connect();
$sql="SELECT * FROM direcciones WHERE idDistrito='0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($direcciones,$fila);
	$asociados[$fila["idDireccion"]]="";
}

foreach($distritos as $distrito)
{
	//echo $distrito.PHP_EOL;
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$distrito.geojson"),'json');	

	//print_r($poligono->asArray());//.PHP_EOL;

	foreach($direcciones as $direccion)
	{
		$punto = geoPHP::load("POINT({$direccion['lng']} {$direccion['lat']})","wkt");
		if($poligono->contains($punto))
		{
			$asociados[$direccion["idDireccion"]]=$distrito;
		}
	}
}

foreach($asociados as $id=>$distrito)
{
	mysqli_query($link,"UPDATE direcciones SET idDistrito='$distrito' WHERE idDireccion='$id'");
	echo $distrito."\t".$id.PHP_EOL;
}



?>