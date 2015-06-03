<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);
set_time_limit(0);

exit();
// Update idPadre of territories of level 10 (neighborhood) to the district that contains its centroid.

$distritos=array();
$link=connect();
$sql="SELECT * FROM territorios WHERE id like '90128%'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	array_push($distritos,$fila);
}

//Extraemos los centroides de los Barrios

$barrios=array();
$sql="SELECT * FROM territorios WHERE nivel='10' AND id>1001280056";
$result=mysqli_query($link,$sql);
while($barrio=mysqli_fetch_assoc($result))
{
	array_push($barrios,$barrio);
	$asociados[$barrio["id"]]="";
}

foreach($distritos as $distrito)
{
	//echo $barrio.PHP_EOL;
    
        $idDistrito=$distrito["id"];
	$poligono = geoPHP::load(file_get_contents("../shp/geoJSON/9/$idDistrito.geojson"),'json');
        echo "DISTRITO: ".$idDistrito.PHP_EOL;
//print_r($poligono->asArray());
//.PHP_EOL;
// hay que marcarle los centroides de los hijos y ver si pertenecen al papi
	foreach($barrios as $key=>$barrio)
	{
		$centroideBarrio = geoPHP::load("POINT({$barrio['xcentroid']} {$barrio['ycentroid']})","wkt");
		if($poligono->contains($centroideBarrio))
		{
			$asociados[$barrio["id"]]=$idDistrito;
                        // Since we have already found it... remove it from barrios, to avoid to process it again.
                        unset($barrios[$key]);
                        echo "ENCONTRADO: ".$barrio["id"].PHP_EOL;
		}
	}
}

foreach($asociados as $id=>$idDistrito)
{
	mysqli_query($link,"UPDATE territorios SET idPadre='$idDistrito' WHERE id='$id'");
	echo $id."\t".$idDistrito.PHP_EOL;
}

?>
