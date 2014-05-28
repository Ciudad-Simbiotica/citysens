<?php
function connect()
{
    $conn = mysql_connect("localhost", "root", "root");
    if (!$conn) {
        echo "Unable to connect to DB: " . mysql_error();
        exit;
    }

    if (!mysql_select_db("citysens")) {
        echo "Unable to select escucho: " . mysql_error();
        exit;
    }
    return $conn;
}

function getAsociaciones($cadena,$cantidad=10)
{
    $link=connect();
    $sql="SELECT * 
            FROM  asociaciones 
            WHERE asociacion LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

function getTematicas($cadena,$cantidad=10)
{
    $link=connect();
    $sql="SELECT * 
            FROM  tematicas 
            WHERE tematica LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

function getEventos($cadena,$cantidad=50)
{
    $link=connect();
    $sql="SELECT * 
            FROM  eventos 
            WHERE titulo LIKE '%$cadena%'
            ORDER BY fecha ASC
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

?>