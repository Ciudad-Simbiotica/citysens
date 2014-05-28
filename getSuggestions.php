<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$sugestions=array();

$sugestion["tipo"]="busqueda";
$sugestion["texto1"]="protesta sanciones";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);


$tematicas=getTematicas($_GET["query"],3);
foreach($tematicas as $tematica)
{
	//print_r($asociacion);
	$sugestion["tipo"]="tematica";
	$sugestion["texto1"]=ucfirst(strtolower(substr(preg_replace('/[^(\x20-\x7F)]*/','', $tematica["tematica"]),0,50)));
	$sugestion["texto2"]="";
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
$sugestion["tipo"]="lugar";
$sugestion["texto1"]="El Ensanche";
$sugestion["texto2"]="Alcalá de Henares";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="lugar";
$sugestion["texto1"]="San Fernando";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);

$asociaciones=getAsociaciones($_GET["query"],3);
foreach($asociaciones as $asociacion)
{
	//print_r($asociacion);
	$sugestion["tipo"]="organizacion";
	$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr(preg_replace('/[^(\x20-\x7F)]*/','', $asociacion["asociacion"]),0,50))));
	$sugestion["texto2"]=htmlentities("Distrito ".$asociacion["distrito"]);
	array_push($sugestions,$sugestion);
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