<?php

set_time_limit(0);
include_once "../db.php";

// Script is unactive unless it is required
exit();

 $link=connect();

// Para poner idCiudad a las entidades que tienen una sola direccion
//$sql="SELECT idEntidad, idCiudad FROM entidades, places WHERE entidades.idPlace=places.idPlace";       
//$result=mysqli_query($link, $sql);
//
//while($fila=mysqli_fetch_assoc($result))
//{
//    $idEntidad=$fila['idEntidad'];
//    $idCiudad=$fila['idCiudad'];
//
//     $sql="UPDATE entidades SET 
//	  idCiudad='$idCiudad' 
//	  WHERE idEntidad='$idEntidad'";
//	 echo $sql.PHP_EOL;;
//	 mysqli_query($link, $sql);
//         
//} 
 
// Fusionar prefijo y teléfono. Eliminar espacios en teléfonos.
$sql="SELECT * FROM entidades";       
$result=mysqli_query($link, $sql);

while($fila=mysqli_fetch_assoc($result))
{
    $idEntidad=$fila['idEntidad'];
    $prefijo=$fila['prefijo'];
    $telefono=$fila['telefono'];
    
    $nuevo_telefono= str_replace(' ', '', $prefijo.$telefono);
    
    if ($nuevo_telefono!='') {
       $sql="UPDATE entidades SET     
             telefono='$nuevo_telefono' 
   	       WHERE idEntidad='$idEntidad'";
       echo $sql.PHP_EOL;;
       mysqli_query($link, $sql);
    }      
} 


?>
