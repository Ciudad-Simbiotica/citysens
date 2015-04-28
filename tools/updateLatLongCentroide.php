<?php
include "../db.php";
//Script not active unless required
exit();
$link=connect();
$sql="UPDATE lugares_shp SET xcentroid='{$_GET["xcentroid"]}', ycentroid='{$_GET["ycentroid"]}' WHERE id='{$_GET["id"]}'";
mysqli_query($link,$sql);

?>