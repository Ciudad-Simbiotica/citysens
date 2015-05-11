<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();
// Links an address to the neighborhood it lies within (lugares_shp with level 10).

$nivel=10;  // Neighborhoods are level 10
$barrios=array();
$link=connect();
$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel' AND idPadre like '90128000%'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($barrios,$fila["id"]);
}

$direcciones=array();
$link=connect();
$sql="SELECT * FROM direcciones WHERE idBarrio<>'0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($direcciones,$fila);
	$asociados[$fila["idDireccion"]]="";
}

foreach($barrios as $barrio)
{
	//echo $barrio.PHP_EOL;
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$barrio.geojson"),'json');	

//print_r($poligono->asArray());
//.PHP_EOL;

	foreach($direcciones as $direccion)
	{
		$punto = geoPHP::load("POINT({$direccion['lng']} {$direccion['lat']})","wkt");
		if($poligono->contains($punto))
		{
			$asociados[$direccion["idDireccion"]]=$barrio;
		}
	}
}

foreach($asociados as $id=>$barrio)
{
	mysqli_query($link,"UPDATE direcciones SET idBarrio='$barrio' WHERE idDireccion='$id'");
	echo $barrio."\t".$id.PHP_EOL;
}



?>