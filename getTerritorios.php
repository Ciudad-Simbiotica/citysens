<?php
include_once "db.php";
error_reporting(0);

$territorios=getTerritorios($_GET["territorios"]);
echo json_encode($territorios);
  
?>