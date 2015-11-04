<?php

include "../db.php";
error_reporting(E_ERROR);
set_time_limit(0);
ini_set('default_charset', 'utf-8');
mysql_query("SET NAMES 'utf8'");
// Disabled unless needed
exit();

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

    echo "HECHO!";
?>