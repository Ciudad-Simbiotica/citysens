<?php
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once('../db.php');
error_reporting(E_ERROR);
set_time_limit(0);

$link=connect();
mysql_query('SET CHARACTER SET utf8',$link);

$sql="SELECT * FROM lugares_shp WHERE (id>777000000 AND id<778000000) OR id=4357";
$result=mysql_query($sql,$link);
$regiones=array();
while($fila=mysql_fetch_assoc($result))
{
	array_push($regiones,$fila["id"]);
}
print_r($regiones);


foreach($regiones as $region)
{
		echo $region.PHP_EOL;
		if(!isset($combined_poly))
		{
			$combined_poly=geoPHP::load(file_get_contents("../shp/geoJSON/8/4357.geojson"),'json');
		}
		else
		{
			$new_poly=geoPHP::load(file_get_contents("../shp/geoJSON/7/$region.geojson"),'json');
			$combined_poly=$combined_poly->union($new_poly);
		}
}
file_put_contents("../shp/geoJSON/6/Madrid.geojson",$combined_poly->out('json'));


/*
$output="../shp/geoJSON/8/4407.fixed.geojson";


$poly1 = geoPHP::load(file_get_contents("../shp/geoJSON/8/4407.geojson"),'json');
$poly2 = geoPHP::load(file_get_contents("../shp/geoJSON/8/4383.geojson"),'json');

$combined_poly=$poly1->difference($poly2);

file_put_contents($output,$combined_poly->out('json'));
*/

?>