<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

//$suggestions=array();


$ciudades=getCiudadesMadrid($_GET["query"],1000);

echo json_encode($ciudades);


?>