<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();

$nivel=8;
$distritos=array();
$link=connect();
$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel'";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($distritos,$fila["id"]);
}

$direcciones=array();
$link=connect();
$sql="SELECT * FROM direcciones WHERE idDistritoPadre=0";
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
		$punto = geoPHP::load("POINT({$direccion['long']} {$direccion['lat']})","wkt");
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