<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

// script deactivated unless needed
exit();


// This scripts determines neighbouring cities and neighbourhoods given cities/neighbourhoods.

$link=connect();

$nivel=10;  // Level "city" (8) or neighborhood (10)
$provincia=28;
$territorios=array();
$vecinos=array();

//Take all cities from a certain province
//$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND provincia='$provincia'";
$sql="SELECT * FROM territorios WHERE nivel='$nivel'";

//To just take Alcalá
//$sql="SELECT * FROM territorios WHERE nivel='$nivel' AND id='801280005'";

$territorios=mysqli_query($link, $sql);

foreach($territorios as $territorio) {
  
//  if (!isset($territorio["vecinos"])) {
      $idTerritorio=$territorio["id"];
      $nombreTerritorio=$territorio["nombre"];
      $poli_ciudad = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$idTerritorio.geojson"),'json');	

      $cercanos=getTerritoriosColindantes($idTerritorio,$nivel,$territorio["xmin"]-0.002,$territorio["xmax"]+0.002,$territorio["ymin"]-0.002,$territorio["ymax"]+0.002);



      foreach($cercanos as $cercano)
        {
            $idCercano=$cercano["id"];
            $nombreCercano=$cercano["nombre"];
            $poli_cercano = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$idCercano.geojson"),'json');

            $distance=$poli_ciudad->distance($poli_cercano);

//            if ($distance<0.02) { // For cities (8), around 2km
              if ($distance<0.0025) { // For neighborhoods (10), around 350m
            
              array_push($vecinos,$idCercano);
            }
        }

      if (isset($vecinos)) {
        $sql="UPDATE territorios SET vecinos='".join($vecinos,",")."' WHERE id='$idTerritorio'";
        mysqli_query($link,$sql);    
        $vecinos=[];
      }
  }
//}


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