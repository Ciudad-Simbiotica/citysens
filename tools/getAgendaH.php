<?php
//GENERAR EVENTOS
set_time_limit(0);

// Script is unactive unless it is required
// For use  select id an direcotories targeted.
//exit();
$url="http://agendadelhenares.org/widget-json?selectRecentChanges=86400&orderByLastBigChanges=true";
//$url="eventos.json";
$raw_data=file_get_contents($url);

$data=json_decode($raw_data,true);


$i=0;
foreach($data["events"] as $id=>$event)
{  
   echo $id."<BR>";
/*
*/
    $datos["idAgenda"]=$event["id"];
    $datos["clase"]="eventos";
    $grupo=date("Y-m-d",$event["start_time"]);//QUERY porquea variable grupo?
    //$grupo=date("Y-m-d",$event["start_time"]);
    $datos["titulo"]=$event["title"];
    $datos["texto"]=$event["body"];//$lorem; //está en todos vacío
    $datos["hora"]=date("H:i",$event["start_time"]);
    $datos["lugar"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["name"];
    $datos["idTearritorioAgenda"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["id"];
    //$datos["temperatura"]=rand(1,5);  //No aplica
  
    // TEMÁTICAS
    $datos["lugarEntidad"]=$event["render"]["topics"];

    //DIRECCIONES
    $datos["lugarEntidad"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["address"];
    // 4 es Alcalá , 6 es Guadalajara, 8 es Torrejón. 76 es alovera, 185 es Marchamalo, torres de la alameda es 180
    $datos["lat"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["latitude"];//if false, ignore;
    $datos["lng"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["longitude"];//if false, ignore;
    $datos["zoom"]=$event["dboUseForeign_place_id"]["zoom"];//if false, ignore; En primcipio no es facilitado wn wsta búsqueda
    $datos["idlugarAgenda"]=$event["dboUseForeign_place_id"]["id"];

}
// hacer comprobación
/*
foreach($datos["idAgenda"??
 * if not exist id Evento
 	$sql="INSERT INTO eventos (idEvento, titulo, texto, fecha, eventoActivo)
 *          VALUES ('$datos[id]','$datos[title]','$datos[body]','$datos[hora]',1)";	
	mysqli_query($link, $sql);
 * $sql="INSERT INTO direcciones (idDireccion, idCiudad, nombre, direccion, lat,lng,zoom,direccionActiva)
 *          VALUES ('$datos[id]','$datos[title]','$datos[lugarEntidad]','$datos[latitude]','$datos[longitude]',$datos[zoom],1)";	
	mysqli_query($link, $sql);
	foreach($ids as $id)
	{
		$sql="UPDATE lugares_shp SET idPadre='$nextID' WHERE id='$id'";
		mysqli_query($link, $sql);
	}
 * 
 * 
 * 
 * 
 * 
 Hacer Query. update or  create
 $sql="UPDATE eventos SET".$datos ='$status' WHERE idEvento='$idEvento'";
print_r($datos["idAgenda"]);  
}
*/


// 
//    if($grupo==date("Y-m-d",strtotime("2014-05-13")))
//        $cabeceraIzq="Hoy, ";
//    else if($grupo==date("Y-m-d",strtotime("2014-05-13")+86400))
//        $cabeceraIzq="Mañana, ";
//    else
//        $cabeceraIzq="";
/*
    if(!is_array($returnData["grupos"][$grupo]["filas"]))
        $returnData["grupos"][$grupo]["filas"]=array();
    array_push($returnData["grupos"][$grupo]["filas"],$datos);

    $i++;

    if($i>=50)
        break;

}
  */
 
// No aplica
//    if(!isset($returnData["grupos"][$grupo]["totalFilas"]))
//    {
//        $returnData["grupos"][$grupo]["totalFilas"]["convocatoria"]=0;
//        $returnData["grupos"][$grupo]["totalFilas"]["recurrente"]=0;
//    }


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>