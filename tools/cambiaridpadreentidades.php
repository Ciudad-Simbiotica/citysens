<?php
include "../db.php";
error_reporting(E_ERROR);
//Script not active unless required
exit();
$padre=array();
$link=connect();
mysql_query('SET CHARACTER SET utf8', $link);
$sql="SELECT direcciones.idPadre, entidades.idEntidad " 
    . "FROM citysens.direcciones, citysens.entidades "
    . "where entidades.idDireccion=direcciones.idDireccion";

$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
   $idPadre = $fila["idPadre"];
   $idEntidad = $fila["idEntidad"];
   $sql = "UPDATE entidades SET idDistritoPadre='$idPadre' WHERE idEntidad='$idEntidad'";
    echo $sql.PHP_EOL;
   mysql_query($sql,$link); 
   
	//print_r();
}
//echo $a;
?>