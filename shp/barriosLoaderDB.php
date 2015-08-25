<?php

include_once "ShapeFile.inc.php";
include "../db.php";
set_time_limit(0);
error_reporting(E_ALL);

//Script not active, unless needed.
exit();

$arrayNombres =[
"079011" => "Palacio",
"079012" => "Embajadores",
"079013" => "Cortes",
"079014" => "Justicia",
"079015" => "Universidad",
"079016" => "Sol",
"079021" => "Imperial",
"079022" => "Las Acacias",
"079023" => "La Chopera",
"079024" => "Legazpi",
"079025" => "Delicias",
"079026" => "Palos de Moguer",
"079027" => "Atocha",
"079031" => "Pacífico",
"079032" => "Adelfas",
"079033" => "Estrella",
"079034" => "Ibiza",
"079035" => "Jerónimos",
"079036" => "Niño Jesús",
"079041" => "Recoletos",
"079042" => "Goya",
"079043" => "Fuente del Berro",
"079044" => "Guindalera",
"079045" => "Lista",
"079046" => "Castellana",
"079051" => "El Viso",
"079052" => "Prosperidad",
"079053" => "Ciudad Jardín",
"079054" => "Hispanoamérica",
"079055" => "Nueva España",
"079056" => "Castilla",
"079061" => "Bellas Vistas",
"079062" => "Cuatro Caminos",
"079063" => "Castillejos",
"079064" => "Almenara",
"079065" => "Valdeacederas",
"079066" => "Berruguete",
"079071" => "Gaztambide",
"079072" => "Arapiles",
"079073" => "Trafalgar",
"079074" => "Almagro",
"079075" => "Ríos Rosas",
"079076" => "Vallehermoso",
"079081" => "El Pardo",
"079082" => "Fuentelarreina",
"079083" => "Peña Grande",
"079084" => "El Pilar",
"079085" => "La Paz",
"079086" => "Valverde",
"079087" => "Mirasierra",
"079088" => "El Goloso",
"079091" => "Casa de Campo",
"079092" => "Argüelles",
"079093" => "Ciudad Universitaria",
"079094" => "Valdezarza",
"079095" => "Valdemarín",
"079096" => "El Plantío",
"079097 "=> "Aravaca",
"079101" => "Los Cármenes",
"079102" => "Puerta del Ángel",
"079103" => "Lucero",
"079104" => "Aluche",
"079105" => "Campamento",
"079106" => "Cuatro Vientos",
"079107" => "Las Águilas",
"079111" => "Comillas",
"079112" => "Opañel",
"079113" => "San Isidro",
"079114" => "Vista Alegre",
"079115" => "Puerta Bonita",
"079116" => "Buenavista",
"079117" => "Abrantes",
"079121" => "Orcasitas",
"079122" => "Orcasur",
"079123" => "San Fermín",
"079124" => "Almendrales",
"079125" => "Moscardó",
"079126" => "Zofío",
"079127" => "Pradolongo",
"079131" => "Entrevías",
"079132" => "San Diego",
"079133" => "Palomeras Bajas",
"079134" => "Palomeras Sureste",
"079135" => "Portazgo",
"079136" => "Numancia",
"079141" => "Pavones",
"079142" => "Horcajo",
"079143" => "Marroquina",
"079144" => "Media Legua",
"079145" => "Fontarrón",
"079146" => "Vinateros",
"079151" => "Ventas",
"079152" => "Pueblo Nuevo",
"079153" => "Quintana",
"079154" => "Concepción",
"079155" => "San Pascual",
"079156" => "San Juan Bautista",
"079157" => "Colina",
"079158" => "Atalaya",
"079159" => "Costillares",
"079161" => "Palomas",
"079162" => "Piovera",
"079163" => "Canillas",
"079164" => "Pinar del Rey",
"079165" => "Apóstol Santiago",
"079166" => "Valdefuentes",
"079171" => "San Andrés",
"079172" => "San Cristóbal",
"079173" => "Butarque",
"079174" => "Los Rosales",
"079175" => "Los Ángeles",
"079181" => "Casco Histórico de Vallecas",
"079182" => "Santa Eugenia",
"079191" => "Casco Histórico de Vicálvaro",
"079192" => "Ambroz",
"079201" => "Simancas",
"079202" => "Hellín",
"079203" => "Amposta",
"079204" => "Arcos",
"079205" => "Rosas",
"079206" => "Rejas",
"079207" => "Canillejas",
"079208" => "El Salvador",
"079211" => "Alameda de Osuna",
"079212" => "Aeropuerto",
"079213" => "Casco Histórico de Barajas",
"079214" => "Timón",
"079215" => "Corralejos",
];
// This didn't work. Special chars were not properly handled.

//$shp = new ShapeFile("lineas_limite/SHP_ETRS89/poligonos_municipio_etrs89/poligonos_municipio_etrs89.shp"); // along this file the class will use file.shx and file.dbf
//$shp = new ShapeFile("Distritos-1/Distritos.shp"); // along this file the class will use file.shx and file.dbf
//$shp = new ShapeFile("lineas_limite/SHP_ETRS89/poligonos_provincia_etrs89/poligonos_provincia_etrs89.shp"); // along this file the class will use file.shx and file.dbf

$shp = new ShapeFile("Agosto/BarriosdeMadrid.shp"); // along this file the class will use file.shx and file.dbf
$level = '9';

$link=connect();

mysqli_query($link,"SET NAMES 'utf8'");

$id=901280111;
echo "<pre>";
while ($record = $shp->getNext()) 
{
	$id++;
   
   $datos=$record->getDbfData();

// does not apply	$codbdt=trim(utf8_encode($datos["CODBDT"]));
// does not apply	
   $geocodigo=trim(utf8_encode($datos["GEOCODIGO"]));
// does not apply	$desbdt=trim(utf8_encode($datos["DESBDT"]));
// not needed	$deleted=trim(utf8_encode($datos["deleted"]));
//    $codine = trim(utf8_encode($datos["CODINE"]));
   //trim(utf8_encode($datos["COD_CCAA"]));

   $nombre=$arrayNombres[$geocodigo];
   
   echo $id, " ", $geocodigo, " ", $nombre, PHP_EOL;
   
   $coordenadas=$record->getShpData();   
    
    $xmin=$coordenadas["xmin"];
    $ymin=$coordenadas["ymin"];
    $xmax=$coordenadas["xmax"];
    $ymax=$coordenadas["ymax"];
    
		// id codes are going to be:
        // CCAA: level(1)+country(2)+CCAA(6)    CORRECTO
        //           401000009
        // Province: level(1)+country(2)+CCAA(2)+province(4)  CORRECTO
        //           601XX0028 (Madrid)
        // Region: level(1)+country(2)+Province(2)+region(4) (in province) CORRECTO - Pero faltan casi todas.
        //           701280002 (Corredor del Henares)
        // City: level(1)+country(2)+Province(2)+cityNumber(4) (in province) CORRECTO //Faltan asignaciones a región
        //           801280005
        // District: level(1)+country(2)+Province(2)+DistrictNumber(4)(in province) CORRECTO
        //           901280009
      
    $sql=utf8_decode("INSERT INTO territorios
				(id,nombre,provincia,idPadre,idDescendiente, 
					xmin,ymin,xmax,ymax,nivel,activo)
				VALUES ('$id','$nombre','28','0','0',
					'$xmin','$ymin','$xmax','$ymax','$level','1')");
    mysqli_query($link,$sql);
    
	  echo $sql, PHP_EOL;
     echo "geoJSON/".$level."/".$geocodigo.".geojson", PHP_EOL;
     
    $data=file_get_contents("geoJSON/".$level."/".$geocodigo.".geojson");
    echo $data, PHP_EOL;
     
    file_put_contents("geoJSON/".$level."/".$id.".geojson",$data); 
}


?>
