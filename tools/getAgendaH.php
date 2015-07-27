<?php

set_time_limit(0);
error_reporting(E_ERROR);
include "../db.php";
include_once '../vendor/simplehtmldom/simple_html_dom.php';
include_once('../vendor/phayes/geophp/geoPHP.inc');

ini_set('default_charset', 'utf-8');
$link = connect();

// Script is unactive unless it is required
exit();

//FIRST version of the extraction script, which used scrapping of the event and place pages with simple_html_dom
// Later we discovered that we could get the info directly using the json feed.

$numberOfDays = 1;
$seconds = $numberOfDays * 86400;

$url = "http://agendadelhenares.org/event-list-json?selectRecentChanges=" . $seconds . "&orderByLastBigChanges=true";

// This format can be used to obtain past events:
//http://agendadelhenares.org/event-list-json?startTime=1370037601&endTime=1372629601&limit=150
$raw_data = file_get_contents($url);
$data = json_decode($raw_data, true);

$i = 0;
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
     
    */
   // Identificar todo lo que queremos, inicializarlos a cero, y así nos sirve para saber todo lo que queremos.

   $adhEventId = $event["id"];
   $adhEventTitle = $event["title"];
   $adhTopics = $event["topics"];
   //$agendaTematicas=split(',',utf8_encode($event["topics"]));    
   $adhIdLugar = $event["place__id"];
   $adhCityId = $event["place__city__id"];
   $adhNombreCiudad = $event["place__city__name"];
   $adhStartTime = $event["start_time"];
   $adhVisitas = $event["visits"];

   // TEMÁTICAS
   $eventData["tematicas"] = array();
   $sql = "SELECT * FROM tematicas where idTopicAgenda in ($adhTopics)";
   $result = mysqli_query($link, $sql);
   while ($fila = mysqli_fetch_assoc($result)) {
      $eventData["tematicas"][] = $fila["idTematica"];
   }

   //PLACE
   // We look for the place
   $sql = "SELECT * FROM adhLugares_ctsDirecciones where adhIdLugar=$adhIdLugar";
   $result_lugares = mysqli_query($link, $sql);
   $numLugaresEncontrados=mysqli_num_rows($result_lugares);
   if ($numLugaresEncontrados == 0) {
      // The place does not exist in CTS. There is still no assignment to a place in CTS
      // We have to create a new direccion. Initialice values to 0.
      $newPlace=true;
      $placeData["idCiudad"] = $placeData["idDistrito"] = $placeData["idBarrio"] = $placeData["nombre"] = $placeData["direccion"] = $placeData["indicacion"] = $placeData["cp"] = $placeData["lat"] = $placeData["lng"] = "";
      $placeData["zoom"] = "15";
      $placeData["direccionActiva"] = "1";
   } else {
      // A place already exists. 
      // We initialice with DB values and will only update in case we detect a change.
      $eventData["idLugar"] = mysqli_fetch_assoc($result_lugares)['ctsIdlugar'];
      
      $sql= "SELECT * FROM direcciones where idDireccion={$eventData['idLugar']}";
      $result_ctsLugar = mysqli_query($link, $sql);
      $existingPlaceData = mysqli_fetch_assoc($result_ctsLugar);
   }

   // Start looking for the city
   $sql = "SELECT * FROM adhCiudades_ctsTerritorios where adhCityId=$adhCityId";
   $result_ciudad = mysqli_query($link, $sql);
   if (mysqli_num_rows($result_ciudad) == 0) {
      // There is still no city assignment to the CityId from AdH
      // We have to find city and create assignment
      $sql = "SELECT id,nombre FROM territorios where nombre like '$adhNombreCiudad%' AND nivel=8 AND (provincia=28 OR provincia=19)";
      $result_busqueda_ciudad = mysqli_query($link, $sql);
      $ciudadesEncontradas = mysqli_num_rows($result_busqueda_ciudad);
      if ($ciudadesEncontradas == 1) {
         $ciudad = mysqli_fetch_assoc($result_busqueda_ciudad);
         $placeData["idCiudad"] = $ciudad['id'];

         // Insert the link cityAdh-ciudadCts
         $sql_insertTerritorio = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ($adhCityId, {$placeData["idCiudad"]})";
         //mysqli_query($link, $sql_insertTerritorio);
      }
      // In case 0 or more than 1 city are found, we will try to use lat-lng of the event to identify the city.
   } else { // City has been already assigned
      $ciudad = mysqli_fetch_assoc($result_ciudad);
      $placeData["idCiudad"] = $ciudad['ctsIdTerritorio'];
   }

   // Now we can get the data to create the new place

   $html = file_get_html('http://agendadelhenares.org/lugar/' . $adhIdLugar);
   //Attention: AdH redirects to the main place in case a placeId refers to a variation of a main place
   // It seems appropriate to use the main place.
   $lat=$html->find('meta[itemprop=latitude]', 0)->content;
   $lng=$html->find('meta[itemprop=longitude]', 0)->content;
   
   if (!$newPlace && ($placeData["lat"]!=$lat || $placeData["lng"]!=$lng))
      $coordinatesChanged=true;
   $placeData["lat"] = $lat;
   $placeData["lng"] = $lng;
   //These can be empty in case place is not geo-located.

   $direccionCompleta = $html->find('a[itemprop=streetAddress]')[0]->innertext;
   // <a href="http://agendadelhenares.org/lugar/431" rel="nofollow" itemprop="streetAddress">Federación Comarcal de Asociación de Vecinos de Alcalá de henares. Calle Eduardo Pascual y Cuéllar N 10</a>
   $longitudNombre = strpos($direccionCompleta, '<br />'); // Returns false (boolean) if not found
   if ($longitudNombre === false) {
      $placeData["nombre"] = $direccionCompleta;
      $placeData["direccion"] = $direccionCompleta;
   } else {
      $placeData["nombre"] = substr($direccionCompleta, 0, $longitudNombre);
      $placeData["direccion"] = trim(substr($direccionCompleta, $longitudNombre + 6));
   }

   $googleLink = $html->find('a[class=mapimage-link]', 0)->href;
   //<a class="mapimage-link" href="https://www.google.com/maps/place/40.482854253155,-3.3528684631599/@40.482854253155,-3.3528684631599,17z">
   if (isset($googleLink))
      if (preg_match('~,(.[0-9]+)z~', $googleLink, $matches))
         $placeData["zoom"] = $matches[1];

   $html->clear();
   unset($html);

   if (isset($placeData["lat"]) && isset($placeData["lng"]) && ($newPlace || $coordinatesChanged || $placeData["idCiudad"]=="")) {
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
               $sql_insertTerritorio = "INSERT INTO adhCiudades_ctsTerritorios (`adhCityId`, `ctsIdTerritorio`) VALUES ($adhCityId, {$placeData["idCiudad"]})";
               //mysqli_query($link, $sql_insertTerritorio);
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
         //$eventData["idLugar"] = createPlace($placeData);
      } else {
         $changes=array_diff_assoc($existingPlaceData,$placeData);
         if (count($changes)!=0) {
            //updatePlace($placeData,$eventData["idLugar"]);
         }   
      }

   } else {
      // If it was not possible to find a city, it should be logged.
      // No city -> no place -> no event can be created. Jump to next event
      continue;
   }

   // EVENTOS
   // Place and City have been identified. Event gets updated/created.

   $eventData["idEvento"] = "";
   $eventData["fecha"] = idate("Y-m-d", $event["start_time"]); //Validate if it is satisfactory. 3:33 is reserved for events with no time.
   $eventData["fechaFin"] = "";
   $eventData["clase"] = "eventos";
   $eventData["tipo"] = "convocatoria";
   $eventData["titulo"] = $event["title"];
   $eventData["texto"] = "";
   $eventData["idEntidad"] = ""; //POSIBLE ASIGNAR???
   $eventData["idDireccion"] = "";
   $eventData["url"] = "";
   $eventData["email"] = "";
   $eventData["etiquetas"] = "";
   $eventData["repeatsAfter"] = 0;
   $eventData["eventoActivo"] = 1;
   $eventData["temperatura"] = ceil($event["visits"]/10); // let's make each 10 $event["visits"]; 1 degree temperature, up to a maximum of 5.
   if ($eventData["temperatura"]>5)
     $eventData["temperatura"]=5;

   //createEvent($eventData);
   // DESCRIPTION
   $html = file_get_html('http://agendadelhenares.org/evento/' . $adhEventId);
   $ret = $html->find('div[id=textPart0]')[0];


   // Processing / cleaning html content

   foreach ($ret->find('p[class=demosphere-source-link-top]') as $p) {
      $p->outertext = '';
   }
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
   foreach ($ret->find('p[class=demosphere-sources') as $p_source) {
      foreach ($p_source->find('a') as $a_source) {
         $eventData["url"] = $a_source->src;
      }
      $p->outertext = '';
   }   
   
   //echo "<pre>";
   echo($ret);
   $eventData["texto"] = $ret;

   $html->clear();
   unset($html);

   // INSERT EVENT
   createEvent($eventData);

}
?>