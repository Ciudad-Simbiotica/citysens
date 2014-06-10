<?php
function connect()
{
    $conn = mysql_connect("localhost", "root", "root");
    if (!$conn) {
        echo "Unable to connect to DB: " . mysql_error();
        exit;
    }

    if (!mysql_select_db("citysens")) {
        echo "Unable to select citysens: " . mysql_error();
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

function getDatosLugar($idLugar)
{
    $link=connect();
    $sql="SELECT * 
            FROM  lugares_shp 
            WHERE id='$idLugar'";
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $fila=mysql_fetch_assoc($result);
    return $fila;
}

function getChildAreas($lugarOriginal,$nivel)
{
    $link=connect();
    $sql="SELECT lugares_shp.*,count(eventos.idDistritoPadre) as cantidad
            FROM lugares_shp LEFT OUTER JOIN eventos 
            ON lugares_shp.id=eventos.idDistritoPadre 
            WHERE nivel='$nivel'
            AND idPadre='$lugarOriginal'
            GROUP BY lugares_shp.id";



    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT),$fila["cantidad"]));
    return $returnData;
}

function getColindantes($lugarOriginal,$type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    $sql="SELECT * FROM lugares_shp WHERE 
            nivel='$type' AND
            provincia=28 AND
            NOT(xmin > $xmax 
            OR $xmin >  xmax
            OR  ymax < $ymin 
            OR $ymax < ymin)";
            
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        if($fila["id"]!=$lugarOriginal)
            array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["geocodigo"]));
    return $returnData;
}

function getEventosCoordenadas($xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    $sql="SELECT * FROM eventos WHERE 
            x>$xmin AND x<$xmax AND y>$ymin AND y<$ymax";
            
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}


?>