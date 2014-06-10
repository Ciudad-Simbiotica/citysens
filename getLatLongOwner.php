<?php
include_once('vendor/phayes/geophp/geoPHP.inc');

$polygon = geoPHP::load(file_get_contents('shp/geoJSON/8/4404.geojson'),'json');
$area = $polygon->getArea();
$centroid = $polygon->getCentroid();
$centX = $centroid->getX();
$centY = $centroid->getY();

print "This polygon has an area of ".$area." and a centroid with X=".$centX." and Y=".$centY;



?>