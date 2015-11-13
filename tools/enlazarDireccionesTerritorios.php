<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);
set_time_limit(0);

// Disabled unless needed
exit();

// Links addresses of a province to the corresponding city (level 8), disctrict (9) and neighborhood (10) territories

$provincia=28;

$link=connect();

  $territorios=array();
  //Take all territories of level $nivel in a certain province
  $sql="SELECT * FROM territorios WHERE nivel='8' AND provincia='$provincia'";

  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
      array_push($territorios,$fila["id"]);
  }
  
  // Takes addresses that are not linked to any city
    $sql="SELECT * FROM places WHERE idCiudad is null OR idCiudad='0'";
  
  $places=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
      array_push($places,$fila);
      $asociados[$fila["idPlace"]]="";
  }
  
  if (!empty($places)){
    foreach($territorios as $territorio) {
        echo $territorio.PHP_EOL;
        $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/8/$territorio.geojson"),'json');	

        //print_r($poligono->asArray());//.PHP_EOL;

        foreach($places as $key=>$place)
        {
            $punto = geoPHP::load("POINT({$place['lng']} {$place['lat']})","wkt");

            if($poligono->contains($punto))
            {
                $asociados[$place["idPlace"]]=$territorio;
                // Since we have already found it... remove it from places, to avoid to process it again. Will it work?
                unset($places[$key]);
            }
        }
    }

    foreach($asociados as $id=>$territorio)
    {
      mysqli_query($link,"UPDATE places SET idCiudad='$territorio' WHERE idPlace='$id'");
    }
  }

  $territorios=array();
  $sql="SELECT * FROM places WHERE (idDistrito is null OR idDistrito='0') AND idCiudad is not null AND idCiudad<>'0'";
  
  $places=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
//      array_push($places,$fila);
      $idCiudad=$fila["idCiudad"];

      $territorios=array();
      //Take all territories of level $nivel in a certain province
      $sql="SELECT * FROM territorios WHERE nivel='9' AND idPadre='$idCiudad'";

      $distritos=mysqli_query($link,$sql);
      while($distrito=mysqli_fetch_assoc($distritos))
      {
          array_push($territorios,$distrito["id"]);
      }  
  

        foreach($territorios as $territorio) {
            echo $territorio.PHP_EOL;
            $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/9/$territorio.geojson"),'json');	

            //print_r($poligono->asArray());//.PHP_EOL;

                $punto = geoPHP::load("POINT({$fila['lng']} {$fila['lat']})","wkt");

                if($poligono->contains($punto))
                {
                    $asociados[$fila["idPlace"]]=$territorio;
                }
  }
  
    foreach($asociados as $id=>$territorio)
    {
          mysqli_query($link,"UPDATE places SET idDistrito='$territorio' WHERE idPlace='$id'");
    }
  }

    $territorios=array();
    $sql="SELECT * FROM places WHERE (idBarrio is null OR idBarrio='0') AND idCiudad is not null AND idCiudad<>'0'";
  
  $places=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
//      array_push($places,$fila);
      $idCiudad=$fila["idCiudad"];

      $territorios=array();
      //Take all territories of level $nivel in a certain province
      $sql="SELECT barrio.* FROM territorios as distrito, territorios as barrio WHERE barrio.nivel='10' AND barrio.idPadre=distrito.id AND distrito.idPadre='$idCiudad'";

      $barrios=mysqli_query($link,$sql);
      while($barrio=mysqli_fetch_assoc($barrios))
      {
          array_push($territorios,$barrio["id"]);
      }  
  

        foreach($territorios as $territorio) {
            echo $territorio.PHP_EOL;
            $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/10/$territorio.geojson"),'json');	

            //print_r($poligono->asArray());//.PHP_EOL;

                $punto = geoPHP::load("POINT({$fila['lng']} {$fila['lat']})","wkt");

                if($poligono->contains($punto))
                {
                    $asociados[$fila["idPlace"]]=$territorio;
                }
  }
  
    foreach($asociados as $id=>$territorio)
    {
          mysqli_query($link,"UPDATE places SET idBarrio='$territorio' WHERE idPlace='$id'");
    }
  }
  echo "HECHO!";
?>