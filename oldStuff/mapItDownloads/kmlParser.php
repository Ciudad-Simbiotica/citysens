<?php

$simple = file_get_contents("DistritosAlcala.kml");
$xml = simplexml_load_string($simple);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
foreach($array["Document"]["Folder"]["Placemark"] as $data)
{
	echo $data["name"].PHP_EOL;
	$coordenadas=split(" ",str_replace("\t","",str_replace("\n","",$data["Polygon"]["outerBoundaryIs"]["LinearRing"]["coordinates"])));
	$first=true;

	$geoJSON='{"type":"Polygon","coordinates":[[';
	foreach($coordenadas as $coordenada)
	{
		if($coordenada!="")
		{
			$coordenada=split(",",$coordenada);
			$coordenada="[{$coordenada[0]},{$coordenada[1]}]";
			if(!$first)
				$geoJSON.=",";
			$first=false;
			$geoJSON.=$coordenada;
		}
	}
	$geoJSON.=']]}';
	file_put_contents($data["name"].".geojson", $geoJSON);
}

?>