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

$tematicas=getTematicas($_GET["query"],4);
foreach($tematicas as $tematica)
{
	//print_r($entidad);
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


$lugares=getLugaresSuggestions($_GET["query"],$_GET["idTerritorio"]);
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
	$entidades=getEntidadesZonaConEventos($_GET["query"],4,$_GET["idTerritorio"]);
	foreach($entidades as $entidad)
	{
		//print_r($entidad);
		$sugestion["tipo"]=$entidad["tipoEntidad"];
		$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($entidad["entidad"],0,50))));
		$sugestion["texto2"]=htmlentities("Distrito ".$entidad["distrito"]);
		$sugestion["id"]=$entidad["idEntidad"];
		array_push($sugestions,$sugestion);
	}
}


$lugaresIrA=getIrA($_GET["query"],$_GET["idTerritorio"]);
if($lugaresIrA)
{
	//print_r($lugaresIrA);
	$sugestion["tipo"]="IrA";
	$sugestion["texto1"]=htmlentities(ucwords(strtolower(substr($lugaresIrA["nombre"],0,50))));
	$sugestion["texto2"]="";
	$sugestion["id"]=$lugaresIrA["id"];
	$sugestion["activo"]=$lugaresIrA["activo"];
	array_push($sugestions,$sugestion);
}



$return["suggestions"]=$sugestions;

echo json_encode($return);


?>