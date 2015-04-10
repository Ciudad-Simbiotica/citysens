<?php
include "../db.php";
error_reporting(E_ERROR);
//Script not active unless required
exit();
$padre=array();
$link=connect();
mysql_query('SET CHARACTER SET utf8', $link);
$sql="SELECT direcciones.idPadre, eventos.idEvento " 
    . "FROM citysens.direcciones, citysens.eventos "
    . "where eventos.idDireccion=direcciones.idDireccion";

$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
   $idPadre = $fila["idPadre"];
   $idEvento = $fila["idEvento"];
   $sql = "UPDATE eventos SET idDistritoPadre='$idPadre' WHERE idEvento='$idEvento'";
    echo $sql.PHP_EOL;
   mysql_query($sql,$link); 
   
	//print_r();
}
//echo $a;
?>