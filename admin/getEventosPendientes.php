<?php
include_once "../db.php";
error_reporting(E_ERROR);

echo json_encode(getEventosPorValidar());

?>