<?php
error_reporting(E_ERROR);
include "loadSession.php";
include "preload.php";

if(verifyUser($_GET["email"],$_GET["token"]))
{
	//Verificado
	$_SESSION["notificacion"]="exitoRegistro";
}
else
{
	//No verificado
	$_SESSION["notificacion"]="errorRegistro";
}
header('Location: http://localhost:8888/citysens/?idLugar=888004284');
?>