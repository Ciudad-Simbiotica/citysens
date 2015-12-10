<?php

include "../db.php";
error_reporting(E_ERROR);
set_time_limit(0);
ini_set('default_charset', 'utf-8');
mysql_query("SET NAMES 'utf8'");

// Disabled unless needed
//exit();

$link = connect();
echo "<pre>";

// This file is a repository of different operations to change data on the database. Normaly they involve a query and an update operation with the result
// Uncomment the one you want to use

/* UPDATE THE POINTS OF ENTITIES, CONSIDERING IF THEY ARE convocatorias/recurrentes AND older/younger THAN A YEAR.

   $today = "2015-09-09";
   $yearAgo = "2014-09-09"
   $sql = "select puntos.idEntidad, puntos.entidad, floor(sum(puntos.points)) as totalPuntos from 
            (
              (select en.idEntidad, en.entidad, count(ev.idEvento)/4 as points from entidades as en, eventos as ev where en.idEntidad=ev.idEntidad and ev.tipo='recurrente' and ev.fecha>'$yearAgo' group by en.idEntidad) 
                UNION
              (select en.idEntidad, en.entidad, count(ev.idEvento)/8 as points from entidades as en, eventos as ev where en.idEntidad=ev.idEntidad and ev.tipo='recurrente' and ev.fecha<'$yearAgo' group by en.idEntidad) 
              UNION
              (select en.idEntidad, en.entidad, count(ev.idEvento) as points from entidades as en, eventos as ev where en.idEntidad=ev.idEntidad and ev.tipo='convocatoria' and ev.fecha>'$yearAgo' group by en.idEntidad)
               UNION
              (select en.idEntidad, en.entidad, count(ev.idEvento)/2 as points from entidades as en, eventos as ev where en.idEntidad=ev.idEntidad and ev.tipo='convocatoria' and ev.fecha<'$yearAgo' group by en.idEntidad)
            ) as puntos
            group by idEntidad";
   $result = mysqli_query($link, $sql);
   while ($fila = mysqli_fetch_assoc($result)) {   
      $sql="update entidades SET points={$fila["totalPuntos"]} where idEntidad='{$fila["idEntidad"]}'";
      echo $sql,PHP_EOL;
      mysqli_query($link, $sql);
   }

*/

 /* ASSIGN THEMATIC "Otros" TO ENTITIES THAT HAVE NO THEMATIC ASSIGNED
   $sql= "select entidades.* from entidades
            where not exists (select * 
                                from entidades_tematicas 
                               where entidades.idEntidad=entidades_tematicas.idEntidad)";
   $result = mysqli_query($link, $sql);

   $firstValue=true;
   $sql="INSERT INTO entidades_tematicas (idEntidad,idTematica) VALUES ";
   while ($fila = mysqli_fetch_assoc($result)) {   
      if ($firstValue) {
         $firstValue=false;
         $sql.="({$fila["idEntidad"]},38)";
      } else {
         $sql.=",({$fila["idEntidad"]},38)";
      }
   }
   if (!$firstValue) {
      mysqli_query($link,$sql);
      echo $sql;
   }

 */


 /* Remove address assignment for events to addresses with no coordinates. 
  * Change for a direct idCiudad or idComarca assignment and delete the wrong address.


   $idsPlaceToDelete=array();
   
   // First, events from municipalities
   $sql= "select e.idEvento, p.idCiudad, p.direccion, p.idPlace
            from eventos e, places p
            where e.idPlace=p.idPlace
             and p.lng=0 and p.lat=0
             and p.idCiudad <>0";
   echo $sql,PHP_EOL;
   $result = mysqli_query($link, $sql);
   while ($fila = mysqli_fetch_assoc($result)) {   
      $sql="update eventos 
            SET idPlace=0, idCiudad={$fila["idCiudad"]}, detalleDireccion='{$fila["direccion"]}'
            where idEvento={$fila["idEvento"]}";
      
      echo $sql,PHP_EOL;
      mysqli_query($link, $sql);
      
      $idsPlaceToDelete[]=$fila["idPlace"];
   }
   
   // Second, events from Metropolis
   $sql= "select e.idEvento, p.idComarca, p.direccion, p.idPlace
            from eventos e, places p
            where e.idPlace=p.idPlace
             and p.lng=0 and p.lat=0
             and p.idCiudad=0";
   echo $sql,PHP_EOL;
   $result = mysqli_query($link, $sql);
   while ($fila = mysqli_fetch_assoc($result)) {   
      $sql="update eventos
             SET idPlace=0, idComarca={$fila["idComarca"]}, detalleDireccion='{$fila["direccion"]}'
             where idEvento={$fila["idEvento"]}";
      echo $sql,PHP_EOL;
      mysqli_query($link, $sql);
      
      $idsPlaceToDelete[]=$fila["idPlace"]; 
   }
   
      // First, entities from municipalities
   $sql= "select e.idEntidad, p.idCiudad, p.direccion, p.idPlace
            from entidades e, places p
            where e.idPlace=p.idPlace
             and p.lng=0 and p.lat=0
             and p.idCiudad <>0";
   echo $sql,PHP_EOL;
   $result = mysqli_query($link, $sql);
   while ($fila = mysqli_fetch_assoc($result)) {   
      $sql="update entidades
            SET idPlace=0, idCiudad={$fila["idCiudad"]}, detalleDireccion='{$fila["direccion"]}'
            where idEntidad={$fila["idEntidad"]}";
      echo $sql,PHP_EOL;
      mysqli_query($link, $sql);
      
      $idsPlaceToDelete[]=$fila["idPlace"];      
   }
   
   foreach($idsPlaceToDelete as $idPlace) {
      $sql="DELETE FROM places WHERE idPlace='$idPlace'";
      echo $sql,PHP_EOL;
      mysqli_query($link, $sql);
   }

   */

    echo "HECHO!";
?>