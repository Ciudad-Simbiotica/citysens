<?php

set_time_limit(0);
error_reporting(E_ERROR);
include "../db.php";

ini_set('default_charset', 'utf-8');
$link = connect();
mysqli_query($link, 'SET CHARACTER SET utf8');

// This script imports to the table importacionEntidades the content of an import file
// The archive must have been generated (eg. with OpenOffice Calc) as CSV, coded with UTF-8, and using " for fields and ; as separator

// Script is unactive unless it is required
exit();


//$fileName="NOMBRE_ARCHIVO.csv";
$fileName="data/2015.11.05.iniciativasCivics.Madrid-procesado.v2.csv";

echo "<pre>";
$fo = fopen($fileName, "r"); // CSV file
while (($emapData = fgetcsv($fo, "", ";")) !== FALSE)
{
   $sql = "INSERT INTO importacionEntidades (codImport, idEntImport, nombre, nombreCorto, descBreve, texto, fechaConstitucion, nombreDireccion, direccion, indicacionDireccion, direccionBreve, cp, ciudad, lat, lng, email, telefono, url, facebook, twitter, tipo, clasificacion, created, tipoCTS) VALUES ('$emapData[0]','$emapData[1]','$emapData[2]','$emapData[3]','$emapData[4]','$emapData[4]','$emapData[5]','$emapData[6]','$emapData[7]','$emapData[8]','$emapData[9]','$emapData[10]','$emapData[11]','$emapData[12]','$emapData[13]','$emapData[14]','$emapData[15]','$emapData[16]','$emapData[17]','$emapData[18]','$emapData[19]','$emapData[20]','$emapData[21]','$emapData[22]')";
   echo $sql, PHP_EOL, PHP_EOL;
   $result = mysqli_query($link, $sql);
   if ( false===$result ) {
      printf("Query Failed! %s\n", mysqli_error($link));
   }
   //$result = mysqli_query($link, $sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
}

?>