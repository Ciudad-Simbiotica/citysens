<?php
include "../db.php";
set_time_limit(0);
error_reporting(E_ALL);

$lugares=json_decode(file_get_contents("Provincias/522175-covers-madrid.json"),true);

$link=connect();

$i=0;
foreach($lugares as $id=>$lugar)
{
	$i++;
	echo $i."/".count($lugares)."<BR>";flush();
	$data=json_decode(file_get_contents("Madrid/$id.json"),true);
	$type=intval(substr($data["type"],2,1));
	$name=utf8_decode($data["name"]);
	$sql="INSERT INTO lugares VALUES ('{$data["id"]}','$name','$type')";	
	mysql_query($sql,$link);
}

?>