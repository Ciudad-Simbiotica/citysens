<?php
include_once "db.php";
error_reporting(E_ERROR);

$user=createUser($_POST["nombre"],$_POST["email"],$_POST["password"]);

if($user)
{
	//Enviar email creado
	$headers = 'From: CitYsens <soporte@citysens.net>' . "\r\n" .'X-Mailer: PHP/' . phpversion();

	$cadena = "Hola, ".$user["user"].":".PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "¡Bienvenido a CitYsens!".PHP_EOL;
	$cadena.= "Para empezar necesitamos que verifiques tu dirección de correo electrónico. Haz click en el siguiente enlace:".PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= BASE_URL."/verifySignup.php?email=".$user["email"]."&token=".urlencode($user["verificationToken"]).PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "Un saludo,".PHP_EOL;
	$cadena.= "El equipo de CitYsens".PHP_EOL;

	mail($user["email"], "CitYsens: Verifica tu correo electrónico", $cadena, $headers);

}

?>