<?php
set_time_limit(0);
include_once "../../db.php";
error_reporting(E_ERROR);

ini_set('default_charset', 'utf-8');
$link=connect();
mysqli_query($link,"SET NAMES 'utf8'");
// script deactivated unless needed
exit();
$fo = fopen('libro10.csv', "r"); // CSV fiile
while (($emapData = fgetcsv($fo, "", ";")) !== FALSE)
{
      $sql = "UPDATE direcciones SET entidad='$emapData[1]' WHERE etiquetas='$emapData[0]'";
echo $sql, PHP_EOL;      
echo "<pre>Debug: $query</pre>\m";
$result = mysqli_query($link, $sql);
if ( false===$result ) {
  printf("error: %s\n", mysqli_error($link));
}
else {
  echo 'done.';
}
//$result = mysqli_query($link, $sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);
}
?>