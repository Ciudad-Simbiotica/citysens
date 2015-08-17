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
//    tipo: window.listado.tipo
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
//    
////- Nombre del mes
////- Fin de semana
////- Último mes / semana / año ? (para ver pasado)
////- Día específico (decían que no era útil, pero sí nos conviene, como día de comienzo, mostrando a partir del previo).
////- ¿Semana santa / puente mayo / etc.? NO SÉ - Lo mejor sería poder resaltar días festivos, igual que resaltamos el "HOY" y "MAÑANA".
////- 1 mes, 2 meses??
////- 2 semanas, 3 semanas, 4 semanas??
//   
  $months =["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
  $fechas=array();
  $matches=array();
  
  // Checking for dates
  // $date_regex ="/^(\d?\d)[\/\-.](\d?\d)([\/\-.]((\d\d)?\d{2}))?$/";
  $date_regex ="/^(\d?\d)[\/\-.](\d?\d)([\/\-.]((\d\d)?(\d{2})?)?)?$/";
  $period_regex ="/^(\d?) (s(e(m(a(n(a(s)?)?)?)?)?)?|d((i|í)(a(s)?)?)?|m(es(e(s)?)?)?)$/i";

  if (preg_match($date_regex, $cadena, $matches)) {
/*    if ($matches[4]=="") {
       $matches[4]=date('Y');
    } else if (strlen($matches[4])==2) {
       $matches[4]="20".$matches[4];
    } */
    $year=$matches[4];
    if ($year=="") {
       $year=date('Y');
    } else if (strlen($matches[4])==2) {
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
  else if (preg_match($period_regex, $cadena, $matches)){
     switch ($matches[2][0]) {
        case "s":
        case "S":
           $periodo="semanas";
           break;
        case "d":
        case "D":
           $periodo="días";
           break;
        case "m":
        case "M":
           $periodo="meses";
           break;
     }
     $fecha["name"]=$matches[1]." ".$periodo;
     $fecha["start"]="";
     $fecha["end"]="";
     array_push($fechas,$fecha);
  }
  else { // Ckecking for months
     $suggestedMonths=preg_grep("/^{$cadena}/i",$months);
  
     if($suggestedMonths) {
        foreach($suggestedMonths as $index => $month) {
           $year=date('Y');
           $fecha["start"]=$year."-".($index+1)."-1";
           $fecha["end"]=date("Y-m-t", strtotime($fecha["start"]));  //TO DO: Review, seems to be failing.
           $fecha["name"]=$month." ".date('Y');
           array_push($fechas,$fecha);
        }
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