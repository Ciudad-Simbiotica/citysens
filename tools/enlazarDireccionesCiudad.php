<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();
// Script based on enlazarDireccionesDistritos, that links an address to its city.
// Probably the best will be to have a single script that links to city, disctrict and neighborhood.
 
$nivel=8;  // Level "city"
$provincia=28;
$ciudades=array();
$link=connect();

//Take all cities from a certain province
$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel' AND provincia='$provincia'";

//To just take Alcalá
//$sql="SELECT * FROM lugares_shp WHERE nivel='$nivel' AND id='801280005'";

$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($ciudades,$fila["id"]);
}

$direcciones=array();
// $link=connect();  // Probably not needed?
// Takes addresses that are not linked to any city
$sql="SELECT * FROM direcciones WHERE idCiudad=0";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($direcciones,$fila);
	$asociados[$fila["idDireccion"]]="";
}

foreach($ciudades as $ciudad)
{
	echo $ciudad.PHP_EOL;
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$ciudad.geojson"),'json');	

	//print_r($poligono->asArray());//.PHP_EOL;

	foreach($direcciones as $key=>$direccion)
	{
		$punto = geoPHP::load("POINT({$direccion['lng']} {$direccion['lat']})","wkt");
        
		if($poligono->contains($punto))
		{
			$asociados[$direccion["idDireccion"]]=$ciudad;
            // Since we have already found it... remove it from direcciones, to avoid to process it again. Will it work?
            unset($direcciones[$key]);
		}
	}
}

foreach($asociados as $id=>$ciudad)
{
	mysql_query("UPDATE direcciones SET idCiudad='$ciudad' WHERE idDireccion='$id'");
	echo $ciudad."\t".$id.PHP_EOL;
}



?>