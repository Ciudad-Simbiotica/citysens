<?php
include_once('vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ALL);

$input=array(4284,4420);
$output="AlcalaTorrejon.geojson";


$poly1 = geoPHP::load(file_get_contents("shp/geoJSON/8/4284.geojson"),'json');
$poly2 = geoPHP::load(file_get_contents("shp/geoJSON/8/4420.geojson"),'json');

$combined_poly=$poly1->union($poly2);
/*foreach ($input as $key => $value) 
{
}
*/

file_put_contents($output,$combined_poly->out('json'));


?>