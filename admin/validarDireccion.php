<?php
include_once "../db.php";
error_reporting(E_ERROR);

//print_r($_POST);

validarDireccion($_POST["idPlace"],$_POST["status"]);

?>