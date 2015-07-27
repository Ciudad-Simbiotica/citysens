<?php
include "../db.php";
error_reporting(E_ERROR);

exit();

$link=connect();
$sql="SELECT * FROM territorios where nombre like '% El'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
    $id=$fila["id"];
    $nombre=$fila["nombre"];
    $posicion = strpos($fila["nombre"], " El");
    
    $nombreCorregido = "El ".substr($nombre, 0, $posicion);
    
	$sql="UPDATE territorios SET nombre='$nombreCorregido' WHERE id='$id'";

    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
}
$sql="SELECT * FROM territorios where nombre like '% Los'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
    $id=$fila["id"];
    $nombre=$fila["nombre"];
    $posicion = strpos($fila["nombre"], " Los");
    
    $nombreCorregido = "Los ".substr($nombre, 0, $posicion);
    
	$sql="UPDATE territorios SET nombre='$nombreCorregido' WHERE id='$id'";

    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
}
$sql="SELECT * FROM territorios where nombre like '% La'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
    $id=$fila["id"];
    $nombre=$fila["nombre"];
    $posicion = strpos($fila["nombre"], " La");
    $longitud = strlen($fila["nombre"]);
    $nuevaLongitud = $longitud-$posicion;
    
    $nombreCorregido = "La ".substr($nombre, 0, $posicion);
    
	$sql="UPDATE territorios SET nombre='$nombreCorregido' WHERE id='$id'";

    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
}
$sql="SELECT * FROM territorios where nombre like '% Las'";
$result=mysqli_query($link,$sql);
while($fila=mysqli_fetch_assoc($result))
{
    $id=$fila["id"];
    $nombre=$fila["nombre"];
    $posicion = strpos($fila["nombre"], " Las");
    $longitud = strlen($fila["nombre"]);
    $nuevaLongitud = $longitud-$posicion;
    
    $nombreCorregido = "Las ".substr($nombre, 0, $posicion);
    
	$sql="UPDATE territorios SET nombre='$nombreCorregido' WHERE id='$id'";

    echo $sql.PHP_EOL;
    mysqli_query($link,$sql);
}


?>