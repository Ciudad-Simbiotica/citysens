<?php
ini_set('default_charset', 'utf-8');

$data=json_decode(file_get_contents("returnCache_eventos.txt"),true);

foreach($data["grupos"] as $fecha=>$grupo)
{
	//echo $fecha.PHP_EOL;
	foreach($grupo["filas"] as $fila)
	{
		$hora=$fecha." ".$fila["hora"].":00";
		$sql="INSERT INTO eventos VALUES ('{$fila["id"]}','".$hora."','{$fila["clase"]}','{$fila["tipo"]}','{$fila["titulo"]}','{$fila["texto"]}','{$fila["lugar"]}','{$fila["temperatura"]}');";
		echo $sql.PHP_EOL;
	}
}

?>