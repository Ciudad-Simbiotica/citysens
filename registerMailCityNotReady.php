<?php
	error_reporting(E_ERROR);
	include "db.php";
	insertEmailPreregister($_POST["email"],$_POST["idCiudad"]);
?>