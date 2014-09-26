<?php
error_reporting(E_ERROR);
ini_set('default_charset', 'utf-8');
include_once "db.php";

$sugestions=array();


$tematicas=getTematicas($_GET["query"],1000);

echo json_encode($tematicas);


?>