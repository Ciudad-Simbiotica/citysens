<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$sugestions=array();


$lugares=getDireccionesSuggestions($_GET["query"],$_GET["idTerritorio"]);
foreach($lugares as $lugar)
{
	//print_r($lugar);
	$sugestion["tipo"]="lugar";
	$sugestion["texto1"]=substr($lugar[1],0,60);
	$sugestion["texto2"]=substr($lugar[2],0,60);//htmlentities(ucwords(strtolower(substr($lugar[2],0,50))));
	$sugestion["id"]=$lugar[0];
	$sugestion["lat"]=$lugar[3];
	$sugestion["lon"]=$lugar[4];
	$sugestion["zoom"]=$lugar[5];
	array_push($sugestions,$sugestion);
}


$return["suggestions"]=$sugestions;

echo json_encode($return);


?>