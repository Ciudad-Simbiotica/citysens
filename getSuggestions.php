<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$sugestions=array();


//ToDo: Sugerencia de búsquedas comunes
/*
$sugestion["tipo"]="busqueda";
$sugestion["texto1"]="protesta sanciones";
$sugestion["texto2"]="";
$sugestion["id"]=0;
array_push($sugestions,$sugestion);
*/

$tematicas=getTematicas($_GET["query"],3);
foreach($tematicas as $tematica)
{
	//print_r($asociacion);
	$sugestion["tipo"]="tematica";
	$sugestion["texto1"]=substr($tematica["tematica"],0,50);
	$sugestion["texto2"]="";
	$sugestion["id"]=$tematica["idTematica"];
	array_push($sugestions,$sugestion);
}

/*
print_r($tematicas);
print_r($sugestions);
*/


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


$lugares=getLugaresSuggestions($_GET["query"],$_GET["idLugar"]);
foreach($lugares as $lugar)
{
	//print_r($lugar);
	$sugestion["tipo"]="lugar";
	$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($lugar[1],0,50))));
	$sugestion["texto2"]="";//htmlentities("Distrito ".$lugar["nombre"]);
	$sugestion["id"]=$lugar[0];
	array_push($sugestions,$sugestion);
}


if($_GET["entidades"]=="")	//Sólo las mostramos si NO estamos buscando entidades
{
	$asociaciones=getAsociacionesZonaConEventos($_GET["query"],3,true);
	foreach($asociaciones as $asociacion)
	{
		//print_r($asociacion);
		$sugestion["tipo"]=$asociacion["tipoAsociacion"];
		$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($asociacion["asociacion"],0,50))));
		$sugestion["texto2"]=htmlentities("Distrito ".$asociacion["distrito"]);
		$sugestion["id"]=$asociacion["idAsociacion"];
		array_push($sugestions,$sugestion);
	}
}
/*
$sugestion["tipo"]="organizacion";
$sugestion["texto1"]="Club de Atletismo San Fernando";
$sugestion["texto2"]="San Fernando";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="institucion";
$sugestion["texto1"]="Ayuntamiento de Los Santos";
$sugestion["texto2"]="Los Santos";
array_push($sugestions,$sugestion);
*/
/*
$returnSuggestions=array();


//$palabras=split(" ",$_GET["query"]);
//foreach($palabras as $palabra)
//{
	foreach($sugestions as $sugestion)
	{
		if(preg_match('/'.$_GET["query"].'/i', $sugestion["texto1"]))
		{
			array_push($returnSuggestions, $sugestion);
		}
	}	
//}
*/

$return["suggestions"]=$sugestions;

echo json_encode($return);


?>