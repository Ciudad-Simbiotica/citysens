<?php
exit('Exit de emergencia');
set_time_limit(0);
error_reporting(E_ALL);

$lugares=json_decode(file_get_contents("Provincias/522175-covers-madrid.json"),true);

$i=0;
foreach($lugares as $id=>$lugar)
{
	$i++;
	echo $i."/".count($lugares).PHP_EOL;
	$detalles="http://global.mapit.mysociety.org/area/$id";
	$poligono="http://global.mapit.mysociety.org/area/$id.geojson";
	echo $detalles;
	file_put_contents("Madrid/$id.json",file_get_contents($detalles));
	echo " - OK".PHP_EOL;
	sleep(2);
	echo $poligono;
	file_put_contents("Madrid/$id.geojson",file_get_contents($poligono));
	echo " - OK".PHP_EOL;
	sleep(2);
	//exit();		
}

?>