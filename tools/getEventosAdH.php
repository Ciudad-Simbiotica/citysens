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

// Script is unactive unless it is required
//exit();

$lastChangedLimit = time()- (1 * 86400); //To be compared with the event's "changed" field to see if it got updated since the last time we checked. 

$url = "http://agendadelhenares.org/event-list-json?changed&place__address&place__latitude&place__longitude&place__zoom&body";

// This format can be used to obtain past events:
//http://agendadelhenares.org/event-list-json?startTime=1370037601&endTime=1372629601&limit=150&changed&place__address&place__latitude&place__longitude&place__zoom&body

$raw_data = file_get_contents($url);
$data = json_decode($raw_data, true);

foreach ($data["events"] as $event) {
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
     
    EXTRA:
    *      body: "<h3> Taller social autogestionado desde primavera del 2010. Nuestro objetivo es que el uso de la bici sea el mayor posible en Alcalá de Henares. Nos encontramos en el <a class="" href="http://cafecontinental.es/">Café Continental</a>, calle Empecinado nº23 (acceso por el Callejón del Vicario). </h3> <h3> Los días que ejercemos actividad de taller actualmente son los Domingos y Lunes desde las 18 hasta las 21h (aprox). </h3> <h3> Anímate a rescatar esa bici que tienes guardada en el trastero cogiendo polvo, tan solo necesitamos de tu predisposición a contribuir activamente en el proceso de reparación. </h3> <p><img class="floatRight" src="/files/import-images/326c3d1969942aae8d043c23e69f99de.png" alt="" width="240" height="239"/></p> <p class=" demosphere-sources"> Origen : <a class="" href="http://tallersocialdealcala.blogspot.com.es/">http://tallersocialdealcala.blogspot.com.es/</a> </p>",
    *      changed: 1435578517
    *      place__address: "Callejón del Vicario Alcalá de Henares",
    *      place__latitude: 40.479449965183,
    *      place__longitude: -3.367143009787,
    *      place__zoom: 16
    */

   if ($event["changed"]>=$lastChangedLimit) {
   
      //PLACE
      // Initialice
      
      $placeData["idCiudad"] = $placeData["idDistrito"] = $placeData["idBarrio"] = $placeData["nombre"] = $placeData["direccion"] = $placeData["indicacion"] = "";
      $placeData["direccionActiva"] = "1";
      $placeData["lat"] = ($event["place__latitude"]!=0) ? (string)$event["place__latitude"]: "";
      $placeData["lng"] = ($event["place__longitude"]!=0) ? (string)$event["place__longitude"]: "";
      //These can be empty in case place is not geo-located.
      $placeData["zoom"] = ($event["place__zoom"]!=0) ? $event["place__zoom"]: "";
      
      // We look for the place assignment
      $sql = "SELECT * FROM adhLugares_ctsDirecciones where adhIdLugar={$event["place__id"]}";
      $result_lugares = mysqli_query($link, $sql);
      $numLugaresEncontrados=mysqli_num_rows($result_lugares);
      if ($numLugaresEncontrados == 0) {
         // The place does not exist in CTS. There is still no assignment to a place in CTS
         // We have to create a new direccion. 
         $newPlace=true;
      } else {
         // A place already exists. We will only update in case we detect a change on important fields.
         $eventData["idDireccion"] = mysqli_fetch_assoc($result_lugares)['ctsIdLugar'];

         $sql= "SELECT * FROM direcciones where idDireccion={$eventData['idDireccion']}";
         $result_ctsLugar = mysqli_query($link, $sql);
         $existingPlaceData = mysqli_fetch_assoc($result_ctsLugar);
         
         // Remove fields not to be used to detect changes
         unset($existingPlaceData["idDireccion"]);
         unset($existingPlaceData["cp"]);
         unset($existingPlaceData["created"]);
         unset($existingPlaceData["updated"]);
         
      }

      // Look for the city assignment
      $sql = "SELECT * FROM adhCiudades_ctsTerritorios where adhCityId={$event["place__city__id"]}";
      $result_ciudad = mysqli_query($link, $sql);
      if (mysqli_num_rows($result_ciudad) == 0) {
         // There is still no city assigned to the CityId from AdH
         // We have to find city and create assignment
         $sql = "SELECT id,nombre FROM territorios where nombre like '{$event["place__city__name"]}%' AND nivel=8 AND (provincia=28 OR provincia=19)";
         $result_busqueda_ciudad = mysqli_query($link, $sql);
         $ciudadesEncontradas = mysqli_num_rows($result_busqueda_ciudad);
         if ($ciudadesEncontradas == 1) {
            $ciudad = mysqli_fetch_assoc($result_busqueda_ciudad);
            $placeData["idCiudad"] = $ciudad['id'];

            // Insert the link cityAdh-ciudadCts
            $sql_insertTerritorio = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ({$event["place__city__id"]}, {$placeData["idCiudad"]})";
            mysqli_query($link, $sql_insertTerritorio);
         }
         // In case 0 or more than 1 city are found, we will try to use lat-lng of the event to identify the city.
      } else { // City has been already assigned
         $ciudad = mysqli_fetch_assoc($result_ciudad);
         $placeData["idCiudad"] = $ciudad['ctsIdTerritorio'];
      }

      $direccionCompleta = $event["place__address"];
      
      $longitudNombre = strpos($direccionCompleta, PHP_EOL); // Returns false (boolean) if not found
      if ($longitudNombre === false) {
         $placeData["nombre"] = $direccionCompleta;
         $placeData["direccion"] = $direccionCompleta;
      } else {
         $placeData["nombre"] = substr($direccionCompleta, 0, $longitudNombre);
         $placeData["direccion"] = trim(substr($direccionCompleta, $longitudNombre + 1));
      }

      if (!$newPlace && ($existingPlaceData["lat"]!=$event["place__latitude"] || $existingPlaceData["lng"]!=$event["place__longitude"]))
         $coordinatesChanged=true;

      if ($placeData["lat"]!="" && $placeData["lng"]!="" && ($newPlace || $coordinatesChanged || $placeData["idCiudad"]=="")) {
         // district, barrio and eventually city are calculated using coordinates
         // places with no coordinates will be shown at city level but not on lower levels.
         // TODO: Verify it is not a problem.

         $punto = geoPHP::load("POINT({$placeData["lng"]} {$placeData["lat"]})", "wkt");

         // If no CTS territory was identified for the city using the name, we use the coordinates
         if ($placeData["idCiudad"] == "") {
            $ciudadesIds = getAllDescendantsOfLevel([601130028, 601080019], 8, 6); //Obtain all cities from Guadalajara and Madrid
            foreach ($ciudadesIds as $ciudadId) {
               $poligono = geoPHP::load(file_get_contents("../shp/geoJSON/8/$ciudadId.geojson"), 'json');
               if ($poligono->contains($punto)) {
                  $placeData["idCiudad"] = $ciudadId;

                  // Insert the link cityAdh-ciudadCts
                  $sql_insertTerritorio = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ({$event["place__city__id"]}, {$placeData["idCiudad"]})";
                  mysqli_query($link, $sql_insertTerritorio);
                  break;
               }
            }
         }

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
      }
      if ($placeData["idCiudad"] != "") {
         if ($newPlace) {
            // Get the cp using GoogleMaps API           
            $placeData["cp"]=getCP($placeData["lat"],$placeData["lng"]);            
            
            $eventData["idDireccion"] = createPlace($placeData);            
            
            $sql_insertLugar = "INSERT INTO adhLugares_ctsDirecciones (`adhIdLugar`, `ctsIdLugar`) VALUES ({$event["place__id"]}, {$eventData["idDireccion"]})";
            mysqli_query($link, $sql_insertLugar);
            
         } else {
            $changes=array_diff_assoc($existingPlaceData,$placeData);
            if (count($changes)!=0) {
               
               // Get the cp using GoogleMaps API
               $placeData["cp"]=getCP($placeData["lat"],$placeData["lng"]);
               
               updatePlace($placeData,$eventData["idDireccion"]);
            }   
         }

      } else {
         // If it was not possible to find a city, it should be logged.
         // No city -> no place -> no event can be created. Jump to next event
         continue;
      }

      // TEMÁTICAS
      $eventData["tematicas"] = array();
      $sql = "SELECT * FROM tematicas where idTopicAgenda in ({$event["topics"]})";
      $result = mysqli_query($link, $sql);
      while ($fila = mysqli_fetch_assoc($result)) {
         $eventData["tematicas"][] = $fila["idTematica"];
      }
      
      // EVENTOS
      // Place and City have been identified. Event gets updated/created.

      $eventData["fecha"] = date("c", $event["start_time"]); //Validate if it is satisfactory. 3:33 is reserved for events with no time.
      $eventData["fechaFin"] = "";
      $eventData["clase"] = "eventos";
      $eventData["tipo"] = "convocatoria";
      $eventData["titulo"] = $event["title"];
      $eventData["texto"] = "";
      $eventData["idEntidad"] = ""; //SERÍA POSIBLE ASIGNAR???
      $eventData["url"] = "";
      $eventData["email"] = "";
      $eventData["etiquetas"] = "";
      $eventData["repeatsAfter"] = 0;
      $eventData["eventoActivo"] = 1;
      $eventData["temperatura"] = ceil(($event["visits"]+1)/10); // let's make each 10 $event["visits"]; 1 degree temperature, up to a maximum of 5. +1 to avoid 0 temperature.
      if ($eventData["temperatura"]>5)
        $eventData["temperatura"]=5;

      
      // We look for the event
      $sql = "SELECT * FROM adhEventos_ctsEventos where adhIdEvento={$event["id"]}";
      $result_eventos = mysqli_query($link, $sql);
      $numEventosEncontrados=mysqli_num_rows($result_eventos);
      if ($numEventosEncontrados == 0) {
         // The event does not exist in CTS. There is still no assignment to an event in CTS
         // We have to create a new event.
         $newEvent=true;
      } else {
         // An event already exists. 
         // We will only update in case we detect a change.
         $eventData["idEvento"] = mysqli_fetch_assoc($result_eventos)['ctsIdEvento'];

         $sql= "SELECT * FROM eventos where idEvento={$eventData['idEvento']}";
         $result_ctsEvento = mysqli_query($link, $sql);
         $existingEventData = mysqli_fetch_assoc($result_ctsEvento);
      }
      
      // Event Description
      $ret = str_get_html($event["body"]);

      // Processing / cleaning html content

//    Not sure what was this for.   
//      foreach ($ret->find('p[class=demosphere-source-link-top]') as $p) {
//         $p->outertext = '';
//      }
      foreach ($ret->find('h3') as $h3) {
         $h3->outertext = '<p>' . $h3->innertext . '</p>';
      }
      foreach ($ret->find('h4') as $h4) {
         $h4->outertext = '<p>' . $h4->innertext . '</p>';
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
      
      if ($newEvent) {
         $eventData["idEvento"]=createEvent($eventData);
         // Include link evento evento?

         $sql_insertEvento = "INSERT INTO adhEventos_ctsEventos (`adhIdEvento`, `ctsIdEvento`) VALUES ({$event["id"]}, {$eventData["idEvento"]})";
         mysqli_query($link, $sql_insertLugar);
      } else {
         $changes=array_diff_assoc($existingEventData,$eventData);
         if (count($changes)!=0) {
          updateEvent($placeData,$eventData["idLugar"]);
         }   
      }      
   }
}
echo "Importados!!";
?>