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
    //mysql_query('SET CHARACTER SET utf8',$conn);
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

function getAsociacionesZonaConEventos($cadena,$cantidad=10)
{
    $link=connect();
    $sql="SELECT * 
            FROM asociaciones JOIN eventos 
            ON asociaciones.idAsociacion=eventos.idAsociacion
            WHERE asociacion LIKE '%$cadena%'
            GROUP BY asociaciones.idAsociacion
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

function getAsociacionesQuery($query,$cantidad=10)
{
    $link=connect();
    $busqueda="";
    $tematicas=array();
    $lugar="";
    $lugares=array();
    foreach($query as $tag)
    {
        $tipo=$tag["tipo"];
        $texto=$tag["texto"];
        $id=$tag["id"];
        switch($tipo)
        {
            case "busqueda":
                if($busqueda!="")
                    $busqueda.=" OR ";
                $busqueda.="asociacion LIKE '%$texto%'";
                break;
            case "tematica":
                if($tematica!="")
                    $tematica.=" OR ";
                $tematica.="idTematica='$id'";
                break;
            case "lugar":
                array_push($lugares,$id);
                break;
        }
    }


    $lugares=getAllChildren($lugares);
    foreach($lugares as $idLugar)
    {
        if($lugar!="")
            $lugar.=" OR ";
        $lugar.="idDistritoPadre='$idLugar'";
    }


    $sql="SELECT * FROM asociaciones JOIN asociaciones_tematicas ON asociaciones.idAsociacion=asociaciones_tematicas.idAsociacion WHERE ";
    if($busqueda!="")
        $sql.="($busqueda) AND ";
    if($tematica!="")
        $sql.="($tematica) AND ";
    if($lugar!="")
        $sql.="($lugar) AND ";
    $sql.="1 GROUP BY asociaciones.idAsociacion ORDER BY points DESC LIMIT 0,$cantidad";

    echo $sql;

    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;


    return;


    //id/clase=organizaciones/tipo/tituloOrg/textoOrg/lugarOrg/puntos

    $link=connect();
    $sql="SELECT * 
            FROM asociaciones JOIN eventos 
            ON asociaciones.idAsociacion=eventos.idAsociacion
            WHERE asociacion LIKE '%$cadena%'
            GROUP BY asociaciones.idAsociacion
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
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

function getEvento($idEvento)
{
    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM eventos WHERE idEvento='$idEvento'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $evento=$fila;
        //mysql_query('SET CHARACTER SET utf8',$link);
        $sql="SELECT * FROM eventos_tematicas, tematicas 
                WHERE eventos_tematicas.idEvento='$idEvento' AND
                eventos_tematicas.idTematica=tematicas.idTematica";
        $result=mysql_query($sql,$link);
        while($fila=mysql_fetch_assoc($result))
        {
            $evento['tematicas'][$fila['idTematica']]=ucfirst(strtolower($fila['tematica']));
        }
        return $evento;
    }
    else
    {
        return false;
    }

}

function getEventos($query,$cantidad=50)
{
    $link=connect();
    $busqueda="";
    $tematica="";
    $lugar="";
    $lugares=array();
    $organizacio="";
    foreach($query as $tag)
    {
        $tipo=$tag["tipo"];
        $texto=$tag["texto"];
        $id=$tag["id"];
        switch($tipo)
        {
            case "busqueda":
                if($busqueda!="")
                    $busqueda.=" OR ";
                $busqueda.="titulo LIKE '%$texto%'";
                break;
            case "tematica":
                if($tematica!="")
                    $tematica.=" OR ";
                $tematica.="idTematica='$id'";
                break;
            case "lugar":
                array_push($lugares,$id);
                break;
            case "organizacion":
            case "institucion":
            case "colectivo":
                if($organizacion!="")
                    $organizacion.=" OR ";
                $organizacion.="idAsociacion='$id'";
                break;                
        }
    }


    $lugares=getAllChildren($lugares);
    foreach($lugares as $idLugar)
    {
        if($lugar!="")
            $lugar.=" OR ";
        $lugar.="idDistritoPadre='$idLugar'";
    }


    $sql="SELECT * FROM  eventos WHERE ";
    if($busqueda!="")
        $sql.="($busqueda) AND ";
    if($tematica!="")
        $sql.="($tematica) AND ";
    if($lugar!="")
        $sql.="($lugar) AND ";
    if($organizacion!="")
         $sql.="($organizacion) AND ";
    $sql.="1 ORDER BY fecha ASC LIMIT 0,$cantidad";


    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

function getAllChildren($lugares)
{
    $link=connect();
    for($nivel=6;$nivel<=10;$nivel++)
    {
        $ids=implode(",",$lugares);
        $sql="SELECT id FROM lugares_shp WHERE nivel='$nivel' AND idPadre IN ($ids)";
        $result=mysql_query($sql,$link);
        while($fila=mysql_fetch_assoc($result))
            array_push($lugares,$fila['id']);
    }
    return(array_unique($lugares));

}

function getAllAncestors($idLugar)
{
    $lugar=getDatosLugar($idLugar);
    $lugares[$lugar["nivel"]]=$lugar;
    $idPadre=$lugar["idPadre"];
    while($idPadre!=0)
    {
        $lugar=getDatosLugar($idPadre);
        $lugares[$lugar["nivel"]]=$lugar;
        $idPadre=$lugar["idPadre"];
    }
    
    /*
    //Simulando Comunidad de Madrid
    $lugares[4]["nombre"]="Comunidad de Madrid";
    $lugares[4]["nombreCorto"]="CM";
    $lugares[4]["id"]="444000028";
    */

    //Simulando España
    $lugares[2]["nombre"]="España";
    $lugares[2]["nombreCorto"]="ES";
    $lugares[2]["id"]="222000034";

    return $lugares;
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

function getLugares($cadena,$lugarOriginal,$type,$cantidad=3,$inSet=array())
{
    $link=connect();
    $sql="SELECT * FROM lugares_shp WHERE 
            nivel='$type' AND
            provincia=28 AND
            nombre LIKE '%$cadena%' AND
            id<>'$lugarOriginal'";
    if(count($inSet)>0)
        $sql.="";
    $sql.="LIMIT 0,$cantidad";
            
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["geocodigo"]));
    return $returnData;

}

function getLugaresSuggestions($cadena,$lugarOriginal,$cantidad=5)
{
    //echo $lugarOriginal;
    $inSet=getAllChildren(array($lugarOriginal));
    $link=connect();
    unset($inSet[0]);   //Quitamos el original
    $sql="SELECT * FROM lugares_shp WHERE 
            nombre LIKE '%$cadena%' AND
            id IN (".implode(",",$inSet).") 
            LIMIT 0,$cantidad";
    //echo $sql;        
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["geocodigo"]));
    return $returnData;

}

function getDireccionesSuggestions($cadena,$lugarOriginal,$cantidad=5)
{
    //echo $lugarOriginal;
    $inSet=getAllChildren(array($lugarOriginal));
    $link=connect();
    //unset($inSet[0]);   //Quitamos el original
    $sql="SELECT * FROM direcciones WHERE 
            (nombre LIKE '%$cadena%' OR direccion LIKE '%$cadena%') AND
            idPadre IN (".implode(",",$inSet).") 
            LIMIT 0,$cantidad";
    //echo $sql;        
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,array($fila["idDireccion"],$fila["nombre"],$fila["direccion"],$fila["lat"],$fila["long"],$fila["zoom"]));
    return $returnData;

}


function getColindantes($lugarOriginal,$type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    $sql="SELECT * FROM lugares_shp WHERE 
            nivel='$type' AND
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

function getLevels($provincia,$type)
{
    $link=connect();
    $sql="SELECT * FROM lugares_shp WHERE 
            nivel='$type' AND
            provincia='$provincia'";
            
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        if($fila["id"]!=$lugarOriginal)
            array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["geocodigo"],$fila["idPadre"]));
    return $returnData;
}


?>