<?php
error_reporting(E_ERROR);
include "db.php";
$idEntidad=$_GET["id"];
$entidad=getAsociacion($idEntidad);

echo json_encode($entidad);
?>