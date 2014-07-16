<?php
error_reporting(E_ERROR);
include_once "db.php";
$query=json_decode($_GET["query"],true);
$eventos=getAsociacionesQuery($query,50);
foreach($eventos as $evento)
{

}
?>