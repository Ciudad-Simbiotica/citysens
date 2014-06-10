<?php
set_time_limit(0);
error_reporting(E_ERROR);
include "db.php";

$link=connect();
$sql="SELECT * FROM eventos";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$data=file_get_contents("AgendaKML/{$fila["idEvento"]}.kml");
	$xml = simplexml_load_string($data);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);

	$coordinates=split(",",$array["Document"]["Placemark"]["Point"]["coordinates"]);
	//print_r($coordinates);
	$sql="UPDATE eventos SET x={$coordinates["0"]}, y={$coordinates["1"]} WHERE idEvento={$fila["idEvento"]}";
	mysql_query($sql,$link);


	/*
	$url="http://agendadelhenares.org/kml/{$fila["idEvento"]}?cs=d03fa";
	echo "<A HREF='$url'>$url</A><BR>".PHP_EOL;
	file_put_contents("AgendaKML/{$fila["idEvento"]}.kml", file_get_contents($url));
	//exit();
	for($i=0;$i<=10;$i++)
	{
		sleep(1);
		echo ".";flush();
	}
	echo "PHP_EOL";*/
}
?>