<?php
error_reporting(E_ERROR);
include "db.php";
$idEvento=$_GET["id"];
$evento=getEvento($idEvento);

/*
$evento['idLugar']='99';
$evento['titulo']='a';
$evento['texto']='a';
$evento['lugar']='a';
*/

//print_r($evento);


echo json_encode($evento);
?>