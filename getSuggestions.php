<?php
$sugestions=array();

$sugestion["tipo"]="busqueda";
$sugestion["texto1"]="protesta sanciones";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="sanidad";
$sugestion["texto1"]="Sanidad";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="lugar";
$sugestion["texto1"]="El Ensanche";
$sugestion["texto2"]="Alcalá de Henares";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="lugar";
$sugestion["texto1"]="San Fernando";
$sugestion["texto2"]="";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="organizacion";
$sugestion["texto1"]="Club de Atletismo San Fernando";
$sugestion["texto2"]="San Fernando";
array_push($sugestions,$sugestion);

$sugestion["tipo"]="institucion";
$sugestion["texto1"]="Ayuntamiento de Los Santos";
$sugestion["texto2"]="Los Santos";
array_push($sugestions,$sugestion);

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

$return["suggestions"]=$returnSuggestions;


echo json_encode($return);


?>