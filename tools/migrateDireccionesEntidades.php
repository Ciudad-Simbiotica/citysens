<?php
include "../db.php";
error_reporting(E_ERROR);

// Script to migrate the Addresses that currently appear at Entidades to the Places table
// It creates a place with the entities address and adds a link at Entidades to the newly created place.

ini_set('default_charset', 'utf-8');

$idCiudad="888004284";  //Id of the city addresses to update. In this case, Alcalá de Henares
$entidades=array();
$link=connect();
mysql_query("SET NAMES 'utf8'");
$sql="SELECT * FROM entidades"; //Right now there is only entities from Alcalá
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	array_push($entidades,$fila);
}

foreach($entidades as $entidad)
{
    // Get relevant values to copy
    $strName=$entidad['entidad'];
    $strDireccion = $entidad['domicilio'];
    $strCP = $entidad['cp'];
    $idDistritoPadre= $entidad['idDistritoPadre'];
    
    $sql="INSERT INTO places (idPadre,idCiudad,nombre,direccion,indicacion,cp,lat,lng,zoom,placeStatus)
                           VALUES ('$idDistritoPadre','$idCiudad','$strName','$strDireccion','','$strCP','0','0','15','1')";  // idPadre?? Probably outdated.
    mysql_query($sql,$link);
    $insertID=mysql_insert_id();
    
    $asociados[$entidad['idEntidad']]=$insertID;
}

foreach($asociados as $idEntidad=>$idPlace)
{
	$sql = "UPDATE entidades SET idPlace='$idPlace' WHERE idEntidad='$idEntidad'";
    echo $sql.";\r\n";

    mysql_query($queryStr,$link);  
}

?>