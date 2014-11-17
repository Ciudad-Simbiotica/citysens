<?php
error_reporting(E_ERROR);
include "settings.php";
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
//TODO: Avoid hard-coding to 888004284
header('Location: '.BASE_URL.'/?idLugar=888004284');
?>