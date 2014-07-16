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

$sql="SELECT idAsociacion FROM asociaciones";
$result=mysql_query($sql,$link);
$asociaciones=array();
while($fila=mysql_fetch_assoc($result))
{
	array_push($asociaciones,$fila["idAsociacion"]);
}

foreach($asociaciones as $asociacion)
{
	$tematicasAsociacion=array();
	$cantidadTematicas=rand(2,4);
	while(count($tematicasAsociacion)<$cantidadTematicas)
	{
		array_push($tematicasAsociacion, $tematicas[rand(0,259)]);
		$tematicasAsociacion=array_unique($tematicasAsociacion);
	}

	foreach($tematicasAsociacion as $tematica)
	{
		$sql="INSERT INTO asociaciones_tematicas (idAsociacion, idTematica) VALUES ('$asociacion','$tematica')";
		mysql_query($sql,$link);
	}
	echo $asociacion.PHP_EOL;
}

?>