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

$sql="SELECT idEvento FROM eventos";
$result=mysql_query($sql,$link);
$eventos=array();
while($fila=mysql_fetch_assoc($result))
{
	array_push($eventos,$fila["idEvento"]);
}

foreach($eventos as $evento)
{
	$tematicasEvento=array();
	$cantidadTematicas=rand(2,4);
	while(count($tematicasEvento)<$cantidadTematicas)
	{
		array_push($tematicasEvento, $tematicas[rand(0,259)]);
		$tematicasEvento=array_unique($tematicasEvento);
	}

	foreach($tematicasEvento as $tematica)
	{
		$sql="INSERT INTO eventos_tematicas (idEvento, idTematica) VALUES ('$evento','$tematica')";
		mysql_query($sql,$link);
	}
	echo $evento.PHP_EOL;
}

?>