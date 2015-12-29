<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

$nivel=8;
$eventos=array();
$link=connect();
$sql="SELECT * FROM eventos";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$sql="SELECT * FROM places WHERE idPadre='{$fila["idDistritoPadre"]}' ORDER BY RAND() LIMIT 1";
	$result2=mysql_query($sql,$link);
	$fila2=mysql_fetch_assoc($result2);
	$sql="UPDATE eventos SET y='{$fila2["lat"]}',";
	$sql.=					" x='{$fila2["lng"]}',"; 
	$sql.=					" idPlace='{$fila2["idPlace"]}'";
	$sql.=			 "  WHERE idEvento='{$fila["idEvento"]}';";
	echo $sql.PHP_EOL;
}


?>