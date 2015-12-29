<?php

set_time_limit(0);
error_reporting(E_ERROR);
include "../db.php";
include_once '../vendor/simplehtmldom/simple_html_dom.php';
include_once('../vendor/phayes/geophp/geoPHP.inc');

ini_set('default_charset', 'utf-8');
$link = connect();
mysqli_query($link, 'SET CHARACTER SET utf8');


function getCP($lat, $lng) {
  $returnValue = NULL;
  $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&sensor=false";
  
  $respuesta=json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&sensor=false"),true);
  
  if ($respuesta['status']=='OK' && isset($respuesta['results'])) {
     foreach    ($respuesta['results'] as $result) {
        foreach ($result['address_components'] as $address_component) {
          $types = $address_component['types'];
          if (in_array('postal_code', $types) && sizeof($types) == 1) {
             $returnValue = $address_component['short_name'];
             break 2; 
          }
        }
     }
  }
  return $returnValue;
}

function clean_all(&$items,$leave = ''){
    foreach($items as $id => $item){
        if($leave && ((!is_array($leave) && $id == $leave) || (is_array($leave) && in_array($id,$leave)))) continue;
        if($id != 'GLOBALS'){
            if(is_object($item) && ((get_class($item) == 'simple_html_dom') || (get_class($item) == 'simple_html_dom_node'))){
                $items[$id]->clear();
                unset($items[$id]);
            }else if(is_array($item)){
                $first = array_shift($item);
                if(is_object($first) && ((get_class($first) == 'simple_html_dom') || (get_class($first) == 'simple_html_dom_node'))){
                    unset($items[$id]);
                }
                unset($first);
            }
        }
    }
}

// Script is unactive unless it is required
//exit();

$madridAdhId = 701280008;  // It is inserted directly for events from Madrid with no coords. If we change IDs format, this line should be updated.

// Standard use of the script, to update with changes from the last day
$lastChangedLimit = time()- (1 * 86400); //To be compared with the event's "changed" field to see if it got updated since the last time we checked. 
//$url = "http://agendadelhenares.org/event-list-json?changed&place__address&place__latitude&place__longitude&place__zoom&body";

// This alternative can be used to obtain past events, for example, to extract events month by month
//$timeFrame="startTime=1438380001&endTime=1441058401"; // Agosto 2015
//$timeFrame="startTime=1441058401&endTime=1443650401"; // Septiembre 2015
//$timeFrame="startTime=1443650401&endTime=1446332401"; // Octubre 2015
//$timeFrame="startTime=1446332401&endTime=1448924401"; // Noviembre 2015
$timeFrame="startTime=1448924401&endTime=1451602801"; // Diciembre 2015
$url = "http://agendadelhenares.org/event-list-json?{$timeFrame}&changed&place__address&place__latitude&place__longitude&place__zoom&body";
//http://agendadelhenares.org/event-list-json?startTime=1370037601&endTime=1372629601&limit=150&changed&place__address&place__latitude&place__longitude&place__zoom&body

$raw_data = file_get_contents($url);
$data = json_decode($raw_data, true);

$newCities=$newPlaces=$newEvents=$updatedEvents=$updatedPlaces=$totalEvents=0;
$newCityNames=array();

foreach ($data["events"] as $event) {
   $totalEvents++;
   /* This is the contents of the JSON feed:
     Marked the ones we can use:
    *      id: 3949,
    *      title: "Asamblea de la PAH Corredor del henares",
     html_title: "Asamblea de la <strong>PAH Corredor del Henares</strong>",
    *      start_time: 1434474000,
     dayrank: 1,
    *      visits: 22, //We will use it to calculate temperature: 10 visits means 1 more degree hotness
    *      place__id: 805,
    *      place__city__id: 4,
    *      place__city__name: "Alcalá de Henares",
     place__city__shortName: "Alcalá",
    *      topics: "11,13"
     time: "19:00"
     place__city__getShort: "Alcalá"
    *      body: "<h3> Taller social autogestionado desde primavera del 2010. Nuestro objetivo es que el uso de la bici sea el mayor posible en Alcalá de Henares. Nos encontramos en el <a class="" href="http://cafecontinental.es/">Café Continental</a>, calle Empecinado nº23 (acceso por el Callejón del Vicario). </h3> <h3> Los días que ejercemos actividad de taller actualmente son los Domingos y Lunes desde las 18 hasta las 21h (aprox). </h3> <h3> Anímate a rescatar esa bici que tienes guardada en el trastero cogiendo polvo, tan solo necesitamos de tu predisposición a contribuir activamente en el proceso de reparación. </h3> <p><img class="floatRight" src="/files/import-images/326c3d1969942aae8d043c23e69f99de.png" alt="" width="240" height="239"/></p> <p class=" demosphere-sources"> Origen : <a class="" href="http://tallersocialdealcala.blogspot.com.es/">http://tallersocialdealcala.blogspot.com.es/</a> </p>",
    *      changed: 1435578517
    *      place__address: "Callejón del Vicario Alcalá de Henares",
    *      place__latitude: 40.479449965183,
    *      place__longitude: -3.367143009787,
    *      place__zoom: 16
    */

//   if ($event["changed"]>=$lastChangedLimit) {
   if (true) {   
      //PLACE
      // Initialice
      
      $placeData["idCiudad"] = $placeData["nombre"] = $placeData["direccion"] = $placeData["indicacion"] = "";
      $placeData["idDistrito"] = $placeData["idBarrio"] = 0;
      $placeData["placeStatus"] = "1";
      $placeData["lat"] = ($event["place__latitude"]!=0) ? (string)$event["place__latitude"]: "0";
      $placeData["lng"] = ($event["place__longitude"]!=0) ? (string)$event["place__longitude"]: "0";
      //These can be empty in case place is not geo-located.
      $placeData["zoom"] = ($event["place__zoom"]!=0) ? $event["place__zoom"]: "0";
      $eventData["idPlace"] = $eventData["idCiudad"] = $eventData["idComarca"] = 0;
      $eventData["detalleDireccion"]='';

      $withCoords =  ($placeData["lat"]!=0)? true: false; // For places with no geolocation no CTS place is created. Just a link to the city in the event.
      
      // We look for the place assignment
      $newPlace = false;
      $sql = "SELECT * FROM adhLugares_ctsDirecciones where adhIdLugar={$event["place__id"]}";
      $result_lugares = mysqli_query($link, $sql);
      $numLugaresEncontrados=mysqli_num_rows($result_lugares);
      
      if ($numLugaresEncontrados == 0) {
         // The place does not exist in CTS. There is still no assignment to a place in CTS
         // We have to create a new place
         // WARNING: In AdH many times you have different places that refer to the same place. Even if you identify them as duplicates of a main one... they will keep appearing with their PlaceId in the feed.
         //          This causes that several places are created, even if they refer to duplicates of the same places.
         //             Manual fix is to identify them, update the adhPlaceID to ctsPlaceID assignment to refer to the same place, update events, and delete duplicates.
         
            $newPlace=true;
      } else {
         // A place already exists. We will only update in case we detect a change on important fields
         $eventData["idPlace"] = $placeData["idPlace"] = mysqli_fetch_assoc($result_lugares)['ctsIdLugar'];

         $sql= "SELECT * FROM places where idPlace={$eventData['idPlace']}";
         $result_ctsLugar = mysqli_query($link, $sql);
         $existingPlaceData = mysqli_fetch_assoc($result_ctsLugar);
         
         // Remove fields not to be used to detect changes
         unset($existingPlaceData["created"]);
         unset($existingPlaceData["updated"]);         
      }

      // Look for a city assignment
      $sql = "SELECT * FROM adhCiudades_ctsTerritorios where adhCityId={$event["place__city__id"]}";
      $result_ciudad = mysqli_query($link, $sql);
      
      if (mysqli_num_rows($result_ciudad) == 1) // City has been already assigned
         $placeData["idCiudad"] = mysqli_fetch_assoc($result_ciudad)['ctsIdTerritorio'];
      else {
         // There is still no city assigned to the CityId from AdH. We have to identify city based on name (or coordinates) and create assignment.
         //   In the case of Madrid (AdHID 33) there is no link, as she is treated in CTS as a "comarca/metropoli" (level 7).
         //    When looking for a city with the name ('Madrid'), nothing will be found, so later coordinates will be used to find out the district (level 8) corresponding to the place.
         $sql = "SELECT id,nombre FROM territorios where nombre like '{$event["place__city__name"]}%' AND nivel=8 AND (provincia=28 OR provincia=19)";
         $result_busqueda_ciudad = mysqli_query($link, $sql);
         $ciudadesEncontradas = mysqli_num_rows($result_busqueda_ciudad);

         if ($ciudadesEncontradas == 1) { // In case 0 or more than 1 city are found, we will later try to use lat-lng of the event to identify the city.
            $placeData["idCiudad"] = mysqli_fetch_assoc($result_busqueda_ciudad)['id'];

            // Insert the link cityAdh-ciudadCts
            $sql = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ({$event["place__city__id"]}, {$placeData["idCiudad"]})";
            mysqli_query($link, $sql);
            $newCities++;
            //Not really tested, the $newCityNames thing
            array_push($newCityNames,$event["place__city__id"].' - '.$event["place__city__name"]);
         }
      }

      // POSTAL ADDRESS TREATMENT
      $direccionCompleta = $event["place__address"];
      
      // Content between brackets is considered an "indication" for the post address     
      if (preg_match( '!\(([^\)]+)\)!', $direccionCompleta, $match )) {
         $placeData["indicacion"] = $match[1];
         $direccionCompleta=preg_replace("/\([^)]+\)/","",$direccionCompleta);
      }      
      
      // First line  is considered the name of the place
      $longitudNombre = strpos($direccionCompleta, PHP_EOL); // Returns false (boolean) if not found
      if ($longitudNombre === false) {
         $placeData["nombre"] = $direccionCompleta;
         $placeData["direccion"] = $direccionCompleta;
      } else {
         $placeData["nombre"] = substr($direccionCompleta, 0, $longitudNombre);
         $placeData["direccion"] = trim(substr($direccionCompleta, $longitudNombre + 1));
      }

      // Coordinates treatment
      if (!$newPlace) {
         if ($existingPlaceData["lat"]!=$placeData["lat"] || $existingPlaceData["lng"]!=$placeData["lng"])
            $coordinatesChanged=true;
         else {
            // assign the old territory IDs and CP to the place
            $placeData["idBarrio"]=$existingPlaceData["idBarrio"];
            $placeData["idDistrito"]=$existingPlaceData["idDistrito"];
            $placeData["cp"]=$existingPlaceData["cp"];
         }
      }   

      if ($placeData["lat"]!="" && $placeData["lng"]!="" && ($newPlace || $coordinatesChanged || $placeData["idCiudad"]=="")) {
         // district, barrio and eventually city are calculated using coordinates
         // places with no coordinates will be shown at city level but not on lower levels. 
         // TODO: Verify it is not a problem.

         $punto = geoPHP::load("POINT({$placeData["lng"]} {$placeData["lat"]})", "wkt");

         // If no CTS territory was identified for the city using the name, we use the coordinates.
         //   This will also identify the district that contains the address in the case of Madrid.
         if ($placeData["idCiudad"] == "") {
            $ciudadesIds = getAllDescendantsOfLevel([601130028, 601080019], 8, 6); //Obtain all cities from Guadalajara and Madrid
            foreach ($ciudadesIds as $ciudadId) {
               $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/8/$ciudadId.geojson"), 'json');
               if ($poligono->contains($punto)) {
                  $placeData["idCiudad"] = $ciudadId;

                  // Insert the link cityAdh-ciudadCts. Not for districts of Madrid (AdHID 33), which is treated in CTS as a "comarca/metropoli" (level 7).
                  if ($event["place__city__id"]<>'33') {
                     $sql = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ({$event["place__city__id"]}, {$placeData["idCiudad"]})";
                     mysqli_query($link, $sql);
                     $newCities++;
                  }
                  break;
               }
            }
         }
         // If a CTS territory has been identified for the city, find the district and neighborhood
         if ($placeData["idCiudad"] != "") {
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
         }
         // Get the cp using GoogleMaps API for new and potentially updated locations
         $placeData["cp"]=getCP($placeData["lat"],$placeData["lng"]);
      }
      // TODO: It could make sense that, in case there are no coordinates, GoogleMapsAPI is used to find them
   
      // There could be a idCiudad because: the place already existed, the city was identified based on the name, or it was identified based on the coordinates
      //   In case of events from Madrid, coordinates will have identified the Great District
      //   In case of events with no coordinates, the city name could have revealed the city.
      //   In case of events with no coordinates in Madrid, there will be no idCiudad identified.
      if ($placeData["idCiudad"] != "") {  
         $placeData["idComarca"] = getParentID($placeData["idCiudad"]);
         if ($withCoords) { // No place is created for AdH places with no coords. Event will be directly assigned to idCiudad or idComarca 
            if ($newPlace) {
               // 2015.11.06 BUG: In many cases a place is created twice. And twice the link AdH and CTS was created.
               // Table has been changed with a UNIQUE constrain and the link will now not be created.
               // But the source problem, why an existing place is considered a newPlace, remains to be found.
               $eventData["idPlace"] = createPlace($placeData);            
               $sql = "INSERT INTO adhLugares_ctsDirecciones (`adhIdLugar`, `ctsIdLugar`) VALUES ({$event["place__id"]}, {$eventData["idPlace"]})";
               mysqli_query($link, $sql); 
               $newPlaces++;
            } else {
               $changes=array_diff_assoc($placeData,$existingPlaceData);
               if (count($changes)!=0) {
                  updatePlace($placeData);
                  $updatedPlaces++;
               }   
            }
         } else {  // Event in a municipality but no coordinates
            $eventData["idCiudad"] = $placeData["idCiudad"];
            $eventData["idComarca"] = 0; 
            $eventData["detalleDireccion"] = $event["place__address"];
         }
      } else {
         if ($withCoords) {
            // If it was not possible to find a city, it should be logged.
            // No city -> no place -> no event can be created. Jump to next event
            continue;
         }
         else { // Event in Madrid, with no coordinates ( $event["place__city__id"] should be '33' )
           $eventData["idCiudad"] = 0;
           $eventData["idComarca"] = $madridAdhId;
           $eventData["detalleDireccion"] = $event["place__address"];
         }
      }
      
      // EVENTOS
      // Place and City have been identified. Event gets updated/created.
      $eventData["fecha"] = date("Y-m-d H:i:s", $event["start_time"]); //Validate if it is satisfactory. 3:33 is reserved for events with no time.
      $eventData["fechaFin"] = "";
      $eventData["clase"] = "eventos";
      $eventData["titulo"] = $event["title"];
      $eventData["texto"] = $eventData["organizador"] = $eventData["url"] = $eventData["email"] = $eventData["etiquetas"] = "";
      $eventData["repeatsAfter"] = $eventData["idEntidad"] = 0;
      $eventData["eventoActivo"] = 1;
      $eventData["temperatura"] = ceil(($event["visits"]+1)/10); // let's make each 10 $event["visits"]; 1 degree temperature, up to a maximum of 5. +1 to avoid 0 temperature.
      if ($eventData["temperatura"]>5)
        $eventData["temperatura"]=5;

      // Event Description
      $ret = str_get_html($event["body"]);
      // Processing / cleaning html content
//    Not sure what was this for.   
//      foreach ($ret->find('p[class=demosphere-source-link-top]') as $p) {
//         $p->outertext = '';
//      }
       if (isset($ret)) { // It is rare, but there are cases of events with no body (not sure if this avoids failure)
         foreach ($ret->find('h3') as $h3) {
            $h3->outertext = '<p><strong>' . $h3->innertext . '</strong></p>';
         }
         foreach ($ret->find('h4') as $h4) {
            $h4->outertext = '<p><strong>' . $h4->innertext . '</strong></p>';
         }
         foreach ($ret->find('a') as $element) {
            $href = $element->href;
            if (substr($href, 0, 1) === '/') {
               $element->href = "http://agendadelhenares.org" . $href;
            }
         }
         foreach ($ret->find('img') as $element) {
            $source = $element->src;
            if (substr($source, 0, 1) === '/') {
               $element->src = "http://agendadelhenares.org" . $source;
            }
         }
         foreach ($ret->find('p[class=demosphere-sources]') as $p_source) {
            foreach ($p_source->find('a') as $a_source) {
               $eventData["url"] = $a_source->src;
            }
            $p->outertext = '';
         }      
         //echo "<pre>";
         //echo($ret);
         $eventData["texto"] = $ret->save();
         $ret->clear();
         unset($ret);
      }
      
      // Extract data on Organizer from page
      $html = file_get_html('http://agendadelhenares.org/evento/' . $event["id"]);
      //<a data-term="49" href="http://agendadelhenares.org/?selectVocabTerm=49">Sala Margarita Xirgú</a> 
         // When there is no organizer defined the term is 1 and empty name

      $a=$html->find('a[data-term]')[0];
         if (isset($a)) { // In some occasion, the event missed the section
         $organizerId = $a->getAttribute('data-term');
         $eventData["organizador"] = $a->innertext;
      } else {
         $organizerId = '1';
         $eventData["organizador"] = "[sin informacion]";
      }
         
      //$html->clear();
      //unset($html);

      if ($organizerId!='1'){
         // Look for the organizer assignment
         $sql = "SELECT * FROM adhEntidades_ctsEntidades where adhIdEntidad={$organizerId}";
         $result_entidad = mysqli_query($link, $sql);
         if (mysqli_num_rows($result_entidad)==1)
            $eventData["idEntidad"]= mysqli_fetch_assoc($result_entidad)['ctsIdEntidad'];
         else
            $eventData["organizador"] = $organizerId."-".$eventData["organizador"];
      }
      
      clean_all($GLOBALS);
      
      // Info Temáticas
      $eventData["tematicas"] = array();
      $sql = "SELECT * FROM tematicas where idTopicAgenda in ({$event["topics"]})";
      $result = mysqli_query($link, $sql);
      while ($fila = mysqli_fetch_assoc($result)) {
         $eventData["tematicas"][] = $fila["idTematica"];
      }
      
      // We look for the event
      $newEvent = false;
      $sql = "SELECT * FROM adhEventos_ctsEventos where adhIdEvento={$event["id"]}";
      $result_eventos = mysqli_query($link, $sql);
      $numEventosEncontrados=mysqli_num_rows($result_eventos);
      if ($numEventosEncontrados == 0) {
         // The event does not exist in CTS. There is still no assignment to an event in CTS
         // We have to create a new event
         $newEvent=true;
      } else {
         // An event already exists. 
         // We will only update in case we detect a change.
         $eventData["idEvento"] = mysqli_fetch_assoc($result_eventos)['ctsIdEvento'];

         $sql= "SELECT * FROM eventos where idEvento={$eventData['idEvento']}";
         $result_ctsEvento = mysqli_query($link, $sql);
         $existingEventData = mysqli_fetch_assoc($result_ctsEvento);
         // Remove fields not used to detect changes   
         unset($existingEventData["created"]);
         unset($existingEventData["updated"]);
         unset($existingEventData["tipo"]);
         // Add thematic info
         $sql="SELECT * FROM eventos_tematicas 
                WHERE eventos_tematicas.idEvento={$eventData['idEvento']}";
         $result=mysqli_query($link, $sql);
         while($fila=mysqli_fetch_assoc($result)) {
            $existingEventData["tematicas"][] = $fila["idTematica"];
         }       
      }
  
      if ($newEvent) {
         // Tipo is set to "recurrente" if an event with the same name and address appened in the previos two months; "convocatoria" otherwise.
         $timeLimit=new DateTime($eventData["fecha"]);
         $timeLimit->modify('- 2 months');
         $timeLimit=$timeLimit->format('Y-m-d');
         $sql = "SELECT count(*) FROM eventos where titulo='{$eventData["titulo"]}' and fecha>$timeLimit";
         $result = mysqli_query($link, $sql);
         $recentOccurrences = mysqli_fetch_row($result)[0];
         if ($recentOccurrences>=1) {
            $eventData["tipo"]="recurrente";
         } else {
            $eventData["tipo"]="convocatoria";
         } 
         
         $eventData["idEvento"]=createEvent($eventData);

         $sql_insertEvento = "INSERT INTO adhEventos_ctsEventos (`adhIdEvento`, `ctsIdEvento`) VALUES ({$event["id"]}, {$eventData["idEvento"]})";
         mysqli_query($link, $sql_insertEvento);
         $newEvents++;      
      } else {
         $changes=array_diff_assoc($eventData,$existingEventData);
         if (count($changes)!=0) {
            // Updated without the field "tipo", as this is not really coming from AdH,
            //   and this value could be manually for tool-shows
            updateEvent($eventData);
            $updatedEvents++;
         }   
      }      
   }
   usleep(15000000);//google free 2.500 searchs with speed 5 pers sec.
}
echo "Importados:".PHP_EOL."Total Events: ".$totalEvents.PHP_EOL."Inserted Events: ".$newEvents.PHP_EOL."Updated Events: ".$updatedEvents.PHP_EOL."Inserted Places: ".$newPlaces.PHP_EOL."Updated Places: ".$updatedPlaces.PHP_EOL."New Cities: ".$newCities.PHP_EOL;
if ($newCityNames) {
   echo "New City Names: ";
   print_r($newCityNames);
}
?>