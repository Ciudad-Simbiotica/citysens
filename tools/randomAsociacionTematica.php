<?php
include_once "../db.php";
error_reporting(0);

$link=connect();
$sql="SELECT idTematica FROM tematicas";
$result=mysql_query($sql,$link);
$tematicas=array();
while($fila=mysql_fetch_assoc($result))
{
	array_push($tematicas,$fila["idTematica"]);
}

$sql="SELECT idEntidad FROM entidades";
$result=mysql_query($sql,$link);
$entidades=array();
while($fila=mysql_fetch_assoc($result))
{
	array_push($entidades,$fila["idEntidad"]);
}

foreach($entidades as $entidad)
{
	$tematicasEntidad=array();
	$cantidadTematicas=rand(2,4);
	while(count($tematicasEntidad)<$cantidadTematicas)
	{
		array_push($tematicasEntidad, $tematicas[rand(0,29)]);
		$tematicasEntidad=array_unique($tematicasEntidad);
	}

	foreach($tematicasEntidad as $tematica)
	{
		$sql="INSERT INTO entidades_tematicas (idEntidad, idTematica) VALUES ('$entidad','$tematica')";
		mysql_query($sql,$link);
	}
	echo $entidad.PHP_EOL;
}

?>