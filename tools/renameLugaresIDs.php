<?php
include "../db.php";

// Little script used to migrate the IDs used for territories (table lugares_shp)

// Script not active unless required
exit();

$link=connect();
$sql="SELECT *  
      FROM lugares_shp";
$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
	$level=$fila["nivel"];
    $province=$fila["provincia"];
    $id=$fila["id"]; // somthing like 88800898

    $newID=$id-111000000*$level+$level*100000000+1000000+$province*10000;
    echo "  /  ID: ".$id." provincia: ".$province." nivel: ".$level." NewID: ".$newID."\t\n";

//	$data=file_get_contents("../shp/geoJSON/".$level."/".$id.".geojson");
//	file_put_contents("../shp/geoJSON/".$level."/".$newID.".geojson",$data);
    
    $read="../shp/geoJSON/".$level."/".$id.".geojson";
    $write="../shp/geoJSON/".$level."/".$newID.".geojson";
    
    $sql=utf8_decode("UPDATE lugares_shp SET id='$newID' WHERE id='$id'");
    echo $sql.PHP_EOL;
    mysql_query($sql,$link);
    //exit();
	//print_r();
    
    
}
?>