<?php
	error_reporting(0);
	include_once "db.php";
	$respuesta=getDatosLugar($_GET["idLugar"]);
	echo json_encode($respuesta);
?>