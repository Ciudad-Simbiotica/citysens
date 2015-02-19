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
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($distritos,$fila["id"]);
}

$direcciones=array();
$link=connect();
$sql="SELECT * FROM direcciones WHERE idPadre=0";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($direcciones,$fila);
	$asociados[$fila["idDireccion"]]="";
}

foreach($distritos as $distrito)
{
	echo $distrito.PHP_EOL;
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
	mysql_query("UPDATE direcciones SET idPadre='$distrito' WHERE idDireccion='$id'");
	echo $distrito."\t".$id.PHP_EOL;
}



?>