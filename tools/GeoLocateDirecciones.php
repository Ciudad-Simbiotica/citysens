<?php
include "../db.php";
error_reporting(E_ERROR);

// Script to get from Google GeoCode API the lat and lng of the directions of a city

ini_set('default_charset', 'utf-8');

$idCiudad="888004284";  //Id of the city addresses to update. In this case, Alcalá de Henares
$direcciones=array();
$link=connect();
mysql_query("SET NAMES 'utf8'");
$sql="SELECT * FROM lugares_shp WHERE id='$idCiudad'";
$result=mysql_query($sql,$link);
$area=mysql_fetch_assoc($result);
$nombreCiudad=$area["nombre"];
    
$sql="SELECT * FROM direcciones WHERE idCiudad='$idCiudad' AND lat='0'";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($direcciones,$fila);
}

foreach($direcciones as $direccion)
{
    $idDireccion=$direccion['idDireccion'];
    $strDireccion = $direccion['direccion'];
    $strDireccion = $strDireccion.", ".$nombreCiudad;
    echo PHP_EOL, $strDireccion, PHP_EOL;
    
    $strDireccionClean = str_replace (" ", "+", $strDireccion);
    $respuesta=json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$strDireccionClean."&sensor=false"),true);
    
    if ($respuesta['status']=='OK') {
        $lat=$respuesta['results'][0]['geometry']['location']['lat']; // get lat for json
        $lng=$respuesta['results'][0]['geometry']['location']['lng']; // get lng for json
        $queryStr = "UPDATE direcciones SET lat='$lat', lng='$lng' WHERE idDireccion='$idDireccion'";
        echo $queryStr, PHP_EOL;

        mysql_query($queryStr);
    }    
}

?>