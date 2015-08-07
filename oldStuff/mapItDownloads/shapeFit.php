<?php

function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2) 
{
    return sqrt(pow(($latitude1-$latitude2),2)+pow(($longitude1-$longitude2),2));
}

function extractCoordinates($coordinates,$from,$to,$reverse=false)
{
	if($to>$from)
	{
		$arrayFinal=array_splice($coordinates, $from,$to-$from+1);
	}
	else
	{
		$arrayFinal=array_splice($coordinates, $from,count($coordinates)-$from+1);
		$arrayFinal2=array_splice($coordinates, 0,$to-0+1);
		foreach($arrayFinal2 as $datos)
			array_push($arrayFinal,$datos);
	}

	if($reverse)
		$arrayFinal=array_reverse($arrayFinal);
	return $arrayFinal;
}

$mapit=json_decode(file_get_contents("Madrid/579300.geojson"),true);
$toFit=json_decode(file_get_contents("Madrid/Distrito II.geojson"),true);


$arrayFinal1=extractCoordinates($mapit["coordinates"][0],339,521);
$arrayFinal2=extractCoordinates($toFit["coordinates"][0],580,84,true);

$geoJSON='{"type":"Polygon","coordinates":[[';

$first=true;
foreach($arrayFinal1 as $coordenada)
{
		$coordenada="[{$coordenada[0]},{$coordenada[1]}]";
		if(!$first)
			$geoJSON.=",";
		$first=false;
		$geoJSON.=$coordenada;
}
foreach($arrayFinal2 as $coordenada)
{
		$coordenada="[{$coordenada[0]},{$coordenada[1]}]";
		if(!$first)
			$geoJSON.=",";
		$first=false;
		$geoJSON.=$coordenada;
}




$geoJSON.=']]}';
file_put_contents("DistritoII.geojson", $geoJSON);

?>