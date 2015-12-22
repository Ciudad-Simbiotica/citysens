<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);
set_time_limit(0);

// script deactivated unless needed
exit();


// This scripts determines neighbouring territories

$link=connect();

$nivel=9;  // Originally it was for "city" (8) or neighborhood (10), but now it can be applied to everything but states (4)
$provincia=28;
$soloNuevos=FALSE;
$territorios=array();
$vecinos=array();
echo "<pre>";

//Take all territories with a certain level from a given province
$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND provincia='$provincia'";

//$sql="SELECT * FROM territorios WHERE nivel='$nivel'";
//$sql="SELECT * FROM territorios WHERE nivel='$nivel'";

// Todos los barrios de Madrid
//$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND provincia='$provincia' and idPadre in (select id from territorios where nivel=9 and idPadre in (select id from territorios where nivel=8 and idPadre='701280008'))";

//To just take Alcalá
//$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND id='801280005'";

$territorios=mysqli_query($link, $sql);

foreach ($territorios as $territorio) {

   if ($territorio['xmax'] && $territorio['xcentroid'] && (!$soloNuevos || !$territorio["vecinos"]) && $territorio['nombreCorto']!='IB' && $territorio['nombreCorto']!='TF') {
      $idTerritorio = $territorio["id"];
      $nombreTerritorio = $territorio["nombre"];

         $poli_ciudad = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$idTerritorio.geojson"), 'json');

      $cercanos = getTerritoriosColindantes($idTerritorio, $nivel, $territorio["xmin"] - 0.02, $territorio["xmax"] + 0.02, $territorio["ymin"] - 0.02, $territorio["ymax"] + 0.02);

      foreach ($cercanos as $cercano) {
         $idCercano = $cercano["id"];
         $nombreCercano = $cercano["nombre"];
         $poli_cercano = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$idCercano.geojson"), 'json');

         $distance = $poli_ciudad->distance($poli_cercano);

//         if ($distance < 0.06) { // For provinces and comarcas (6 y 97), around 6km
//         if ($distance<0.02) { // For cities and districts (8), around 2km
         if ($distance<0.0025) { // For neighborhoods and districts (10 y 9), around 350m
            array_push($vecinos, $idCercano);
         }
      }

      if (isset($vecinos)) {
         $sql = "UPDATE territorios SET vecinos='" . join($vecinos, ",") . "' WHERE id='$idTerritorio'";
         echo $sql, PHP_EOL;
         mysqli_query($link, $sql);
         $vecinos = [];
      }
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

echo 'finish!';
?>