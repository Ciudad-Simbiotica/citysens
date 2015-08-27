<?php

set_time_limit(0);
error_reporting(E_ERROR);
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');

ini_set('default_charset', 'utf-8');
$link = connect();
mysqli_query($link, 'SET CHARACTER SET utf8');

// This script processes the data from entities imported into the table importacionEntidades: inserts data into the corresponding 
// tables (entidades, tematicas_entidades, places), looking for geo-location and CP when needed, etc.

// Script is unactive unless it is required
//exit();

$codigoImportacion="GUADA";
$newEntities=$newPlaces=$updatedPlaces=0;

echo "<pre>";

// Identify idCiudad for the different cities included in import and add it to imp∫ortacionEntidades table
$sql="SELECT distinct ie.ciudad, t.id FROM importacionEntidades as ie left join territorios as t on t.nombre=ie.ciudad and t.nivel=8 where codImport='{$codigoImportacion}'";
$result=mysqli_query($link,$sql);

while($fila=mysqli_fetch_assoc($result)) {
   if ($fila['id']) {
      $sql="UPDATE importacionEntidades set idCiudadCTS={$fila["id"]} where codImport='$codigoImportacion' and ciudad='{$fila['ciudad']}'";
      mysqli_query($link,$sql);
   } else {
      echo 'ERROR: No city found for entry with id: ',$fila['id']," ", $fila['ciudad'],PHP_EOL;      
   }  
}

// We take all entities with identified idCiudadCTS but still no idEntidadCTS
$sql="SELECT * FROM importacionEntidades where codImport='{$codigoImportacion}' AND (idEntidadCTS IS NULL OR idEntidadCTS='') AND idCiudadCTS IS NOT NULL AND idCiudadCTS<>0";
$result_entidades=mysqli_query($link,$sql);

while($entidadImportada=mysqli_fetch_assoc($result_entidades))
{   
   unset($placeData);
   unset($entityData);
   
   // If there is still no place
   if ((!$entidadImportada["idLugarCTS"] || $entidadImportada["idLugarCTS"]=="") && $entidadImportada["idCiudadCTS"] && $entidadImportada["idCiudadCTS"]!=0) {
            
      $sql = "SELECT * FROM places where nombre='{$entidadImportada['nombreDireccion']}' AND direccion='{$entidadImportada['direccion']}' AND idCiudad='{$entidadImportada['idCiudadCTS']}'";
      $result_lugares = mysqli_query($link, $sql);
      $numLugaresEncontrados=mysqli_num_rows($result_lugares);

      if ($numLugaresEncontrados == 0) {
         // The place does not exist in CTS. There is still no assignment to a place in CTS
         // We have to create a new place using a $placeData array
         
         $placeData["idCiudad"] = $entidadImportada["idCiudadCTS"];
         $placeData["nombre"] = $entidadImportada["nombreDireccion"];
         $placeData["direccion"] = $entidadImportada["direccion"];
         $placeData["cp"]=$entidadImportada["cp"];
         $placeData["indicacion"] = "";
         $placeData["placeStatus"] = "1";
         $placeData["idDistrito"] = $placeData["idBarrio"] = $placeData["lat"] = $placeData["lng"] = 0;
         $placeData["zoom"] = 15;
              
         // Get the coordinates, and potentially CP, using GoogleMaps API for new and potentially updated locations
         
         if ($entidadImportada['direccionBreve']=="") {
            $strDireccion = $entidadImportada['direccion'].". ".$entidadImportada["ciudad"];
         } else {
            $strDireccion = $entidadImportada['direccionBreve'].". ".$entidadImportada["ciudad"]; // This is an address shortened (removing details after house number) to get better results with Google API
         }
         $strDireccionClean = str_replace (" ", "+", $strDireccion);
         usleep(1500000); // Google gives 2.500 free searchs/day with speed 5 pers sec.
         
         $respuesta=json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$strDireccionClean."&sensor=false"),true);

         if ($respuesta['status']=='OK' && isset($respuesta['results'])) {
            
            $lat = $respuesta['results'][0]['geometry']['location']['lat']; // get lat for json
            $lng = $respuesta['results'][0]['geometry']['location']['lng']; // get lng for json
            if (lat>25 && lng>-25) { // Google service tends to give something around 20.7 x -103,3 as a result when not finding result

               $placeData['lat'] = $respuesta['results'][0]['geometry']['location']['lat']; // get lat for json
               $placeData['lng'] = $respuesta['results'][0]['geometry']['location']['lng']; // get lng for json

               if ($entidadImportada['cp']=="") {
                  foreach    ($respuesta['results'] as $result) {
                    foreach ($result['address_components'] as $address_component) {
                      $types = $address_component['types'];
                      if (in_array('postal_code', $types) && sizeof($types) == 1) {
                         $entidadImportada['cp']= $placeData["cp"] = $address_component['short_name'];
                         break 2; 
                      }
                    }
                  }
               }
               
               $punto = geoPHP::load("POINT({$placeData["lng"]} {$placeData["lat"]})", "wkt");

               $distritosIds = getDescendantsOfLevel($placeData["idCiudad"], 9);
               foreach ($distritosIds as $distritoId) {
                  $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/9/$distritoId.geojson"), 'json');
                  if ($poligono->contains($punto)) {
                     $placeData["idDistrito"] = $distritoId;
                     break;
                  }
               }
               $barriosIds = getDescendantsOfLevel($placeData["idCiudad"], 10);
               foreach ($barriosIds as $barrioId) {
                  $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/10/$barrioId.geojson"), 'json');
                  if ($poligono->contains($punto)) {
                     $placeData["idBarrio"] = $barrioId;
                     break;
                  }
               }   
            } else {
               echo 'ERROR: No coordinates found for address: ',$strDireccion, " from entry with id: ", $entidadImportada['id'],PHP_EOL;      
            }         
         }         
         $entidadImportada["idLugarCTS"] = createPlace($placeData);
         $newPlaces++;
      } else {
         // A place already exists. We just update the idPlace and cp
         $lugar=mysqli_fetch_assoc($result_lugares);
         $entidadImportada["idLugarCTS"] = $lugar['idPlace'];
         if ($entidadImportada['cp']=="") {
            $entidadImportada["cp"] = $lugar['cp'];
         }
      }                                 
   }
         


// TAGS could be assigned based on the values clasificacion, tipo, subtipo of imported entities.
// For GUADA import, not done.

//
//     $etiquetas=""; 
//      
//     switch($entidadImportada["tipo"]) {
//        case "AMPAS CC":
//        case "AMPAS CP":
//        case "AMPAS EI":
//        case "AMPAS IES":
//        case "AMPAS varias":
//            $tematicas[]=6;
//            
//            if ($etiquetas==""){
//                $etiquetas="ampa";
//            }
//            else{
//                $etiquetas=$etiquetas.",ampa";   
//            }
//            break;
//        case "escuela de teatro":     
//             if ($etiquetas==""){
//                $etiquetas="teatro,escuela";
//            }
//            else{
//                $etiquetas=$etiquetas.",teatro,escuela";   
//            }
//            break;
//        case "escuela de animación sociocultural":
//             if ($etiquetas==""){
//                $etiquetas="animación sociocultural,escuela";
//            }
//            else{
//                $etiquetas=$etiquetas.",animación sociocultural,escuela";   
//            }
//            break;
//        case "escuela de música":
//            if ($etiquetas==""){
//                $etiquetas="música,escuela";
//            }
//            else{
//                $etiquetas=$etiquetas.",música,escuela";   
//            }
//            break;
//        case "":
//        case "festiva":
//        case "taurina":
//        case "partidos políticos con representación municipal":
//        case "general":
//        case "varios":
//        case "asociaciones de comerciantes":
//        case "asociaciones de empresarios":
//            break;
//        case "federaciones":
//            if ($etiquetas==""){
//                $etiquetas="federación";
//            }
//            else{
//                $etiquetas=$etiquetas.",federación";   
//            }
//            break;
//        case "universitarias":
//            if ($etiquetas==""){
//                $etiquetas="universidad";
//            }
//            else{
//                $etiquetas=$etiquetas.",universidad";   
//            }
//            break;
//
//        case "futbolistica":
//        case "futbol":
//        case "futbolística":
//            if ($etiquetas==""){
//                $etiquetas="fútbol";
//            }
//            else{
//                $etiquetas=$etiquetas.",fútbol";   
//            }
//            break;
//        case "sindicatos":       
//             if ($etiquetas==""){
//                $etiquetas="sindicato";
//            }
//            else{
//                $etiquetas=$etiquetas.",sindicato";   
//            }
//            break;    
//        default:
//            if ($etiquetas==""){
//                $etiquetas=$entidadImportada["tipo"];
//            }
//            else{
//                $etiquetas=$etiquetas.",".$entidadImportada["tipo"];   
//            }
//            break;  
//    }
//
//}
   
   
   // ENTITY
   // Place and City have been identified. Entity can be created.
   $entityData["entidad"] = $entidadImportada["nombre"];
   $entityData["nombreCorto"] = $entidadImportada["nombreCorto"];
   if ($entidadImportada["tipoCTS"]!="")
      $entityData["tipo"] = $entidadImportada["tipoCTS"];
   else
      $entityData["tipo"]="organizacion";
   $entityData["idPlace"] = $entidadImportada["idLugarCTS"];
   $entityData["idsCiudades"] = $entidadImportada["idCiudadCTS"];
   $entityData["telefono"] = $entidadImportada["telefono"];
   $entityData["email"] = $entidadImportada["email"];
   $entityData["points"] = 0;
   $entityData["url"] = $entidadImportada["url"];
   $entityData["twitter"] =  $entidadImportada["twitter"];
   $entityData["facebook"] =  $entidadImportada["facebook"];
   $entityData["etiquetas"] = $etiquetas;
   $entityData["descBreve"] =  $entidadImportada["descBreve"];
   $entityData["texto"] = "";
   $entityData["fechaConstitucion"] =  $entidadImportada["fechaConstitucion"];
   
   // ASSIGN TEMATICS
   $entityData["tematicas"]=array(); // Verify if recreated empty
   
   switch($entidadImportada['clasificacion']) {
        case "CASAS REGIONALES":         
            $entityData["tematicas"][]=8;
            $entityData["tematicas"][]=35;
            break;
        case "Culturales":
            $entityData["tematicas"][]=8;
            break;
        case "Deportivas":
            $entityData["tematicas"][]=10;
            break;
        case "EDUCATIVAS":
            $entityData["tematicas"][]=6;
            break;
        case "Social":
            $entityData["tematicas"][]=2;
            break;
        case "INTERCULTURAL Y ONG":
            $entityData["tematicas"][]=7;
            break;
        case "Juveniles":
            $entityData["tematicas"][]=16;
            break;
        case "Padres":
        case "Infancia":
            $entityData["tematicas"][]=34;
            break;
        case "Medio Ambiente":
            $entityData["tematicas"][]=20;
            break;
        case "Mujeres":
            $entityData["tematicas"][]=21;
            break;
        case "Mujeres Culturales":
            $entityData["tematicas"][]=8;
            $entityData["tematicas"][]=21;
            break;
        case "PEÑAS":
            $entityData["tematicas"][]=35;
            break;
        case "POLITICAS":
            $entityData["tematicas"][]=9;
            break;
        case "Religiosas":
            $entityData["tematicas"][]=33;
            break;
        case "Mayores":
            $entityData["tematicas"][]=19;
            break;
        case "Vecinos":
            $entityData["tematicas"][]=3;
            break;
        case "Trabajo":
            $entityData["tematicas"][]=13;
            break;
        case "Sanitarias":
            $entityData["tematicas"][]=23;
            break;
        default:
            $entityData["tematicas"][]=38;
            break;
    }

   
   // $entityData["idPlace"];
   $entidadImportada["idEntidadCTS"]=createEntity($entityData);
   
   $newEntities++;
   $entidadImportada["tematicasCTS"]=implode(",",$entityData["tematicas"]);
   
   // Update the info at the entidadesImportadas table
   $sql = "UPDATE importacionEntidades SET cp={$entidadImportada["cp"]}, 
                  idLugarCTS={$entidadImportada["idLugarCTS"]}, 
                  idEntidadCTS={$entidadImportada["idEntidadCTS"]}, 
                  tematicasCTS={$entidadImportada["tematicasCTS"]}
            WHERE id={$entidadImportada["id"]}";
            
   mysqli_query($link, $sql);
   
}

echo "IMPORTADOS:".PHP_EOL."New Entities: ".$newEntities.PHP_EOL."New Places: ".$newPlaces.PHP_EOL;
?>