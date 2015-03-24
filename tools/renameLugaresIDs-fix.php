<?php
include "../db.php";
// Script to fix a mistake done while migrating ID names for territories.
// It included creating an extra table with old data, to be able to recover lost information. 
// It is temporarily kept in repository for educational purposes.

//Script not active unless required
exit();

$link=connect();
$sql="SELECT distrito.id as distritoid, distrito.nombre as distritonombre, ciudad.ine as ciudadine, ciudad.id as ciudadid, ciudad.nombre as ciudadnombre "
    . "FROM lugares_shp2 as distrito, lugares_shp2 as ciudad "
    . "WHERE distrito.nivel=9 AND distrito.idPadre=ciudad.id";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$distritoViejoId=$fila["distritoid"];
    $distritoNombre=$fila["distritonombre"];
    $ciudadId=$fila["ciudadid"];
    $ciudadNombre=$fila["ciudadnombre"];
    $ciudadIne=$fila["ciudadine"];
    
    $distritoNuevoId=$distritoViejoId-999000000+900000000+1000000+280000;
    
    $sql="SELECT id as ciudadnuevoid "
    . "FROM lugares_shp "
    . "WHERE ine=$ciudadIne";
    
    $result2=mysql_query($sql,$link);
    $fila2=mysql_fetch_assoc($result2);
    $ciudadNuevoId=$fila2["ciudadnuevoid"];
    
    $sql="UPDATE lugares_shp SET idPadre='$ciudadNuevoId' WHERE id='$distritoNuevoId'";
    echo $sql.PHP_EOL;
    mysql_query($sql,$link);
    
    //exit();
	//print_r();
}
?>