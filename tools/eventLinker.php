<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);


$link2=connect();

$eventos=getEventosCoordenadas(-19,5,0,44);
foreach($eventos as $evento)
{
	//print_r($evento);
	
	$nivel9=getColindantes(-1,9,$evento["x"]-0.0001,$evento["x"]+0.0001,$evento["y"]-0.0001,$evento["y"]+0.0001);
	$point = geoPHP::load("POINT ({$evento["x"]} {$evento["y"]})",'wkt');
	unset($distritoPadre);
    foreach($nivel9 as $distrito)
	{
	    $idFichero=str_pad($distrito[4],5,0,STR_PAD_LEFT);
		$poly1 = geoPHP::load(file_get_contents("../shp/geoJSON/9/$idFichero.geojson"),'json');	
		if($poly1->intersects($point))
			$distritoPadre=$distrito[0];
	}
	
	if(isset($distritoPadre))
	{
		$sql="UPDATE eventos SET idDistritoPadre='$distritoPadre' WHERE idEvento='{$evento["idEvento"]}'";
		mysql_query($sql,$link2);
		//echo $sql.PHP_EOL;
	}
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