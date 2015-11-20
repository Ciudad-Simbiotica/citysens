<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$suggestions=array();

//  PARÁMETROS
//  
//    query: texto,
//    idTerritorio: window.conf.idTerritorio,
//    alrededores: window.conf.alrededores,
//    date: "any",
//    tipo: window.conf.tipo
//  

//ToDo: Sugerencia de búsquedas comunes
/*
$sugestion["tipo"]="busqueda";
$sugestion["texto1"]="protesta sanciones";
$sugestion["texto2"]="";
$sugestion["id"]=0;
array_push($sugestions,$sugestion);
*/

function getFiltrosTiempo($cadena)
{
// TIME FILTERS that could be applied (change the date period shown)
// - Name of a month
// - Specific date
// - Último mes / semana / año ? (para ver pasado) (PENDING)
//
// DECIDED NOT TU USE:
//  - 1 month, 2 months... 2 weeks, 3 weeks... 10 days, 20 days...
//
//   TODO: This filters would need to be localization dependant. For the moment being, it is good enough.
//   

   $fechas=array();
   $matches=array();
   $months =["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]; // Checking for months
   $date_regex = "/^(\d?\d)[\/\-.](\d?\d)([\/\-.]((\d\d)?(\d{2})?)?)?$/"; // Checking for dates
  
  if (preg_match($date_regex, $cadena, $matches)) {

    $year=$matches[4];
    if ($year=="") {
       $year=date('Y');
    } else if (strlen($year)==2) {
       $year="20".$year;   
    }
    if (checkdate($matches[2], $matches[1], $year)) {
       $fecha["name"]=$matches[1]."/".$matches[2]."/".$year;
       $fecha["start"]=$year."-".$matches[2]."-".$matches[1];       
       $fecha["end"]="";
       array_push($fechas,$fecha);
    } else {
       $fecha["name"]=$fecha["start"]="Fecha no válida";
       $fecha["end"]="";
       array_push($fechas,$fecha);
    }
  }
  
//    Decided not to apply this kind of filter. It would have used
//         $period_regex = "/^([12]?\d) (s(e(m(a(n(a(s)?)?)?)?)?)?|d((i|í)(a(s)?)?)?|m(e(s(e(s)?)?)?)?)$/i";
//         
//    else if (preg_match($period_regex, $cadena, $matches)){
//     $fecha["start"]=date("Y-m-d");
//     switch ($matches[2][0]) {
//        case "s":
//        case "S":
//           $periodo="semanas";
//           $fecha["end"]=date("Y-m-d", strtotime("+".$matches[1]." week",strtotime($fecha["start"])));
//           break;
//        case "d":
//        case "D":
//           $periodo="días";
//           $fecha["end"]=date("Y-m-d", strtotime("+".$matches[1]." day",strtotime($fecha["start"])));
//           break;
//        case "m":
//        case "M":
//           $periodo="meses";
//           $fecha["end"]=date("Y-m-j", strtotime("+".$matches[1]." month",strtotime($fecha["start"])));
//           break;
//     }
//     $fecha["name"]=$matches[1]." ".$periodo;
//     array_push($fechas,$fecha);
//  }
  else { // Ckecking for months' names
     $suggestedMonths=preg_grep("/^{$cadena}/i",$months);
  
     if($suggestedMonths) {
        // For each month that matches, a suggestion for current year is offered
        foreach($suggestedMonths as $index => $month) {
           $year=date('Y');
           $fecha["start"]=$year."-".($index+1)."-1";
           $fecha["end"]=date("Y-m-t", strtotime($fecha["start"]));
           $fecha["name"]=$month." ".date('Y');
           array_push($fechas,$fecha);
        }
        // In case only one month matches, the previous and next year are offered, in addition to current year
        if (count($fechas)==1 && strlen($cadena)>2) {
           $year=-1+$year;
           $fecha["start"]=$year."-".($index+1)."-1";
           $fecha["end"]=date("Y-m-t", strtotime($fecha["start"]));
           $fecha["name"]=$month." ".$year;
           array_push($fechas,$fecha);

           $year=$year+2;
           $fecha["start"]=$year."-".($index+1)."-1";
           $fecha["end"]=date("Y-m-t", strtotime($fecha["start"]));
           $fecha["name"]=$month." ".$year;
           array_push($fechas,$fecha);

        }
     }
  } 

return $fechas;   
}


$fechas=getFiltrosTiempo($_GET["query"]);
if ($fechas) {
   foreach($fechas as $fecha)
	{
		$suggestion["tipo"]="tiempo";
      $suggestion["texto1"]=$suggestion["id"]=htmlentities($fecha["name"]);         
//    $sugestion["abrev"]=htmlentities(rtrim(substr($fecha["name"],0,27))."...");
//		$sugestion["texto2"]="";
      $suggestion["textoBuscado"]=htmlentities($_GET["query"]); //for bold hint string
		$suggestion["start"]=$fecha["start"];
      $suggestion["end"]=$fecha["end"];
		array_push($suggestions,$suggestion);
      unset($suggestion);
	} 
}

$tematicas=getTematicas($_GET["query"],4);
foreach($tematicas as $tematica)
{
	//print_r($entidad);
	$suggestion["tipo"]="tematica";
   $suggestion["texto1"]=htmlentities($tematica["tematica"]);
   //abrev is only defined if required. It will be used in case it exists.
   if(mb_strlen($tematica["tematica"])>30)
      $suggestion["abrev"]=htmlentities(rtrim(substr($tematica["tematica"],0,27))."...");
	$suggestion["texto2"]="";
   $suggestion["textoBuscado"]=htmlentities($_GET["query"]);//for bold hint string
	$suggestion["id"]=$tematica["idTematica"];
	array_push($suggestions,$suggestion);
   unset($suggestion);
}



/*
$sugestion["tipo"]="sanidad";
$sugestion["texto1"]="Sanidad";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);
*/

/*
$sugestion["tipo"]="lugar";
$sugestion["texto1"]="El Ensanche";
$sugestion["texto2"]="Alcalá de Henares";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="lugar";
$sugestion["texto1"]="San Fernando";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);
*/

$lugares=getTerritoriosSuggestions($_GET["query"],$_GET["idTerritorio"],$_GET["alrededores"]);
foreach($lugares as $lugar)
{
	//print_r($lugar);
	$suggestion["tipo"]="lugar";
//	$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($lugar[1],0,50))));
   $suggestion["texto1"]=htmlentities($lugar["nombre"]);
   if(mb_strlen($lugar["nombre"])>30)
      $suggestion["abrev"]=htmlentities(rtrim(substr($lugar["nombre"],0,27))."...");
	$suggestion["texto2"]="";
   $suggestion["textoBuscado"]=htmlentities($_GET["query"]);//for bold hint string
	$suggestion["id"]=$lugar["id"];
	array_push($suggestions,$suggestion);
   unset($suggestion);
}

if($_GET["tipo"]=="eventos")	//Sólo las mostramos si NO estamos en página de entidades
{
	$entidades=getEntidadesZonaConEventos($_GET["query"],$_GET["idTerritorio"],$_GET["alrededores"],4);
	foreach($entidades as $entidad)
	{
		$suggestion["tipo"]=$entidad["tipo"];
		//$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($entidad["entidad"],0,50))));
        $suggestion["texto1"]=htmlentities($entidad["entidad"]);         
        if(mb_strlen($entidad["entidad"])>30)
           $suggestion["abrev"]=htmlentities(rtrim(substr($entidad["entidad"],0,27))."...");
        $suggestion["texto2"]=htmlentities($entidad["nombre"]);
        $suggestion["textoBuscado"]=htmlentities($_GET["query"]);//for bold hint string
		$suggestion["id"]=$entidad["idEntidad"];
		array_push($suggestions,$suggestion);
      unset($suggestion);
	}
}


$lugaresIrA=getIrA($_GET["query"],$_GET["idTerritorio"]);
if($lugaresIrA) {
	//print_r($lugaresIrA);
	$suggestion["tipo"]="IrA";
//	$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($lugaresIrA["nombre"],0,50))));   
   $suggestion["texto1"]=htmlentities($lugaresIrA["nombre"]);
   $suggestion["textoBuscado"]=htmlentities($_GET["query"]);//for bold hint string
	$suggestion["texto2"]="";
	$suggestion["id"]=$lugaresIrA["id"];
	$suggestion["activo"]=$lugaresIrA["activo"];
	array_push($suggestions,$suggestion);
}

$return["suggestions"]=$suggestions;

echo json_encode($return);


?>