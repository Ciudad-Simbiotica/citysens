<?php

include "../db.php";
error_reporting(E_ERROR);
set_time_limit(0);
ini_set('default_charset', 'utf-8');
mysql_query("SET NAMES 'utf8'");
// Disabled unless needed
exit();

// assign idEntidad to etiquetas for mark duplicates entities

$seleccion = 554;//entidades viejas de Alcalá
$entidades=array();
$idAsignada=array();
$link = connect();
$sql = "SELECT * FROM entidades WHERE idEntidad<'$seleccion'";
$result = mysqli_query($link, $sql);
while ($fila = mysqli_fetch_assoc($result)) {
    array_push($entidades, $fila);
    $nombreEntidad=$fila['entidad'];
    if (!$idAsignada[$nombreEntidad]) {
        $idAsignada[$nombreEntidad] = $fila['idEntidad'];
    }   
}

foreach ($entidades as $entidad) {
    $nombreEntidad=$entidad['entidad'];
    $idParaAsignar=$idAsignada[$nombreEntidad];
    $sql="update entidades SET etiquetas='".$idParaAsignar."' where idEntidad='".$entidad['idEntidad']."'";
    echo $sql;
    mysqli_query($link, $sql);
}
//Mala Praxis. Puede sobreescribir entidades fuera de la seleccion original hecha en la tabla, si tuviese el mismo nombre
/*
foreach ($idAsignada as $nombre => $idEntidad) {
    $sql="update entidades SET etiquetas=".$idEntidad." where entidad='".$nombre."'";
    mysqli_query($link, $sql);
}
*/
    echo "HECHO!";
?>