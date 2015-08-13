<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$suggestions=array();


$lugares=getDireccionesSuggestions($_GET["query"],$_GET["idTerritorio"]);
foreach($lugares as $lugar)
{
	//print_r($lugar);
	$suggestion["tipo"]="lugar";
	$suggestion["texto1"]=substr($lugar[1],0,60);
	$suggestion["texto2"]=substr($lugar[2],0,60);//htmlentities(ucwords(strtolower(substr($lugar[2],0,50))));
	$suggestion["id"]=$lugar[0];
	$suggestion["lat"]=$lugar[3];
	$suggestion["lon"]=$lugar[4];
	$suggestion["zoom"]=$lugar[5];
	array_push($suggestions,$suggestion);
}


$return["suggestions"]=$suggestions;

echo json_encode($return);


?>