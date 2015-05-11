<?php
include "settings.php";
include_once "passwordHashing.php";
mb_internal_encoding("UTF-8");

function connect()
{
    $conn = mysqli_connect(DB_DOMAIN, DB_USERNAME, DB_PASSWORD, DB_DB) or die("Unable to connect to DB: " . mysqli_error($conn));
    //mysqli_query($link, 'SET CHARACTER SET utf8',$conn);
    return $conn;   
}

//USERS
function createUser($user,$email,$pass)
{
    $link=connect();
    $user=safe($link, $user);
    $email=safe($link, filter_var($email,FILTER_SANITIZE_EMAIL));
    $pass=safe($link, $pass);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM users WHERE email='$email' OR user='$user'";
    $result=mysqli_query($link,$sql);
    if($fila=mysqli_fetch_assoc($result))
        return false;

    $hash=create_hash($pass);
    $verificationToken=base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));

    $sql="INSERT INTO users (user,email,hash,verified,verificationToken) VALUES ('$user','$email','$hash','0','$verificationToken')";
    mysqli_query($link, $sql);

    //ToDo: Comprobar que se crea bien el usuario

    $createdUser["user"]=$user;
    $createdUser["email"]=$email;
    $createdUser["verified"]=0;
    $createdUser["verificationToken"]=$verificationToken;


    return $createdUser;
}

function verifyUser($email,$token)
{
    $link=connect();
    $email=safe($link, filter_var($email,FILTER_SANITIZE_EMAIL));
    $token=safe($link, $token);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM users WHERE email='$email' AND verificationToken='$token'";
    $result=mysqli_query($link, $sql);
    //echo $sql;
    
    if($fila=mysqli_fetch_assoc($result))
    {
        $sql="UPDATE users SET verified=1, verificationToken='' WHERE idUser='{$fila["idUser"]}'";
        //echo $sql;
        mysqli_query($link, $sql);
        return true;
    }
    else
        return false;

}

function getUser($email,$pass)
{
    $link=connect();
    //Sanitize inputs
    $email=safe($link, filter_var($email,FILTER_SANITIZE_EMAIL));
    $pass=safe($link, $pass);

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM users WHERE email='$email'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
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
    $link=connect();
    $email=safe($link, filter_var($email,FILTER_SANITIZE_EMAIL));

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM users WHERE email='$email'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
    {
        $verificationToken=base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
        $sql="UPDATE users SET verificationToken='$verificationToken' WHERE idUser='{$fila["idUser"]}'";
        mysqli_query($link, $sql);
        return $verificationToken;
    }
    return false;
}

function changeUserPassword($email,$token,$nuevoPassword)
{
    $link=connect();
    $email=safe($link, $email);
    $token=safe($link, $token);
    $nuevoPassword=safe($link, $nuevoPassword);

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM users WHERE email='$email' AND verificationToken='$token'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
    {
        $hash=create_hash($nuevoPassword);
        $sql="UPDATE users SET hash='$hash', verificationToken='', verified='1' WHERE idUser='{$fila["idUser"]}'";
        mysqli_query($link, $sql);
        return true;
    }
    return false;
}


//Follow lists, according to the filters listed in params

function follow($idUser,$params,$clase)
{
    $link=connect();
    $idUser=safe($link, $idUser);
    $clase=safe($link, $clase);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="INSERT INTO avisosListados (idUser, query,clase) VALUES ('$idUser','$params','$clase')";
    mysqli_query($link, $sql);
}

function unfollow($idUser,$params,$clase)
{   
    $link=connect();
    $idUser=safe($link, $idUser);
    $clase=safe($link, $clase);

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="DELETE FROM avisosListados WHERE idUser='$idUser' AND query='$params' AND clase='$clase'";
    mysqli_query($link, $sql);
}

function isFollowing($idUser,$params,$clase)
{
    $link=connect();
    $idUser=safe($link, $idUser);
    $clase=safe($link, $clase);

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT idAvisoListado FROM avisosListados WHERE idUser='$idUser' AND query='$params' AND clase='$clase'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
        return true;
    else
        return false;
}

//Entidades
function getEntidad($idEntidad)
{
    $link=connect();
    $idEntidad=safe($link, $idEntidad);

    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM entidades WHERE idEntidad='$idEntidad'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
    {
        $entidad=$fila;


        $sql="SELECT * FROM direcciones WHERE idDireccion='{$fila['idDireccion']}'";
        $result=mysqli_query($link, $sql);
        if($fila=mysqli_fetch_assoc($result))
        {
            $entidad['direccion']=$fila;
        }
        else
        {
            $entidad['direccion']['direccion']="Sin dirección";
            $entidad['direccion']['idDireccion']="0";
            $entidad['direccion']['idCiudad']="0";
            $entidad['direccion']['idSubCiudad']="0";
            $entidad['direccion']['lat']=0;
            $entidad['direccion']['lng']=0;
            $entidad['direccion']['nombre']="Sin nombre";
            $entidad['direccion']['zoom']="15";
        }

        $sql="SELECT * FROM entidades_tematicas, tematicas 
                WHERE entidades_tematicas.identidad='$idEntidad' AND
                entidades_tematicas.idTematica=tematicas.idTematica";
        $result=mysqli_query($sql,$link);
        while($fila=mysqli_fetch_assoc($result))
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

function getEntidadesPorNombre($cadena,$cantidad=10)
{
    $link=connect();
    $cadena=safe($link, $cadena);
    $cantidad=safe($link, filter_var($cantidad,FILTER_SANITIZE_NUMBER_INT));
   
    $sql="SELECT * 
            FROM  entidades 
            WHERE entidad LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    	array_push($returnData,$fila);
    return $returnData;
}

// Returns a list of entities that have events in a certain territory
// Needs to be updated to comply with new logic (using direcciones, not idDistritoPadre in eventos)
// TODO: It could be required to change to a collection of territories (for city+ and neighbourhood areas
function getEntidadesZonaConEventos($cadena,$idTerritorio,$cantidad=10)
{
  $link=connect();
  // Sanitize inputs
  $cadena=safe($link, $cadena);
  $cantidad=safe($link, filter_var($cantidad,FILTER_SANITIZE_NUMBER_INT));
  $idTerritorio=safe($link, $idTerritorio);
  $nivel=getNivelTerritorio($idTerritorio);

    $sql="SELECT *
            FROM entidades 
            JOIN eventos ON entidades.idEntidad=eventos.idEntidad
            JOIN direcciones ON eventos.idDireccion=direcciones.idDireccion
            WHERE entidad LIKE '%$cadena%'";

    if ($nivel<8) // Levels above city, searches will be done on a city-basis
    {    
      $hijos=getDescendantsOfLevel($idTerritorio,8);
      $sql.=" AND direcciones.idCiudad IN ('".join($hijos,"','")."')";
    }
    else // Map under city, seaches done on a district neighborhood-basis
    {
        $hijos=getAllChildren($idTerritorio,9);
        $sql.=" AND direcciones.idSubCiudad IN ('".join($hijos,"','")."')";
    }

    $sql.=" GROUP BY entidades.idEntidad
            LIMIT 0,$cantidad";
    
// Ejemplo de query producida:
// SELECT *	FROM entidades 
//	JOIN eventos ON entidades.idEntidad=eventos.idEntidad
//  JOIN direcciones ON eventos.idDireccion=direcciones.direccion
//	WHERE entidad LIKE '%centro%' 
//    AND direcciones.idDistrito IN ('901280005','901280006')    
//  GROUP BY entidades.idEntidad
//  LIMIT 0,10;

    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

// Gets the information about Entities to be displayed, taking into account all filters and   
function getEntidades($filtros, $idTerritorio, $cantidad=10)
{
    $link=connect();
  // Sanitize inpusts
    $cantidad=safe($link, filter_var($cantidad,FILTER_SANITIZE_NUMBER_INT));
    $idTerritorio=safe($link,$idTerritorio);
    $nivel= getNivelTerritorio($idTerritorio);
        
    $busqueda="";
    $tematica="";
    $lugar="";
    $lugares=array();
    foreach($filtros as $filtro)
    {
        $tipo=$filtro["tipo"];
        $texto=$filtro["texto"];
        $id=$filtro["id"];
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


    $sql="SELECT entidades.*,entidades_tematicas.*,tematicas.*,direcciones.*, territorios.nombre as nombreLugar FROM entidades 
          JOIN entidades_tematicas ON entidades.idEntidad=entidades_tematicas.idEntidad 
          JOIN tematicas ON entidades_tematicas.idTematica=tematicas.idTematica 
          JOIN direcciones ON entidades.idDireccion=direcciones.idDireccion ";
    
    if ($nivel<8) // Levels above city, searches will be done on a city-basis
    {
      $sql.="JOIN territorios ON direcciones.idCiudad=territorios.id
             WHERE ";
      $hijos=getAllDescendantsOfLevel($lugares,8);
      $lugar="direcciones.idCiudad IN ('".join($hijos,"','")."')";
    }
    else // Map at City or lower level, searches done on SubCityLevel (district, neighborhood) basis
    {
      $sql.="JOIN territorios ON direcciones.idSubCiudad=territorios.id
             WHERE ";
      $lugares=getAllChildren($lugares,9);
      $ids=implode(",",$lugares);
      $lugar="direcciones.idSubCiudad in ($ids)";
    }
               
    if($busqueda!="")
        $sql.="($busqueda) AND ";
    if($tematica!="")
        $sql.="($tematica) AND ";
    if($lugar!="")
        $sql.="($lugar) AND ";
    $sql.="1 GROUP BY entidades.idEntidad ORDER BY points DESC LIMIT 0,$cantidad";
    
//  Example of query. 
//  We need to edit it. The way the filters of territories are applied is unsatisfactory.
//  Now lugar will have ids of city and subCity (district or neighbourhood).
//  Depending of where the search happens, we will use different ones.
//  
//  SELECT entidades.*,entidades_tematicas.*,tematicas.*,direcciones.*, territorios.nombre as nombreLugar 
//  FROM entidades 
//    JOIN entidades_tematicas ON entidades.idEntidad=entidades_tematicas.idEntidad 
//    JOIN tematicas ON entidades_tematicas.idTematica=tematicas.idTematica 
//    JOIN direcciones ON entidades.idDireccion=direcciones.idDireccion 
//    JOIN territorios ON direcciones.idPadre=territorios.id             
//  WHERE 
//    (entidad LIKE '%$texto%' OR entidad LIKE '%$texto%') and
//    (entidades_tematicas.idTematica='$id' OR entidades_tematicas.idTematica='$id') AND
//    (direcciones.idBarrio='801280005') OR direcciones.idBarrio='901280005' OR direcciones.idBarrio='901280006' OR direcciones.idBarrio='901280007' OR direcciones.idBarrio='901280008') AND
//    1 
//  GROUP BY entidades.idEntidad ORDER BY points DESC LIMIT 0,$cantidad

    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    {
        array_push($returnData,$fila);
    }
    return $returnData;

}

function insertEmailPreregister($email, $idCiudad)
{
    $link=connect();
    //Sanitize inputs
    $email=safe($link, filter_var($email,FILTER_SANITIZE_EMAIL));
    $idCiudad=safe($link, $idCiudad);

    
    $sql="INSERT INTO preregister (email, idCiudad) VALUES ('$email','$idCiudad')";
    mysqli_query($link, $sql);
}

function safe($link, $value){
      return mysqli_real_escape_string($link, $value);
}

function getTematicas($cadena,$cantidad=10)
{
    $link=connect();   
  //Sanitize inputs
  $cadena=safe($link, $cadena);
    $cantidad=safe($link, filter_var($cantidad,FILTER_SANITIZE_NUMBER_INT));

    
    $sql="SELECT *
            FROM  tematicas
            WHERE tematica LIKE '%$cadena%'
            LIMIT 0,$cantidad";
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

function crearNuevoEvento($datosNuevoEvento)
{
    $link=connect();
    //Sanitize inputs
    $fecha=safe($link, $datosNuevoEvento["fecha"]);
    $fechaFin=safe($link, $datosNuevoEvento["fechaFin"]);
    
    if($fechaFin=="")
        $fechaFin='NULL';
    else
        $fechaFin="'$fechaFin'";
    $clase=safe($link, $datosNuevoEvento["clase"]);
    $tipo=safe($link, $datosNuevoEvento["tipo"]);
    $titulo=safe($link, $datosNuevoEvento["titulo"]);
    $texto=safe($link, $datosNuevoEvento["texto"]);
    $lugar=safe($link, $datosNuevoEvento["lugar"]);
    $x=safe($link, $datosNuevoEvento["x"]);
    $y=safe($link, $datosNuevoEvento["y"]);
    $idDistritoPadre=safe($link, $datosNuevoEvento["idDistritoPadre"]);
    $idEntidad=safe($link, $datosNuevoEvento["idEntidad"]);
    $temperatura=safe($link, $datosNuevoEvento["temperatura"]);
    $tematicas=array();
    foreach($datosNuevoEvento["tematicas"] as $tematica)
        array_push($tematicas,safe($link, $tematica));
    $idTematica=$tematicas[0];
    $idDireccion=safe($link, $datosNuevoEvento["idDireccion"]);
    $url=safe($link, filter_var($datosNuevoEvento["url"], FILTER_SANITIZE_URL));
    $email=safe($link, filter_var($datosNuevoEvento["email"], FILTER_SANITIZE_EMAIL));
    $etiquetas=safe($link, $datosNuevoEvento["etiquetas"]);
    $repeatsAfter=safe($link, $datosNuevoEvento["repeatsAfter"]);
    $eventoActivo=safe($link, $datosNuevoEvento["eventoActivo"]);


    
    mysqli_query($link, 'SET CHARACTER SET utf8');

    $sql="INSERT INTO eventos (fecha,fechaFin,clase,tipo,titulo,texto,lugar,temperatura,x,y,idDistritoPadre,idEntidad,
                                idTematica,idDireccion,url,email,etiquetas,repeatsAfter,eventoActivo)
                       VALUES ('$fecha',$fechaFin,'$clase','$tipo','$titulo','$texto','$lugar','$temperatura','$x','$y','$idDistritoPadre','$idEntidad',
                                '$idTematica','$idDireccion','$url','$email','$etiquetas','$repeatsAfter','$eventoActivo')";
    mysqli_query($link, $sql);
    $idEvento=mysql_insert_id();
    foreach($tematicas as $tematica)
    {
        $sql="INSERT INTO eventos_tematicas (idEvento, idTematica) VALUES ('$idEvento', '$tematica')";
        mysqli_query($link, $sql);
    }
}

function getEvento($idEvento)
{
    //sanitize input
    $link=connect();
    $idEvento=safe($link, $idEvento);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM eventos WHERE idEvento='$idEvento' AND eventos.eventoActivo='1'";
    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
    {
        $evento=$fila;

        $sql="SELECT * FROM direcciones WHERE idDireccion='{$fila['idDireccion']}'";
        $result=mysqli_query($link, $sql);
        if($fila=mysqli_fetch_assoc($result))
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
        $result=mysqli_query($link, $sql);
        while($fila=mysqli_fetch_assoc($result))
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

function getEventos($filtros,$idTerritorio,$cantidad=50)
{
    $link=connect();
    //Sanitize inputs
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));
    $idTerritorio=safe($link,$idTerritorio);
    $nivel= getNivelTerritorio($idTerritorio);

    $busqueda="";
    $tematica="";
    $lugar="";
    $lugares=array();
    $organizacion="";
    foreach($filtros as $filtro)
    {
        $tipo=$filtro["tipo"];
        $texto=$filtro["texto"];
        $id=$filtro["id"];
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


    $sql="SELECT eventos.*,
    (SELECT GROUP_CONCAT(tematicas.tematica)
             FROM eventos_tematicas, tematicas
             WHERE eventos_tematicas.idTematica=tematicas.idTematica
             AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
       FROM eventos, eventos_tematicas, direcciones 
       WHERE eventos.idDireccion=direcciones.idDireccion 
         AND eventos.eventoActivo='1' AND ";
//TODO: Validate where the territory place is taken from. Event? It should probably be changed.
        
    if ($nivel<8) // Levels above city, searches will be done on a city-basis
    {    
      $hijos=getAllDescendantsOfLevel($lugares,8);
      $lugar="direcciones.idCiudad IN ('".join($hijos,"','")."')";
    }
    else // Map at City or lower level, searches done on SubCityLevel (district, neighborhood) basis
    {
        $hijos=getAllChildren($lugares,9);
        $lugar="direcciones.idSubCiudad IN ('".join($hijos,"','")."')";
    }
//  Example of the query, already using the direcciones instead of idPadre at events
//  SELECT eventos.*,
//    (SELECT GROUP_CONCAT(tematicas.tematica)
//             FROM eventos_tematicas, tematicas
//             WHERE eventos_tematicas.idTematica=tematicas.idTematica
//             AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
//       FROM eventos, eventos_tematicas, direcciones 
//       WHERE eventos.idDireccion=direcciones.idDireccion
//       AND eventos.eventoActivo='1'
//       AND direcciones.idSubCiudad IN ('901280005','901280006','901280007') 
//       and eventos.idEvento=eventos_tematicas.idEvento 
//       GROUP BY eventos.idEvento        ORDER BY fecha ASC LIMIT 0,50;
//       
    
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

    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
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
                 territorios.nombre as nombreLugar,
                 entidades.entidad as entidad,
                 direcciones.nombre as nombreDireccion,
                 direcciones.direccion as direccion,
                 direcciones.direccionActiva as direccionActiva,
                 (SELECT GROUP_CONCAT(tematicas.tematica)
                 FROM eventos_tematicas, tematicas
                 WHERE eventos_tematicas.idTematica=tematicas.idTematica 
                 AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
            FROM eventos, direcciones, territorios, entidades
            WHERE eventoActivo=0
            AND eventos.idDireccion=direcciones.idDireccion 
            AND direcciones.idPadre=territorios.id 
            AND eventos.idEntidad=entidades.idEntidad
            GROUP BY eventos.idEvento 
            ORDER BY idEvento ASC";    //needs to be updated (idPadre)
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    {
        array_push($returnData,$fila);
    }
    return $returnData;
}

// Returns all descendants of a list of territories, with levels from 5 to 10
// $nivelBase initialised to 5 (country) by default, to cover all territories.
function getAllChildren($territorios, $nivelBase=5)
{
    $link=connect();
    for($nivel=$nivelBase;$nivel<=10;$nivel++)
    {
        $ids=implode(",",$territorios);
        $sql="SELECT id FROM territorios WHERE nivel='$nivel' AND idPadre IN ($ids)";
        $result=mysqli_query($link, $sql);
        while($fila=mysqli_fetch_assoc($result))
            array_push($territorios,$fila['id']);
    }
    
    return(array_unique($territorios));

}

// Returns all descendants of a list of territories, that are of level $nivelFinal
// $nivelBase initialised to 5 (country) by default, to cover all territories.
function getAllDescendantsOfLevel($territorios,$nivelFinal,$nivelBase=5)
{
    $link=connect();
    
    // In case there is just one territory, we can use its level as the base of the loop
    if (count($territorios)==1)
        $nivelBase=getNivelTerritorio($territorios[0])+1;
    
    for($nivel=$nivelBase;$nivel<=$nivelFinal;$nivel++)
    {
        $ids=implode(",",$territorios);
        if ($nivel==$nivelFinal) {
          // When the final level has been reached, array is emptied to just have the results from the lower level
          // and make sure territories of that level included in $territorios are also part of the result        
          $territorios=[];
          $sql="SELECT id FROM territorios WHERE nivel='$nivel' AND (idPadre IN ($ids) OR id IN ($ids))";
        }
        else
          $sql="SELECT id FROM territorios WHERE nivel='$nivel' AND idPadre IN ($ids)";

        $result=mysqli_query($link, $sql);
        while($fila=mysqli_fetch_assoc($result))
          array_push($territorios,$fila['id']);
    }  
    return(array_unique($territorios));
}

// Returns descendants of $idTerritorio that are of level $nivelFinal
function getDescendantsOfLevel($idTerritorio,$nivelFinal)
{
    $link=connect();
    
    $nivelTerritorio=getNivelTerritorio($idTerritorio);
    $territorios=array();
    if ($nivelTerritorio==$nivelFinal)
      array_push($territorios,$idTerritorio);
    else
    {
      $ids=$idTerritorio;
      for($nivelLoop=$nivelTerritorio+1;$nivelLoop<=$nivelFinal;$nivelLoop++)
      {
        $sql="SELECT id FROM territorios WHERE nivel='$nivelLoop' AND idPadre IN ($ids)";
        $result=mysqli_query($link, $sql);
        if ($nivelLoop<$nivelFinal)
        {
          $ids="";
          while($fila=mysqli_fetch_assoc($result))
          {
            if($ids!="")
              $ids.=",";
            $ids.=$fila['id'];
          }   
        }
        else
        {
          while($fila=mysqli_fetch_assoc($result))
            array_push($territorios,$fila['id']);
        }
      }
    }
    return($territorios);
}

//Devuelve array con los datos de los territorios ancestros de idLugar.
function getAllAncestors($idTerritorio)
{
    $lugar=getDatosLugar($idTerritorio); 
    $lugares[$lugar["nivel"]]=$lugar;
    $idPadre=$lugar["idPadre"];
    
    while($idTerritorio!=0)
    {
            $lugar=getDatosLugar($idPadre);
            $lugares[$lugar["nivel"]]=$lugar;
            $idPadre=$lugar["idPadre"];
    }
    
    return $lugares;
}

//Devuelve array con los datos de los territorios ancestros fértiles (más de un hijo) de idLugar. 
//IdLugar should be the base territory (fertile)
//Usado para generar el breadcrumb de un territorio
function getFertileAncestors($idTerritorio)
{
    $lugar=getDatosLugar($idTerritorio);
    $lugares[$lugar["nivel"]] = $lugar;    
    $idPadre = $lugar["idPadre"];
    while($idPadre!=0)
    {
            $lugar=getDatosLugar($idPadre);

            // idDescendiente es 0 si no tiene hijos, id del hijo si sólo tiene un hijo, o "2" si tiene múltiples hijos.
            // NULL corresponde a un estado indeterminado.
            if (!isset($lugar["idDescendiente"])||$lugar["idDescendiente"]==2)
            {
                $lugares[$lugar["nivel"]]=$lugar;
    }
            $idPadre=$lugar["idPadre"];
    }
    
            // idDescendiente es 0 si no tiene hijos, id del hijo si sólo tiene un hijo, o "2" si tiene múltiples hijos.
            // NULL corresponde a un estado indeterminado.
           
           

    return $lugares;
}


function getFertility($idTerritorio)
{
    $link=connect();
    $idTerritorio=safe($link,$idTerritorio);
    $sql="SELECT id
            FROM territorios 
            WHERE idPadre='$idTerritorio'";
 
    $result=mysqli_query($link, $sql);
    $numberSons = mysqli_num_rows($result);
    if ($numberSons == 0)
        $fertility=0;
    elseif($numberSons == 1){
        $fila=mysqli_fetch_assoc($result);
        $fertility=$fila['id'];
    }
    else
        $fertility=2;

    return $fertility; 
}

function getDatosLugar($idTerritorio)
{
    //Sanitize input
    $link=connect();
    $idTerritorio=safe($link, $idTerritorio);  
    
    $sql="SELECT * 
            FROM  territorios 
            WHERE id='$idTerritorio'";
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila;
}

// Returns "Nivel" (level) of the territory with id "idLugar" 
function getNivelTerritorio($idTerritorio)
{
    //Sanitize input
    $link=connect();
    $idTerritorio=safe($link, $idTerritorio);  
    
    $sql="SELECT nivel 
            FROM  territorios 
            WHERE id='$idTerritorio'";

    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila["nivel"];
}

// Returns max min coordinates from the "base" territory for idLugar, ie: the first descendent with multiple offspring or no child. 
function getCoordenadasInteriores($idTerritorio)
{
        //Sanitize input
    $link=connect();
    $idTerritorio=safe($link, $idTerritorio);  
    
    $sql="SELECT min(xmin) as xmin, max(xmax) as xmax, min(ymin) as ymin, max(ymax) as ymax "
        . "FROM territorios "
        . "WHERE idPadre='$idTerritorio'";
    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila;
}

// Returns max min coordinates from the surrounding territories of an idLugar. 
function getCoordenadasColindantes($type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    //sanitize inputs
    $type=safe($link, $type);
    $xmin=safe($link, $xmin);
    $xmax=safe($link, $xmax);
    $ymin=safe($link, $ymin);
    $ymax=safe($link, $ymax);

    $sql="SELECT min(xmin) as xmin, max(xmax) as xmax, min(ymin) as ymin, max(ymax) as ymax FROM territorios WHERE
            nivel='$type'
            AND NOT(xmin > $xmax
            OR $xmin >  xmax
            OR  ymax < $ymin
            OR $ymax < ymin)";

    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila;
}

// Returns max min coordinates of the centroids of the surrounding territories of an idLugar. 
function getCoordenadasCentroidesColindantes($type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    //sanitize inputs
    $type=safe($link, $type);
    $xmin=safe($link, $xmin);
    $xmax=safe($link, $xmax);
    $ymin=safe($link, $ymin);
    $ymax=safe($link, $ymax);

    $sql="SELECT min(xcentroid) as xmin, max(xcentroid) as xmax, min(ycentroid) as ymin, max(ycentroid) as ymax 
            FROM territorios WHERE
            nivel='$type'
            AND NOT(xmin > $xmax
            OR $xmin >  xmax
            OR  ymax < $ymin
            OR $ymax < ymin)";

    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila;
}


// Returns data from the "base" territory for idLugar, ie: the first descendent with multiple offspring or no child. 
function getDatosLugarBase($idTerritorio)
{
        //Sanitize input
    $link=connect();
    $idTerritorio=safe($link, $idTerritorio);  
    
    $sql="SELECT * 
            FROM  territorios 
            WHERE id='$idTerritorio'";
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    $descendiente=$fila["idDescendiente"];
    if (!isset($descendiente) || ($descendiente!=0 && $descendiente!=2))
        //it has just one child
        return getDatosLugarBase($descendiente);
    else
        // it has many or cero children
        return $fila;
    
}

function getChildAreas($lugarOriginal,$nivel)
{
    $link=connect();
    
//Quizás no haría falta hacer el Join con eventos, ya que queremos todos        
 /*   $sql="SELECT territorios.*,count(eventos.idDistritoPadre) as cantidad
            FROM territorios LEFT OUTER JOIN eventos 
            ON territorios.id=eventos.idDistritoPadre 
            WHERE nivel='$nivel'
            AND idPadre='$lugarOriginal'
            GROUP BY territorios.id";
  * 
  */

  $sql="SELECT territorios.*
            FROM territorios 
            WHERE nivel='$nivel'
            AND idPadre='$lugarOriginal' ORDER BY nombre";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();    
    while($fila=mysqli_fetch_assoc($result))
    {
        array_push($returnData,$fila);     
    }
    return $returnData;   
}

//function is used?
function getLugares($cadena,$lugarOriginal,$type,$cantidad=3,$inSet=array())
{
    //Sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $lugarOriginal=safe($link, $lugarOriginal);
    $type=safe($link, $type);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));
    $sql="SELECT * FROM territorios WHERE
            nivel='$type' AND
            provincia=28 AND
            nombre LIKE '%$cadena%' AND
            id<>'$lugarOriginal' ORDER BY nombre";
    if(count($inSet)>0)
        $sql.="";
    $sql.="LIMIT 0,$cantidad";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"]));
    return $returnData;

}

function getLugaresSuggestions($cadena,$lugarOriginal,$cantidad=4)
{
    //Sanitize input
    $link=connect();
    $cadena=safe($link, $cadena);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));
    $lugarOriginal=safe($link, $lugarOriginal);

    $datosLugar=getDatosLugar($lugarOriginal);
    if($datosLugar['nivel']<8)
        $whereNiveles="AND nivel<='8'";

    $inSet=getAllChildren(array($lugarOriginal));
    unset($inSet[0]);   //Quitamos el original
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM territorios WHERE
            nombre LIKE '%$cadena%' AND
            id IN (".implode(",",$inSet).")
            $whereNiveles
            LIMIT 0,$cantidad";
    //echo $sql;
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"]));
    return $returnData;

}

function getIrA($cadena,$lugarOriginal)
{
    //echo $lugarOriginal;
    //$inSet=getAllChildren(array($lugarOriginal));

    //Sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $lugarOriginal=safe($link, $lugarOriginal);

   $sql="SELECT id, nombre, activo FROM territorios WHERE nombre LIKE '$cadena%' AND nivel<9 ORDER BY nombre, activo DESC, id DESC";
   // Order by, so the lower level appear before higher levels with the same name. Guadalajara (city), Guadalajara (province)
   // Order by activo DESC for show first


    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    if($fila=mysqli_fetch_assoc($result))
        return $fila;
    else
        return false;

}

function getDireccionesSuggestions($cadena,$lugarOriginal,$cantidad=5)
{

    //sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $lugarOriginal=safe($link, $lugarOriginal);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));

    //echo $lugarOriginal;
    $inSet=getAllChildren(array($lugarOriginal));
    
    //unset($inSet[0]);   //Quitamos el original
    $sql="SELECT * FROM direcciones WHERE 
            (nombre LIKE '%$cadena%' OR direccion LIKE '%$cadena%') AND
            idPadre IN (".implode(",",$inSet).")  AND direcciones.direccionActiva='1' 
            LIMIT 0,$cantidad"; // Needs to be updated (idPadre)
    //echo $sql;
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    {
        if($fila["nombre"]==="")
            $fila["nombre"]="Dirección";
        array_push($returnData,array($fila["idDireccion"],$fila["nombre"],$fila["direccion"],$fila["lat"],$fila["lng"],$fila["zoom"]));
    }
    return $returnData;

}

function getDistritoPadreDireccion($idDireccion) //Needs to be updated (idPadre)
{
    $link=connect();
    $direccion=safe($link, $direccion);

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM direcciones WHERE 
                    idDireccion='$idDireccion'";
   
    mysqli_query($link, 'SET CHARACTER SET utf8');

    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
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
    //Sanitize input
    $link=connect();
    $idDireccion=safe($link, $idDireccion);

    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM direcciones WHERE idDireccion='$idDireccion'";
    $result=mysqli_query($link, $sql);
    return mysqli_fetch_assoc($result);
}

function crearNuevaDireccion($nombreLugar,$direccion,$lat,$lng,$idPadre)
{
    // Sanitize input
    $link=connect();
    $nombreLugar=safe($link, $nombreLugar);
    $lat=safe($link, $lat);
    $lng=safe($link, $lng);
    $idPadre=safe($link, $idPadre);
    $direccion=safe($link, $direccion);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM territorios WHERE
                    xmin<'$lng' AND
                    ymin<'$lat' AND
                    xmax>'$lng' AND
                    ymax>'$lat' AND
                    idPadre='$idPadre'";

    $result=mysqli_query($link, $sql);
    if($fila=mysqli_fetch_assoc($result))
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
                           VALUES ('$idDistritoPadre','$nombreLugar','$direccion','$lat','$lng','15','0')"; // Needs to be updated (idPadre)
    mysqli_query($link, $sql);
    $returnData["idTerritorio"]=mysqli_insert_id();
    $returnData["idDistritoPadre"]=$idDistritoPadre;
    return $returnData;
}

function validarDireccion($idDireccion,$status)
{
    //Sanitize inputs
    $link=connect();
    $idDireccion=safe($link, $idDireccion);
    $status=safe($link, $status);
   
    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="UPDATE direcciones SET direccionActiva='$status' WHERE idDireccion='$idDireccion'";
    mysqli_query($link, $sql);
}

function validarEvento($idEvento,$status)
{
    //Sanitiza inputs
    $link=connect();
    $idEvento=safe($link, $idEvento);
    $status=safe($link, $status);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="UPDATE eventos SET eventoActivo='$status' WHERE idEvento='$idEvento'";
    mysqli_query($link, $sql);
}

// Gets the areas of level $type that are contained within the limits, excluding the central $lugarOriginal
function getColindantes($lugarOriginal,$type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    //sanitize inputs
    $lugarOriginal=safe($link, $lugarOriginal);
    $type=safe($link, $type);
    $xmin=safe($link, $xmin);
    $xmax=safe($link, $xmax);
    $ymin=safe($link, $ymin);
    $ymax=safe($link, $ymax);


    $sql="SELECT * FROM territorios WHERE
            nivel='$type' AND
            NOT(xmin > $xmax
            OR $xmin >  xmax
            OR  ymax < $ymin
            OR $ymax < ymin)";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        // the original territory is excluded
        if($fila["id"]!=$lugarOriginal)
        {
            array_push($returnData,$fila);
        }
    return $returnData;
}

function getEventosCoordenadas($xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    //sanitize inputs
    $xmin=safe($link, $xmin);
    $xmax=safe($link, $xmax);
    $ymin=safe($link, $ymin);
    $ymax=safe($link, $ymax);

    
    $sql="SELECT * FROM eventos,territorios WHERE 
            x>$xmin AND x<$xmax AND y>$ymin AND y<$ymax AND eventos.idDistritoPadre=territorios.id AND eventos.eventoActivo='1' ";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

function getLevels($provincia,$type)
// Gets a list of municipalities that are part of a province
{
    //sanitize inputs
    $link=connect();
    $provincia=safe($link, $provincia);
    $type=safe($link, $type);

    
    $sql="SELECT * FROM territorios WHERE
            nivel='$type' AND
            provincia='$provincia'";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        if($fila["id"]!=$lugarOriginal)
            array_push($returnData,array($fila["id"],$fila["nombre"],$fila["xcentroid"],$fila["ycentroid"],$fila["idPadre"]));
    return $returnData;
}


?>