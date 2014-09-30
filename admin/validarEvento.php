<?php
include_once "../db.php";
error_reporting(E_ERROR);

//print_r($_POST);

validarEvento($_POST["idEvento"],$_POST["status"]);

?>