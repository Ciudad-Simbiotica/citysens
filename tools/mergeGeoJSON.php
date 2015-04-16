<?php

//Script not active unless required
exit();
include_once('../vendor/phayes/geophp/geoPHP.inc');
include_once('../db.php');
error_reporting(E_ERROR);
set_time_limit(0);

//$comarca='701440001';//introducir numero comarca para crear polígono
$link = connect();
mysqli_query($link,'SET CHARACTER SET utf8');
$sql = "SELECT id FROM lugares_shp WHERE id like '701440%'"; //list of id regions
//$sql = "SELECT id FROM lugares_shp WHERE id='701030001'"; //SPECIFIC REGION 701030001
$result_comarcas = array();

$result_comarcas = mysqli_query($link,$sql);

echo $result_comarcas;
$regiones = array();

while ($comarca = mysql_fetch_assoc($result_comarcas)) {
    $comarca_id = $comarca["id"];
    $nombre_fichero = "../shp/geoJSON/C/" . $comarca_id . ".geojson";

    print($result_comarcas);

    if (file_exists($nombre_fichero)) {
        echo "La zona $nombre_fichero ya existe";
        $prueba = 0;
    } else {

        echo "La zona $nombre_fichero no existe, se procede a  crearlo";
        $prueba = 1;
        $sql = "SELECT id FROM lugares_shp WHERE idPadre=$comarca_id";
        $result = mysqli_query($link,$sql);
        while ($fila = mysqli_fetch_assoc($result)) {
            array_push($regiones, $fila["id"]);
        }
        print_r($regiones);


        foreach ($regiones as $region) {
            echo $region . PHP_EOL;
            if (!isset($combined_poly)) {
                $combined_poly = geoPHP::load(file_get_contents("../shp/geoJSON/8/$region.geojson"), 'json');
            } else {
                $new_poly = geoPHP::load(file_get_contents("../shp/geoJSON/8/$region.geojson"), 'json');
                $combined_poly = $combined_poly->union($new_poly);
            }
        }
        file_put_contents("../shp/geoJSON/CORREGIDOS/$comarca_id.geojson", $combined_poly->out('json')); //Path file
    unset($combined_poly);
    unset($regiones);
    $regiones = array();
        
            }
}
/*
  $output="../shp/geoJSON/8/4407.fixed.geojson";


  $poly1 = geoPHP::load(file_get_contents("../shp/geoJSON/8/4407.geojson"),'json');
  $poly2 = geoPHP::load(file_get_contents("../shp/geoJSON/8/4383.geojson"),'json');

  $combined_poly=$poly1->difference($poly2);

  file_put_contents($output,$combined_poly->out('json'));
 */
?>