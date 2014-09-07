<?php
error_reporting(E_ERROR);
include "db.php";
$idEvento=$_GET["id"];
$evento=getEvento($idEvento);

echo json_encode($evento);
?>