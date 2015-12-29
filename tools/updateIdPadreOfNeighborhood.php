<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);
set_time_limit(0);

exit();
// Update idPadre of territories of to the parent territory that contains its centroid.

echo "<pre>";

$levelParent=9;
$parentTerritories=array();
$link=connect();
//$sql="SELECT * FROM territorios WHERE id like '80128%' and idPadre='701280008'"; //Take all Distritos de Madrid
$sql="SELECT * FROM citysens.territorios where idPadre in (select id from territorios where idPadre='701280008')";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($parentTerritories,$fila);
}

//Extraemos los centroides de los Barrios

$territories=array();
$levelSon=$levelParent+1;
$sql="SELECT * FROM territorios WHERE nivel='$levelSon' AND id like '100128%'";
$result=mysqli_query($link,$sql);
while($territory=mysqli_fetch_assoc($result))
{
	array_push($territories,$territory);
	$asociados[$territory["id"]]="";
}

foreach($parentTerritories as $parentTerritory)
{
	//echo $barrio.PHP_EOL;
    
   $idParent=$parentTerritory["id"];
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$levelParent/$idParent.geojson"),'json');
   echo "PARENT ID: ".$idParent.PHP_EOL;
//print_r($poligono->asArray());
//.PHP_EOL;
// hay que marcarle los centroides de los hijos y ver si pertenecen al papi
	foreach($territories as $key=>$territory)
	{
		$centroidSon = geoPHP::load("POINT({$territory['xcentroid']} {$territory['ycentroid']})","wkt");
		if($poligono->contains($centroidSon))
		{
			$asociados[$territory["id"]]=$idParent;
                        // Since we have already found it... remove it from territories, to avoid to process it again.
                        unset($territories[$key]);
                        echo "ENCONTRADO: ".$territory["id"].PHP_EOL;
		}
	}
}

foreach($asociados as $id=>$idParent)
{
	if ($idParent!="")
      mysqli_query($link,"UPDATE territorios SET idPadre='$idParent' WHERE id='$id'");
	echo $id."\t".$idParent.PHP_EOL;
}

?>
