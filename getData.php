<?php

error_reporting(E_ERROR);
include "loadSession.php";
include "preload.php";

$lorem="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
date_default_timezone_set("Europe/Madrid");
setlocale(LC_ALL,'es_ES.utf8','es_ES','es');



if(!in_array($_GET["clase"], array("eventos","procesos","organizaciones")))
	exit;


if($_GET["clase"]=="eventos")
{
	include "getEventos.php";
}
else if($_GET["clase"]=="organizaciones")
{
	include "getEntidades.php";
}

//Añadimos si el user está siguiendo (si estamos logueados)
if($_SESSION["user"])
{
	$returnData["isFollowing"]=isFollowing($_SESSION["user"]["idUser"],$_GET["query"],$_GET["clase"]);
}
else
{
	$returnData["isFollowing"]=false;	
}

//Añadimos los datos del lugar original
$returnData["lugarOriginal"]=getDatosLugar($_GET["idTerritorioOriginal"]);

$returnJSON=json_encode($returnData);

echo $returnJSON;


exit();

?>
