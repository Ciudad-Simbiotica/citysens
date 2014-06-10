<?php
error_reporting(E_ERROR);
include "db.php";

$childAreas=getChildAreas($_GET["lugarOriginal"],$_GET["nivel"]);
echo json_encode($childAreas);

?>