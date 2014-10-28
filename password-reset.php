<?php
include_once "db.php";
error_reporting(E_ERROR);
$verificationToken=resetUser($_POST["email"]);
if($verificationToken)
{
	//Enviar email creado
	$headers = 'From: CitYsens <soporte@citysens.net>' . "\r\n" .'X-Mailer: PHP/' . phpversion();

	$cadena = "Hola:".PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "Te enviamos este correo porque nos has dicho que has olvidado tu contraseña.Haz click en el siguiente enlace para cambiarla:".PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "http://localhost:8888/resetPassword.php?email=".$_POST["email"]."&token=".urlencode($verificationToken).PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "Un saludo,".PHP_EOL;
	$cadena.= "El equipo de CitYsens".PHP_EOL;
	$cadena.= PHP_EOL;
	$cadena.= "(Si no has sido tú quien lo ha solicitado no tienes que hacer nada, este enlace caducará en 24 horas)".PHP_EOL;

	mail($_POST["email"], "CitYsens: Contraseña olvidada", $cadena, $headers);
	echo "OK";
}
else
	echo "KO";
?>