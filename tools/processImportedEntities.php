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
exit();

$codigoImportacion="CIVICS";
$newEntities=$newPlaces=$updatedPlaces=0;

echo "<pre>";

// Identify idCiudad for the different cities included in import and add it to importacionEntidades table
// WARNING: THIS NEEDS TO BE ADAPTED FOR METROPOLI, like Madrid, considered level 7, where we do not normally have the District name of the address - Coordinates need to be used.

// Adjusted to look for level 7 to include metropolis (Madrid, Barcelona, Valencia...), where the city is, in fact, a "comarca"

$sql="SELECT distinct ie.ciudad, t.id, t.nivel FROM importacionEntidades as ie left join territorios as t on t.nombre=ie.ciudad and (t.nivel=8 or t.nivel=7) where codImport='{$codigoImportacion}'";
$result=mysqli_query($link,$sql);

while($fila=mysqli_fetch_assoc($result)) {
   if ($fila['id']) {
      if($fila['nivel']==8) {
         $sql="UPDATE importacionEntidades set idCiudadCTS={$fila["id"]}, isMetropoli=0 where codImport='$codigoImportacion' and ciudad='{$fila['ciudad']}'";
         mysqli_query($link,$sql);
      } else { // nivel 7
         $sql="UPDATE importacionEntidades set idComarcaCTS={$fila["id"]}, isMetropoli=1 where codImport='$codigoImportacion' and ciudad='{$fila['ciudad']}'";
         mysqli_query($link,$sql);
      }
   } else {
      echo 'ERROR: No city found for entry with id: ',$fila['id']," ", $fila['ciudad'],PHP_EOL;      
   }  
}

// We take all entities with identified idCiudadCTS or idComarcaCTS but still no idEntidadCTS
$sql="SELECT * FROM importacionEntidades where codImport='{$codigoImportacion}' AND (idEntidadCTS IS NULL OR idEntidadCTS='') AND ((idCiudadCTS IS NOT NULL AND idCiudadCTS<>0) OR (idComarcaCTS IS NOT NULL AND idComarcaCTS<>0))";
$result_entidades=mysqli_query($link,$sql);

while($entidadImportada=mysqli_fetch_assoc($result_entidades))
{   
   unset($placeData);
   unset($entityData);
   
   // If there is still no place
   if (!$entidadImportada["idLugarCTS"] || $entidadImportada["idLugarCTS"]=="") {
      
      //WARNING: In case there is no address, the name of the address will be the name of the Entity, and the address Indication/Details will be "sin dirección".
      // In this case, there should not be coordinates. This way: the place will appear at city level, but not at lower.
      if($entidadImportada["isMetropoli"]) {      
         $sql = "SELECT * FROM places where nombre='{$entidadImportada['nombreDireccion']}' AND direccion='{$entidadImportada['direccion']}' AND idComarca='{$entidadImportada['idComarcaCTS']}'";
      } else {
         $sql = "SELECT * FROM places where nombre='{$entidadImportada['nombreDireccion']}' AND direccion='{$entidadImportada['direccion']}' AND idCiudad='{$entidadImportada['idCiudadCTS']}'";
      }
      $result_lugares = mysqli_query($link, $sql);
      $numLugaresEncontrados=mysqli_num_rows($result_lugares);

      if ($numLugaresEncontrados == 0) {
         // The place does not exist in CTS. There is still no assignment to a place in CTS
         // We have to create a new place using a $placeData array
         
         $placeData["idComarca"] = $entidadImportada["idComarcaCTS"];
         $placeData["idCiudad"] = $entidadImportada["idCiudadCTS"];
         $placeData["nombre"] = $entidadImportada["nombreDireccion"];
         $placeData["direccion"] = $entidadImportada["direccion"];
         $placeData["cp"]=$entidadImportada["cp"];
         $placeData["indicacion"] = $entidadImportada["indicacionDireccion"];
         $placeData["placeStatus"] = "1";
         $placeData["idDistrito"] = $placeData["idBarrio"]=0;
         $placeData["lat"] = $placeData["lng"] = '';
         $placeData["zoom"] = 15;
              
         // Get the coordinates, and potentially CP, using GoogleMaps API for new places
         
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
            
            // In case coordinates were found or import provided coordinates
            if ($lat>25 && $lng>-25) { 
               // Google service tends to give something around 20.7 x -103,3 as a result when not finding result
               
               $placeData['lat'] = $lat; // get lat for json
               $placeData['lng'] = $lng; // get lng for json

               if ($entidadImportada['cp']=="" || $entidadImportada['cp']==0) {
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
            }
            
            if ($entidadImportada['lat']<>'' && $entidadImportada['lng']<>'') {
               // Coordinates from import have priority over those from Google
               $placeData['lat'] = $entidadImportada['lat'];
               $placeData['lng'] = $entidadImportada['lng'];
               // TODO: In fact, CP should be calculated using import coordinates in this case, but we have no time to code it.
            }
         }   
         if ($placeData['lat']<>'') {
            // In case we have coordinates, we look for Territories containing it (levels 7-10: comarca-city-district-neighbouhood)
            $punto = geoPHP::load("POINT({$placeData["lng"]} {$placeData["lat"]})", "wkt");

            // First we get idComarca or idCiudad, the one we are missing.
            if ( $entidadImportada["isMetropoli"]) {
               $ciudadesIds = getDescendantsOfLevel($placeData["idComarca"], 8);
               foreach ($ciudadesIds as $ciudadId) {
                  $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/8/$ciudadId.geojson"), 'json');
                  if ($poligono->contains($punto)) {
                     $placeData["idCiudad"] = $entidadImportada["idCiudadCTS"] = $ciudadId;
                     break;
                  }
               }
            } else {
               $placeDate["idComarca"] = $entidadImportada["idComarcaCTS"] = getParentID($placeDate["idCiudad"]);
            }

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
                  
         $entidadImportada["idLugarCTS"] = createPlace($placeData);
         $newPlaces++;
      } else {
         // A place already exists. We just update the idPlace, cp and idCiudadCTS or idComarcaCTS
         $lugar=mysqli_fetch_assoc($result_lugares);
         $entidadImportada["idLugarCTS"] = $lugar['idPlace'];
         if ($entidadImportada['cp']=="" || $entidadImportada['cp']=='0') {
            $entidadImportada['cp'] = $lugar['cp'];
         }
      }                                 
   }
   
   
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
   $entityData["idsComarcas"] = $entidadImportada["idComarcaCTS"];
   $entityData["telefono"] = $entidadImportada["telefono"];
   $entityData["email"] = $entidadImportada["email"];
   $entityData["points"] = 0;
   $entityData["url"] = $entidadImportada["url"];
   $entityData["twitter"] =  $entidadImportada["twitter"];
   $entityData["facebook"] =  $entidadImportada["facebook"];
   $entityData["etiquetas"] = '';
   $entityData["descBreve"] =  $entidadImportada["descBreve"];
   $entityData["texto"] = $entidadImportada["texto"];
   $entityData["fechaConstitucion"] =  $entidadImportada["fechaConstitucion"];
   
   // ASSIGN CTS TEMATICS and TAGS based on clasificacion and tipo fields of import
//'1', 'Administraciones', '0'
//'2', 'Cuidados y Atención social', '0'
//'3', 'Ciudad y Barrios', '12'
//'4', 'Comunicación', '0'
//'5', 'Conocimiento', '0'
//'6', 'Consumo', '0'
//'7', 'Cooperación', '0'
//'8', 'Cultura', '0'
//'9', 'Democracia', '0'
//'10', 'Deporte', '0'
//'11', 'Derechos e Igualdad', '0'
//'12', 'Diversidad funcional', '0'
//'13', 'Economía', '13'
//'14', 'Educación', '6'
//'15', 'Emergencias', '0'
//'16', 'Infancia', '0'
//'17', 'Interculturalidad y Convivencia', '0'
//'18', 'Innovación', '0'
//'19', 'Mayores', '0'
//'20', 'Medio Ambiente', '0'
//'21', 'Género', '2'
//'22', 'Ocio', '7'
//'23', 'Salud', '5'
//'24', 'Servicios Públicos', '0'
//'25', 'Seguridad', '0'
//'26', 'Tecnología', '0'
//'27', 'Movilidad', '0'
//'28', 'Transporte', '0'
//'29', 'Urbanismo', '8'
//'30', 'Vivienda', '0'
//'31', 'Empleo', '0'
//'32', 'Artes', '0'
//'33', 'Religión', '0'
//'34', 'Juventud', '0'
//'35', 'Festejos', '0'
//'36', 'Política', '4'
//'37', 'Social', '11'
//'38', 'Otros', '3'
   
// // For CIVICS import
// They have the following classifications: 
// TIPO ESPACIO: 
// - Centro social, Derivas y Rutas Urbanas, Despensas Solidarias y Bancos de Intercambio, 
//   Digital, Encuentro Diálogo y Corresponsabilidad, Escuela Popular, Infraestructuras y/o 
//   Intervenciones Urbanas, Medios de Comunicación Vecinales, Mercado Social, Otros,
//   Solares y Espacios Recuperados, Trabajo Colaborativo
// 
// TIPO TOPIC
// - Apoyo Mutuo y Cuidados, Arte Urbano, Cultura Libre, Deporte, Derechos e Igualdad, 
//   Ecología Urbana y Consumo, Economía Colaborativa, Educación Expandida, Mediación y 
//   Facilitación, Movilidad Sostenible, Otra, Política y Gobernanza, Urbanismo y Patrimonio
// 
// TIPO AGENT
// - Administración Pública, Asambleas Populares, Mareas, Plataformas y Grupos de Trabajo,
//   Conquistas Ciudadanas del Pasado, Empresa Social, Empresa Social/Startup, Iniciativa Ciudadana,
//   Organizaciones y Asociaciones de Vecinos
// 
// We will do some adjustments to improve data quality and divide some of them.
// 
// TIPO
//   Apoyo Mutuo *
//   Arte *
//   Asamblea *
//   Asociación Cutural *
//   Asociaciones de Vecinos *
//   Centro Social *
//   Consumo *
//   Cultura *
//   Deporte *
//   Diversidad Funcional *
//   Ecología *
//   Empresa Social *
//   Escuela *
//   Espacio Vecinal *
//   Federación *
//   Fundación *
//   Mayores *
//   Medios de Comunicación *
//   Movilidad *
//   Urbanismo *
//   
// CLASIFICACION
//   Arte *
//   Banco de Intercambio *
//   Centro Social *
//   Consumo *
//   Cuidados *
//   Cultura *
//   Deporte *
//   Derechos Humanos *
//   Ecología *
//   Educación *
//   Federación *
//   Infancia *
//   Mayores, diversidad funcional *
//   Patrimonio *
//   Política *
//   Social *
//   Urbanismo *
 
     $entityData["tematicas"]=array(); // TODO: Verify if recreated empty
     $etiquetas=""; 

     switch($entidadImportada["tipo"]) {
        case "Asamblea":
            $entityData["tematicas"][]=9; // Democracia            
            if ($etiquetas==""){
                $etiquetas="asamblea";
            }
            else{
                $etiquetas=$etiquetas.",asamblea";   
            }
            break;
        case "Escuela":
            $entityData["tematicas"][]=14; // Educación            
            if ($etiquetas==""){
                $etiquetas="escuela";
            }
            else{
                $etiquetas=$etiquetas.",escuela";   
            }
            break;
        case "Medios de Comunicación":
            $entityData["tematicas"][]=4; // Comunicación           
            if ($etiquetas==""){
                $etiquetas="comunicación vecinal";
            }
            else{
                $etiquetas=$etiquetas.",comunicación vecinal";   
            }
            break;
        case "Asociaciones de Vecinos":
           $entityData["tematicas"][]=3; // Ciudad y Barrios
           $entityData["tematicas"][]=29; // Urbanismo
            if ($etiquetas==""){
                $etiquetas="asociación de vecinos";
            }
            else{
                $etiquetas=$etiquetas.",asociación de vecinos";   
            }
            break;            
        case "Federación":
            if ($etiquetas==""){
                $etiquetas="federación";
            }
            else{
                $etiquetas=$etiquetas.",federación";   
            }
            break;
        case "Fundación":
            if ($etiquetas==""){
                $etiquetas="fundación";
            }
            else{
                $etiquetas=$etiquetas.",fundación";   
            }
            break;
        case "Cultura":
        case "Asociación Cultural":
           $entityData["tematicas"][]=8;
            break;
        case "Deporte":
           $entityData["tematicas"][]=10;
           break;            
        case "Consumo":
           $entityData["tematicas"][]=6;
           break;
        case "Arte":
           $entityData["tematicas"][]=8;  // Cultura
           $entityData["tematicas"][]=32; // Artes
           break;            
        case "Apoyo Mutuo":
           $entityData["tematicas"][]=37; // Social
           $entityData["tematicas"][]=11; // Derechos e Igualdad
           if ($etiquetas==""){
                $etiquetas="apoyo mutuo";
           }
           else{
                $etiquetas=$etiquetas.",apoyo mutuo";   
           }
           break;
        case "Centro Social":
           $entityData["tematicas"][]=37; // Social
           $entityData["tematicas"][]=11; // Derechos e Igualdad
           $entityData["tematicas"][]=34; // Juventud
           $entityData["tematicas"][]=22; // Ocio
           if ($etiquetas==""){
                $etiquetas="centro social";
           }
           else{
                $etiquetas=$etiquetas.",centro social";   
           }
           break;      
        case "Diversidad Funcional":
           $entityData["tematicas"][]=12;
           break;           
        case "Ecología":
           $entityData["tematicas"][]=20;  // Medio Ambiente
           break;  
        case "Empresa Social":
           $entityData["tematicas"][]=13;  // Economía
           $entityData["tematicas"][]=37;  // Social
           break;  
        case "Espacio Vecinal":
           $entityData["tematicas"][]=37; // Social
           $entityData["tematicas"][]=3;  // Ciudad y Barrios
           $entityData["tematicas"][]=29; // Urbanismo
           if ($etiquetas==""){
                $etiquetas="espacio vecinal";
           }
           else{
                $etiquetas=$etiquetas.",espacio vecinal";   
           }
           break;
        case "Mayores":
           $entityData["tematicas"][]=19;
           break; 
        case "Movilidad":
           $entityData["tematicas"][]=27;
           break;
        case "Urbanismo":
           $entityData["tematicas"][]=29;
           break;        
        default:
            break;  
    }

// Process the clasificacion field    
      switch($entidadImportada['clasificacion']) {
        case "Arte":
           $entityData["tematicas"][]=8;  // Cultura
           $entityData["tematicas"][]=32; // Artes
           break;  
        case "Centro Social":
           $entityData["tematicas"][]=37; // Social
           $entityData["tematicas"][]=11; // Derechos e Igualdad
           break; 
        case "Consumo":
           $entityData["tematicas"][]=6;
           break;
        case "Cuidados":
           $entityData["tematicas"][]=2; // Cuidados y Atención Social
           break;
        case "Cultura":
        case "Culturales":
           $entityData["tematicas"][]=8;
           break;
        case "Deporte":
        case "Deportivas":
           $entityData["tematicas"][]=10;
           break;            
        case "Derechos Humanos":
           $entityData["tematicas"][]=11; // Derechos e Igualdad
           break; 
        case "Medio Ambiente":
        case "Ecología":
           $entityData["tematicas"][]=20;  // Medio Ambiente
           break;
        case "EDUCATIVAS":
        case "Educación":
            $entityData["tematicas"][]=14;  // Educación
            break;
        case "Federación":
            if ($etiquetas==""){
                $etiquetas="federación";
            }
            else{
                $etiquetas=$etiquetas.",federación";   
            }
            break;
        case "Federación":
            if ($etiquetas==""){
                $etiquetas="banco de intercambio";
            }
            else{
                $etiquetas=$etiquetas.",banco de intercambio";   
            }
            break;
        case "Padres":
        case "Infancia":
            $entityData["tematicas"][]=16; // Infancia
            break;
        case "Mayores":
            $entityData["tematicas"][]=19; // Mayores
            break;
         case "Mayores, diversidad funcional":
           $entityData["tematicas"][]=19; // Mayores
           $entityData["tematicas"][]=12; // Diversidad Funcional
           break;
        case "Patrimonio":
           $entityData["tematicas"][]=3; // Ciudad y Barrios
           $entityData["tematicas"][]=8; // Cultura
            if ($etiquetas==""){
                $etiquetas="patrimonio";
            }
            else{
                $etiquetas=$etiquetas.",patrimonio";   
            }
           break;
        case "Urbanismo":
           $entityData["tematicas"][]=29;
           break; 
        case "Social":
           $entityData["tematicas"][]=2;  // Atención Social
           $entityData["tematicas"][]=37; // Social        
           break;        
        case "CASAS REGIONALES":         
            $entityData["tematicas"][]=8; // Cultura
            $entityData["tematicas"][]=35;  // Festejos
            break;
        case "INTERCULTURAL Y ONG":
            $entityData["tematicas"][]=7;  // Cooperación
            break;
        case "Juveniles":
            $entityData["tematicas"][]=34; // Juventud
            break;
        case "Mujeres":
            $entityData["tematicas"][]=21; // Género
            break;
        case "Mujeres Culturales":
            $entityData["tematicas"][]=8;  // Cultura
            $entityData["tematicas"][]=21; // Género
            break;
        case "PEÑAS":
            $entityData["tematicas"][]=35; // Festejos
            break;
        case "POLITICAS":
        case "Política":
            $entityData["tematicas"][]=9;  // Democracia
            break;
        case "Religiosas":
            $entityData["tematicas"][]=33; // Religión
            break;
        case "Vecinos":
            $entityData["tematicas"][]=3; // Ciudad y Barrios
            break;
        case "Trabajo":
            $entityData["tematicas"][]=13; // Economía
            $entityData["tematicas"][]=31; // Empleo
            break;
        case "Sanitarias":
            $entityData["tematicas"][]=23; //Salud
            break;
        default:
            $entityData["tematicas"][]=38;  // Otros
            break;
    }
    
// For GUADA import, not done.
// 
// For CAM import: we used the following:
//  
//      
//     switch($entidadImportada["tipo"]) {
//        case "AMPAS CC":
//        case "AMPAS CP":
//        case "AMPAS EI":
//        case "AMPAS IES":
//        case "AMPAS varias":
//            $entityData["tematicas"][]=14; // Educación
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
    
   $entidadImportada["idEntidadCTS"]=createEntity($entityData);
   
   $newEntities++;
   $entidadImportada["tematicasCTS"]=implode(",",$entityData["tematicas"]);
   
   // Update the info at the entidadesImportadas table
   $sql = "UPDATE importacionEntidades SET cp={$entidadImportada["cp"]}, 
                  idLugarCTS={$entidadImportada["idLugarCTS"]}, 
                  idEntidadCTS={$entidadImportada["idEntidadCTS"]}, 
                  tematicasCTS='{$entidadImportada["tematicasCTS"]}'
            WHERE id={$entidadImportada["id"]}";
            
   mysqli_query($link, $sql);
   
}

echo "IMPORTADOS:".PHP_EOL."New Entities: ".$newEntities.PHP_EOL."New Places: ".$newPlaces.PHP_EOL;
?>