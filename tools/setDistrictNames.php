<?php
include "../db.php";

// Little script used to migrate the IDs used for territories (table lugares_shp)

// Script not active unless required
//exit();
ini_set('default_charset', 'utf-8');

$link=connect();
// All districts of cities with just one district, which do not have a name
$sql="SELECT padre.nombre as nombrePadre, padre.idDescendiente idHijo FROM territorios as hijo, territorios as padre where hijo.nivel=9 and hijo.nombre='' and hijo.idPadre=padre.id and padre.idDescendiente<>'2'";

$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
	$nombrePadre=$fila["nombrePadre"];
    $idHijo=$fila["idHijo"];
        
    $sql=utf8_decode("UPDATE territorios SET nombre='$nombrePadre' WHERE id='$idHijo'");
    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
	//print_r();
    
    
}
echo "FIN!!!";
?>