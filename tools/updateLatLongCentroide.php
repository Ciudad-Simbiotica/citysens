<?php
include "db.php";

$link=connect();
$sql="UPDATE lugares_shp SET xcentroid='{$_GET["xcentroid"]}', ycentroid='{$_GET["ycentroid"]}' WHERE id='{$_GET["id"]}'";
mysql_query($sql,$link);

?>