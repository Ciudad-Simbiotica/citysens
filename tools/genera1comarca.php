<?php

//Script not active unless required
exit();
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once('../db.php');
error_reporting(E_ERROR);
set_time_limit(0);

$link = connect();
$comarca_id='701460006';
        $sql = "SELECT * FROM lugares_shp WHERE idPadre='$comarca_id'";// Introducir aqui id de comarca
        $result = mysqli_query($link, $sql);
        while ($fila = mysqli_fetch_assoc($result)) {
           $idmunicipio = $fila["id"];
           
           if (!isset($combined_poly)) {
                $combined_poly = geoPHP::load(file_get_contents("../shp/geoJSON/8/$idmunicipio.geojson"), 'json');
            } else {
                $new_poly = geoPHP::load(file_get_contents("../shp/geoJSON/8/$idmunicipio.geojson"), 'json');
                $combined_poly = $combined_poly->union($new_poly);
            }
           
        }

    file_put_contents("../shp/geoJSON/CORREGIDOS/$comarca_id.geojson", $combined_poly->out('json')); //Path file
        
?>