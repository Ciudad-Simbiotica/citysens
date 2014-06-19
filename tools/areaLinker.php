<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

exit();

$link2=connect();

$nivel9=getColindantes(-1,9,-19,5,0,44);

foreach($nivel9 as $distrito)
{
	//print_r($distrito);
	$idFichero=str_pad($distrito[4],5,0,STR_PAD_LEFT);
	$poly1 = geoPHP::load(file_get_contents("../shp/geoJSON/9/$idFichero.geojson"),'json');	
	$boundingBox=$poly1->getBBox();
	//print_r($boundingBox);
	$nivel8=getColindantes(-1,8,$boundingBox["minx"],$boundingBox["maxx"],$boundingBox["miny"],$boundingBox["maxy"]);
	$maxArea=-1;
	$idMunicipioMax=-1;
	$municipioMax="";
	foreach($nivel8 as $municipio)
	{
		$poly2 = geoPHP::load(file_get_contents("../shp/geoJSON/8/{$municipio[0]}.geojson"),'json');	
		try{
			$intersect_poly=$poly1->intersection($poly2);
		}
		catch(Exception $e)
		{

		}
		if($intersect_poly->area()>$maxArea)
		{
			$maxArea=$intersect_poly->area();
			$idMunicipioMax=$municipio[0];
			$municipioMax=$municipio[1];
		}
	}
	$sql="UPDATE lugares_shp SET idPadre='$idMunicipioMax' WHERE id='{$distrito[0]}'";
	mysql_query($sql,$link2);
	//echo $sql;
	echo $municipioMax.PHP_EOL;
}


/*

$poly2 = geoPHP::load(file_get_contents("shp/geoJSON/8/4420.geojson"),'json');

$combined_poly=$poly1->union($poly2);
/*foreach ($input as $key => $value) 
{
}
*/

//file_put_contents($output,$combined_poly->out('json'));


?>