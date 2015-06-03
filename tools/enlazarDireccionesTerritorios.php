<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);
set_time_limit(0);

// Disabled unless needed
//exit();

// Links addresses of a province to the correponding city (level 8), disctrict (9) and neighborhood (10) territories

$provincia=28;

$link=connect();

for ($nivel=8; $nivel<=10;$nivel++) {

  $territorios=array();
  //Take all territories of level $nivel in a certain province
  $sql="SELECT * FROM territorios WHERE nivel='$nivel' AND provincia='$provincia'";

  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
      array_push($territorios,$fila["id"]);
  }
  
  if ($nivel==8)
  // Takes addresses that are not linked to any city
    $sql="SELECT * FROM direcciones WHERE idCiudad is null";
  else if ($nivel==9)
    $sql="SELECT * FROM direcciones WHERE idDistrito is null";
  else
    $sql="SELECT * FROM direcciones WHERE idBarrio is null";
  
  $direcciones=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
      array_push($direcciones,$fila);
      $asociados[$fila["idDireccion"]]="";
  }
  
  if (!empty($direcciones)){
    foreach($territorios as $territorio) {
        echo $territorio.PHP_EOL;
        $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/$nivel/$territorio.geojson"),'json');	

        //print_r($poligono->asArray());//.PHP_EOL;

        foreach($direcciones as $key=>$direccion)
        {
            $punto = geoPHP::load("POINT({$direccion['lng']} {$direccion['lat']})","wkt");

            if($poligono->contains($punto))
            {
                $asociados[$direccion["idDireccion"]]=$territorio;
                // Since we have already found it... remove it from direcciones, to avoid to process it again. Will it work?
                unset($direcciones[$key]);
            }
        }
    }

    foreach($asociados as $id=>$territorio)
    {
        if ($nivel==8)
          mysqli_query($link,"UPDATE direcciones SET idCiudad='$territorio' WHERE idDireccion='$id'");
        else if ($nivel==9)
          mysqli_query($link,"UPDATE direcciones SET idDistrito='$territorio' WHERE idDireccion='$id'");
        else
          mysqli_query($link,"UPDATE direcciones SET idBarrio='$territorio' WHERE idDireccion='$id'");
    }
  }

}
echo "HECHO!";

?>