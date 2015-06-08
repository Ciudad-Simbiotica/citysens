<?php
include "../db.php";
include_once('../vendor/phayes/geophp/geoPHP.inc');
error_reporting(E_ERROR);

$link=connect();
$sql="SELECT distrito.* FROM territorios as distrito, territorios as ciudad where distrito.nivel=9 and distrito.idPadre=ciudad.id and ciudad.idDescendiente<>'0' and ciudad.idDescendiente<>'2' and distrito.activo='0'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	$territorioId=$fila["id"];
	$sql="UPDATE territorios SET activo='1'  WHERE id='$territorioId'";

    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
}


?>