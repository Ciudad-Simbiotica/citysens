<?php

set_time_limit(0);
error_reporting(E_ERROR);
include "../db.php";

ini_set('default_charset', 'utf-8');
$link = connect();
mysqli_query($link, 'SET CHARACTER SET utf8');

// This script imports to the table importacionEntidades the content of an import file
// Script is unactive unless it is required
exit();


//$fileName="NOMBRE_ARCHIVO.csv";
$fileName="data/asociacionesGuada.csv";


$fo = fopen($fileName, "r"); // CSV file
while (($emapData = fgetcsv($fo, "", ";")) !== FALSE)
{
   $sql = "INSERT INTO importacionEntidades (codImport, idEntImport, nombre, nombreCorto, fechaConstitucion, nombreDireccion, detalleDireccion, direccion, direccionBreve, cp, ciudad, email, telefono, url, facebook, twitter, clasificacion, tipo, descBreve) VALUES ('$emapData[0]','$emapData[1]','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[5]','$emapData[6]','$emapData[7]','$emapData[8]','$emapData[9]','$emapData[10]','$emapData[11]','$emapData[12]','$emapData[13]','$emapData[14]','$emapData[15]','$emapData[16]','$emapData[17]','$emapData[18]')";
   echo $sql, PHP_EOL; 
   $result = mysqli_query($link, $sql);
   if ( false===$result ) {
      printf("Query Failed! %s\n", mysqli_error($link));
   }
   //$result = mysqli_query($link, $sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
}

?>