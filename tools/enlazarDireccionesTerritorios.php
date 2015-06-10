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

  $territorios=array();
  //Take all territories of level $nivel in a certain province
  $sql="SELECT * FROM territorios WHERE nivel='8' AND provincia='$provincia'";

  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
      array_push($territorios,$fila["id"]);
  }
  
  // Takes addresses that are not linked to any city
    $sql="SELECT * FROM direcciones WHERE idCiudad is null OR idCiudad='0'";
  
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
        $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/8/$territorio.geojson"),'json');	

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
      mysqli_query($link,"UPDATE direcciones SET idCiudad='$territorio' WHERE idDireccion='$id'");
    }
  }

  
  $sql="SELECT * FROM direcciones WHERE (idDistrito is null OR idDistrito='0') AND idCiudad is not null AND idCiudad<>'0'";
  
  $direcciones=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
//      array_push($direcciones,$fila);
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
                    $asociados[$fila["idDireccion"]]=$territorio;
                }
  }
  
    foreach($asociados as $id=>$territorio)
    {
          mysqli_query($link,"UPDATE direcciones SET idDistrito='$territorio' WHERE idDireccion='$id'");
    }
  }

  $sql="SELECT * FROM direcciones WHERE (idBarrio is null OR idBarrio='0') AND idCiudad is not null AND idCiudad<>'0'";
  
  $direcciones=array();
  $asociados=array();
    
  $result=mysqli_query($link,$sql);
  while($fila=mysqli_fetch_assoc($result))
  {
//      array_push($direcciones,$fila);
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
                    $asociados[$fila["idDireccion"]]=$territorio;
                }
  }
  
    foreach($asociados as $id=>$territorio)
    {
          mysqli_query($link,"UPDATE direcciones SET idBarrio='$territorio' WHERE idDireccion='$id'");
    }
  }
  echo "HECHO!";
?>