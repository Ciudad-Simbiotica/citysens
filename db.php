<?php
include "settings.php";
include_once "passwordHashing.php";

function connect()
{
    $conn = mysql_connect(DB_DOMAIN, DB_USERNAME, DB_PASSWORD);
    if (!$conn) {
        echo "Unable to connect to DB: " . mysql_error();
        exit;
    }

    if (!mysql_select_db(DB_DB)) {
        echo "Unable to select citysens: " . mysql_error();
        exit;
    }
    //mysql_query('SET CHARACTER SET utf8',$conn);
    return $conn;
}

//USERS
function createUser($user,$email,$pass)
{
    $user=safe($user);
    $email=safe(filter_var($email,FILTER_SANITIZE_EMAIL));
    $pass=safe($pass);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM users WHERE email='$email' OR user='$user'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
        return false;

    $hash=create_hash($pass);
    $verificationToken=base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));

    $sql="INSERT INTO users (user,email,hash,verified,verificationToken) VALUES ('$user','$email','$hash','0','$verificationToken')";
    mysql_query($sql,$link);

    //ToDo: Comprobar que se crea bien el usuario

    $createdUser["user"]=$user;
    $createdUser["email"]=$email;
    $createdUser["verified"]=0;
    $createdUser["verificationToken"]=$verificationToken;


    return $createdUser;
}

function verifyUser($email,$token)
{
    $email=safe(filter_var($email,FILTER_SANITIZE_EMAIL));
    $token=safe($token);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM users WHERE email='$email' AND verificationToken='$token'";
    $result=mysql_query($sql,$link);
    //echo $sql;
    
    if($fila=mysql_fetch_assoc($result))
    {
        $sql="UPDATE users SET verified=1, verificationToken='' WHERE idUser='{$fila["idUser"]}'";
        //echo $sql;
        mysql_query($sql,$link);
        return true;
    }
    else
        return false;

}

function getUser($email,$pass)
{
    //Sanitize inputs
    $email=safe(filter_var($email,FILTER_SANITIZE_EMAIL));
    $pass=safe($pass);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM users WHERE email='$email'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        if(validate_password($pass,$fila["hash"]))
        {
            $fila["hash"]="";
            return $fila;
        }
        else
        {
            return false;
        }
    }
    else
    {
        return false;
    }
    
}

function resetUser($email)
{
    $email=safe($email);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM users WHERE email='$email'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $verificationToken=base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
        $sql="UPDATE users SET verificationToken='$verificationToken' WHERE idUser='{$fila["idUser"]}'";
        mysql_query($sql,$link);
        return $verificationToken;
    }
    return false;
}

function changeUserPassword($email,$token,$nuevoPassword)
{
    $email=safe($email);
    $token=safe($token);
    $nuevoPassword=safe($nuevoPassword);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM users WHERE email='$email' AND verificationToken='$token'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $hash=create_hash($nuevoPassword);
        $sql="UPDATE users SET hash='$hash', verificationToken='', verified='1' WHERE idUser='{$fila["idUser"]}'";
        mysql_query($sql,$link);
        return true;
    }
    return false;
}


//Seguimiento listados

function follow($idUser,$query,$clase)
{
    $idUser=safe($idUser);
    $query=safe($query);
    $clase=safe($clase);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="INSERT INTO avisosListados (idUser, query,clase) VALUES ('$idUser','$query','$clase')";
    mysql_query($sql,$link);
}

function unfollow($idUser,$query,$clase)
{
    $idUser=safe($idUser);
    $query=safe($query);
    $clase=safe($clase);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="DELETE FROM avisosListados WHERE idUser='$idUser' AND query='$query' AND clase='$clase'";
    mysql_query($sql,$link);    
}

function isFollowing($idUser,$query,$clase)
{
    $idUser=safe($idUser);
    $query=safe($query);
    $clase=safe($clase);

    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT idAvisoListado FROM avisosListados WHERE idUser='$idUser' AND query='$query' AND clase='$clase'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
        return true;
    else
        return false;
}

//Entidades
function getEntidad($idEntidad)
{
    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM entidades WHERE idEntidad='$idEntidad'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $entidad=$fila;
        
        
        $sql="SELECT * FROM direcciones WHERE idDireccion='{$fila['idDireccion']}'";
        $result=mysql_query($sql,$link);
        if($fila=mysql_fetch_assoc($result))
        {
            $entidad['direccion']=$fila;
        }
        else
        {
            $entidad['direccion']['direccion']="Sin dirección";
            $entidad['direccion']['idDireccion']="0";
            $entidad['direccion']['idPadre']="0";
            $entidad['direccion']['lat']=0;
            $entidad['direccion']['lng']=0;
            $entidad['direccion']['nombre']="Sin nombre";
            $entidad['direccion']['zoom']="15";
        }
        

        //$entidad['direccion']="Distrito ".($entidad["idDistritoPadre"]-999000004);
        $sql="SELECT * FROM entidades_tematicas, tematicas 
                WHERE entidades_tematicas.identidad='$idEntidad' AND
                entidades_tematicas.idTematica=tematicas.idTematica";
        $result=mysql_query($sql,$link);
        while($fila=mysql_fetch_assoc($result))
        {
            $entidad['tematicas'][$fila['idTematica']]=ucfirst(strtolower($fila['tematica']));
        }

        return $entidad;
    }
    else
    {
        return false;
    }

}

function getEntidades($cadena,$cantidad=10)
{
    $link=connect();
    $sql="SELECT * 
            FROM  entidades 
            WHERE entidad LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

function getEntidadesZonaConEventos($cadena,$cantidad=10,$idDistritoPadre=0)
{
    $link=connect();
    $sql="SELECT * 
            FROM entidades JOIN eventos 
            ON entidades.idEntidad=eventos.idEntidad
            WHERE entidad LIKE '%$cadena%'";

    if($idDistritoPadre!=0)
    {
        $hijos=getAllChildren(array($idDistritoPadre));
        $sql.=" AND eventos.idDistritoPadre IN ('".join($hijos,"','")."')";
    }

    $sql.=" GROUP BY entidades.idEntidad
            LIMIT 0,$cantidad";

    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

function getEntidadesQuery($query,$cantidad=10)
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
                $busqueda.="entidad LIKE '%$texto%'";
                break;
            case "tematica":
                if($tematica!="")
                    $tematica.=" OR ";
                $tematica.="entidades_tematicas.idTematica='$id'";
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
        $lugar.="direcciones.idPadre='$idLugar'";
    }


    $sql="SELECT entidades.*,entidades_tematicas.*,tematicas.*,direcciones.*, lugares_shp.nombre as nombreLugar FROM entidades 
            JOIN entidades_tematicas ON entidades.idEntidad=entidades_tematicas.idEntidad 
            JOIN tematicas ON entidades_tematicas.idTematica=tematicas.idTematica 
            JOIN direcciones ON entidades.idDireccion=direcciones.idDireccion
            JOIN lugares_shp ON direcciones.idPadre=lugares_shp.id
            WHERE ";
    if($busqueda!="")
        $sql.="($busqueda) AND ";
    if($tematica!="")
        $sql.="($tematica) AND ";
    if($lugar!="")
        $sql.="($lugar) AND ";
    $sql.="1 GROUP BY entidades.idEntidad ORDER BY points DESC LIMIT 0,$cantidad";

    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    {
        array_push($returnData,$fila);
    }
    return $returnData;

    //id/clase=organizaciones/tipo/tituloOrg/textoOrg/lugarOrg/puntos
    /*
    $link=connect();
    $sql="SELECT * 
            FROM entidades JOIN eventos 
            ON entidades.idEntidad=eventos.idEntidad
            WHERE entidad LIKE '%$cadena%'
            GROUP BY entidades.idEntidad
            LIMIT 0,$cantidad";
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
    */
}

function insertEmailPreregister($email, $idCiudad)
{
    $link=connect();
    $email=safe(filter_var($email,FILTER_SANITIZE_EMAIL));
    $idCiudad=safe($idCiudad);
    $sql="INSERT INTO preregister (email, idCiudad) VALUES ('$email','$idCiudad')";
    mysql_query($sql,$link);
}

function safe($value){ 
   return mysql_real_escape_string($value); 
} 

function getTematicas($cadena,$cantidad=10)
{
    $cadena=safe($cadena);
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

function getCiudadesMadrid($cadena,$cantidad=10)
{
    $cadena=safe($cadena);
    $link=connect();
    $sql="SELECT * 
            FROM  lugares_shp 
            WHERE idPadre BETWEEN 777000001 AND 777000008 AND nivel='8'
            AND nombre LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

function crearNuevoEvento($datosNuevoEvento)
{
    $fecha=safe($datosNuevoEvento["fecha"]);
    $fechaFin=safe($datosNuevoEvento["fechaFin"]);
    if($fechaFin=="")
        $fechaFin='NULL';
    else
        $fechaFin="'$fechaFin'";
    $clase=safe($datosNuevoEvento["clase"]);
    $tipo=safe($datosNuevoEvento["tipo"]);
    $titulo=safe($datosNuevoEvento["titulo"]);
    $texto=safe($datosNuevoEvento["texto"]);
    $lugar=safe($datosNuevoEvento["lugar"]);
    $x=safe($datosNuevoEvento["x"]);
    $y=safe($datosNuevoEvento["y"]);
    $idDistritoPadre=safe($datosNuevoEvento["idDistritoPadre"]);
    $idEntidad=safe($datosNuevoEvento["idEntidad"]);
    $temperatura=safe($datosNuevoEvento["temperatura"]);
    $tematicas=array();
    foreach($datosNuevoEvento["tematicas"] as $tematica)
        array_push($tematicas,safe($tematica));
    $idTematica=$tematicas[0];
    $idDireccion=safe($datosNuevoEvento["idDireccion"]);
    $url=safe($datosNuevoEvento["url"]);
    $email=safe($datosNuevoEvento["email"]);
    $etiquetas=safe($datosNuevoEvento["etiquetas"]);
    $repeatsAfter=safe($datosNuevoEvento["repeatsAfter"]);
    $eventoActivo=safe($datosNuevoEvento["eventoActivo"]);



    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);

    $sql="INSERT INTO eventos (fecha,fechaFin,clase,tipo,titulo,texto,lugar,temperatura,x,y,idDistritoPadre,idEntidad,
                                idTematica,idDireccion,url,email,etiquetas,repeatsAfter,eventoActivo)
                       VALUES ('$fecha',$fechaFin,'$clase','$tipo','$titulo','$texto','$lugar','$temperatura','$x','$y','$idDistritoPadre','$idEntidad',
                                '$idTematica','$idDireccion','$url','$email','$etiquetas','$repeatsAfter','$eventoActivo')";
    mysql_query($sql,$link);
    $idEvento=mysql_insert_id();
    foreach($tematicas as $tematica)
    {
        $sql="INSERT INTO eventos_tematicas (idEvento, idTematica) VALUES ('$idEvento', '$tematica')";
        mysql_query($sql,$link);
    }
}

function getEvento($idEvento)
{
    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM eventos WHERE idEvento='$idEvento' AND eventos.eventoActivo='1'";
    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $evento=$fila;
        
        $sql="SELECT * FROM direcciones WHERE idDireccion='{$fila['idDireccion']}'";
        $result=mysql_query($sql,$link);
        if($fila=mysql_fetch_assoc($result))
        {
            $evento['direccion']=$fila;
        }
        else
        {
            $evento['direccion']['direccion']="Sin dirección";
            $evento['direccion']['idDireccion']="0";
            $evento['direccion']['idPadre']="0";
            $evento['direccion']['lat']=0;
            $evento['direccion']['lng']=0;
            $evento['direccion']['nombre']="Sin nombre";
            $evento['direccion']['zoom']="15";
        }

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

function getEventos($query,$cantidad=50,$orden="fecha")
{
    $link=connect();
    $busqueda="";
    $tematica="";
    $lugar="";
    $lugares=array();
    $organizacion="";
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
                $tematica.="eventos_tematicas.idTematica='$id'";
                break;
            case "lugar":
                array_push($lugares,$id);
                break;
            case "organizacion":
            case "institucion":
            case "colectivo":
                if($organizacion!="")
                    $organizacion.=" OR ";
                $organizacion.="idEntidad='$id'";
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


    $sql="SELECT eventos.*,
    (SELECT GROUP_CONCAT(tematicas.tematica)
             FROM eventos_tematicas, tematicas
             WHERE eventos_tematicas.idTematica=tematicas.idTematica 
             AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
       FROM eventos, eventos_tematicas WHERE eventos.eventoActivo='1' AND ";
    if($busqueda!="")
        $sql.="($busqueda) AND ";
    if($tematica!="")
        $sql.="($tematica) AND ";
    if($lugar!="")
        $sql.="($lugar) AND ";
    if($organizacion!="")
         $sql.="($organizacion) AND ";
    $sql.="eventos.idEvento=eventos_tematicas.idEvento GROUP BY eventos.idEvento ORDER BY fecha ASC LIMIT 0,$cantidad";

    //echo $sql;
    //exit();

    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    {
        unset($fila["texto"]);
    	array_push($returnData,$fila);
    }
    return $returnData;
}

function getEventosPorValidar()
{
    $link=connect();

    $sql="SELECT eventos.*,
                 lugares_shp.nombre as nombreLugar,
                 entidades.entidad as entidad,
                 direcciones.nombre as nombreDireccion,
                 direcciones.direccion as direccion,
                 direcciones.direccionActiva as direccionActiva,                 
                 (SELECT GROUP_CONCAT(tematicas.tematica)
                 FROM eventos_tematicas, tematicas
                 WHERE eventos_tematicas.idTematica=tematicas.idTematica 
                 AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
            FROM eventos, direcciones, lugares_shp, entidades
            WHERE eventoActivo=0
            AND eventos.idDireccion=direcciones.idDireccion 
            AND direcciones.idPadre=lugares_shp.id 
            AND eventos.idEntidad=entidades.idEntidad
            GROUP BY eventos.idEvento 
            ORDER BY idEvento ASC";    
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    {
        array_push($returnData,$fila);
    }
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
    $idLugar=safe($idLugar);
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
    //Quizás no haría falta hacer el Join con eventos, ya que queremos todos
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
    {
        $fila["geocodigo"]=str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT);
        array_push($returnData,$fila);
        //array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT),$fila["cantidad"]));
    }
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

function getLugaresSuggestions($cadena,$lugarOriginal,$cantidad=4)
{
    //echo $lugarOriginal;
    $lugarOriginal=safe($lugarOriginal);
    $datosLugar=getDatosLugar($lugarOriginal);
    if($datosLugar['nivel']<8)
        $whereNiveles="AND nivel<='8'";

    $inSet=getAllChildren(array($lugarOriginal));
    unset($inSet[0]);   //Quitamos el original
    $link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
    $sql="SELECT * FROM lugares_shp WHERE 
            nombre LIKE '%$cadena%' AND
            id IN (".implode(",",$inSet).")
            $whereNiveles
            LIMIT 0,$cantidad";
    //echo $sql;        
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["geocodigo"]));
    return $returnData;

}

function getIrA($cadena,$lugarOriginal)
{
    //echo $lugarOriginal;
    //$inSet=getAllChildren(array($lugarOriginal));
    
    $cadena=safe($cadena);

    $link=connect();
    $sql="SELECT * FROM lugares_shp WHERE 
            nombre LIKE '$cadena%' AND (
            (nivel='8' AND ((idPadre BETWEEN 777000001 AND 777000007) OR (idPadre='666000028'))) OR
            (nivel='6' OR nivel='7')
            )";

    //Por ahora forzado a niveles 6/7 de Madrid (no tenemos de otras provincias) Y nivel 8 de Madrid


    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    if($fila=mysql_fetch_assoc($result))
        return $fila;
    else
        return false;

}

function getDireccionesSuggestions($cadena,$lugarOriginal,$cantidad=5)
{
    //echo $lugarOriginal;
    $inSet=getAllChildren(array($lugarOriginal));
    $link=connect();
    //unset($inSet[0]);   //Quitamos el original
    $sql="SELECT * FROM direcciones WHERE 
            (nombre LIKE '%$cadena%' OR direccion LIKE '%$cadena%') AND
            idPadre IN (".implode(",",$inSet).")  AND direcciones.direccionActiva='1' 
            LIMIT 0,$cantidad";
    //echo $sql;        
    mysql_query('SET CHARACTER SET utf8',$link);
    $result=mysql_query($sql,$link);
    $returnData=array();
    while($fila=mysql_fetch_assoc($result))
    {
        if($fila["nombre"]==="")
            $fila["nombre"]="Dirección";
        array_push($returnData,array($fila["idDireccion"],$fila["nombre"],$fila["direccion"],$fila["lat"],$fila["lng"],$fila["zoom"]));
    }
    return $returnData;

}

function getDistritoPadreDireccion($idDireccion)
{
    $direccion=safe($direccion);

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM direcciones WHERE 
                    idDireccion='$idDireccion'";

    $link=connect();    
    mysql_query('SET CHARACTER SET utf8',$link);

    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $idDistritoPadre=$fila["idPadre"];
    }
    else
    {
        //No hemos encontrado padre, poner 0 para revisar más tarde
        $idDistritoPadre=0;
    }
    return $idDistritoPadre;
}

function getDireccion($idDireccion)
{
    $idDireccion=safe($idDireccion);
    $link=connect();    
    mysql_query('SET CHARACTER SET utf8',$link);

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM direcciones WHERE idDireccion='$idDireccion'";
    $result=mysql_query($sql,$link);
    return mysql_fetch_assoc($result);
}

function crearNuevaDireccion($nombreLugar,$direccion,$lat,$lng,$idPadre)
{
    $nombreLugar=safe($nombreLugar);
    $lat=safe($lat);
    $lng=safe($lng);
    $idPadre=safe($idPadre);
    $direccion=safe($direccion);

    $link=connect();    
    mysql_query('SET CHARACTER SET utf8',$link);

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM lugares_shp WHERE 
                    xmin<'$lng' AND
                    ymin<'$lat' AND
                    xmax>'$lng' AND
                    ymax>'$lat' AND 
                    idPadre='$idPadre'";

    $result=mysql_query($sql,$link);
    if($fila=mysql_fetch_assoc($result))
    {
        $idDistritoPadre=$fila["id"];
    }
    else
    {
        //No hemos encontrado padre, poner 0 para revisar más tarde
        $idDistritoPadre=0;
    }

    //Zoom=15
    //Activa=0
    $sql="INSERT INTO direcciones (idPadre,nombre,direccion,lat,lng,zoom,direccionActiva) 
                           VALUES ('$idDistritoPadre','$nombreLugar','$direccion','$lat','$lng','15','0')";
    mysql_query($sql,$link);
    $returnData["idLugar"]=mysql_insert_id();
    $returnData["idDistritoPadre"]=$idDistritoPadre;
    return $returnData;
}

function validarDireccion($idDireccion,$status)
{
    $idDireccion=safe($idDireccion);
    $status=safe($status);
    $link=connect();    
    mysql_query('SET CHARACTER SET utf8',$link);

    //Buscar el padre según las coordenadas
    $sql="UPDATE direcciones SET direccionActiva='$status' WHERE idDireccion='$idDireccion'";
    mysql_query($sql,$link);
}

function validarEvento($idEvento,$status)
{
    $idEvento=safe($idEvento);
    $status=safe($status);
    $link=connect();    
    mysql_query('SET CHARACTER SET utf8',$link);

    //Buscar el padre según las coordenadas
    $sql="UPDATE eventos SET eventoActivo='$status' WHERE idEvento='$idEvento'";
    mysql_query($sql,$link);
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
        {
            $fila["geocodigo"]=str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT);
            array_push($returnData,$fila);
        }
    return $returnData;
}

function getEventosCoordenadas($xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    $sql="SELECT * FROM eventos,lugares_shp WHERE 
            x>$xmin AND x<$xmax AND y>$ymin AND y<$ymax AND eventos.idDistritoPadre=lugares_shp.id AND eventos.eventoActivo='1' ";
            
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