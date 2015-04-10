<?php
include "../db.php";
// Script to fix a mistake done while build regions.
// It included creating an extra table with old data, to be able to recover lost information. 
// It is temporarily kept in repository for educational purposes.

//Script not active unless required
exit();
$a=0;
$link=connect();
mysql_query('SET CHARACTER SET utf8', $link);
$sql="SELECT region.id "
     ."from citysens.lugares_shp as region "
     ."where region.nivel=7 "
     ."and not exists(select * from citysens.lugares_shp as municipio "
			."where region.id=municipio.idPadre)";

$result=mysql_query($sql,$link);
while($fila=mysql_fetch_assoc($result))
{
    $regionvacia=$fila["id"];
   $sql="DELETE FROM citysens.lugares_shp "
   . "where id='$regionvacia'"; 
    echo $sql.PHP_EOL;
    mysql_query($sql,$link); 
    //exit();
	//print_r();
}
//echo $a;
?>