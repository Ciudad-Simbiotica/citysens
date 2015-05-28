<?php

set_time_limit(0);
include_once "../db.php";

// Script is unactive unless it is required
exit();

 $link=connect();

$sql="SELECT id FROM territorios WHERE id like '80128%'";       
$result=mysqli_query($link, $sql);

while($fila=mysqli_fetch_assoc($result))
{
    $idLugar=$fila['id'];
    //Actualiza idDescendiente con el campo adecuado   
     $idDescendiente=getFertility($idLugar);
     
     $sql="UPDATE territorios SET 
	  idDescendiente='$idDescendiente' 
	  WHERE id='$idLugar'";
	 echo $sql.PHP_EOL;;
	 mysqli_query($link, $sql);
         
} 

?>
