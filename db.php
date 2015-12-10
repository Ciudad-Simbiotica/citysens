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


        $sql="SELECT * FROM places WHERE idPlace='{$fila['idPlace']}'";
        $result=mysqli_query($link, $sql);
        if($fila=mysqli_fetch_assoc($result))
        {
            $entidad['place']=$fila;
        }
        else
        {
            $entidad['place']['direccion']="Sin dirección";
            $entidad['place']['idPlace']="0";
            $entidad['place']['idCiudad']="0";
            $entidad['place']['idDistrito']="0";
            $entidad['place']['idBarrio']="0";            
            $entidad['place']['lat']=0;
            $entidad['place']['lng']=0;
            $entidad['place']['nombre']="Sin nombre";
            $entidad['place']['zoom']="15";
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

// Returns a list of entities that have events in a certain territory (set of territories, in special levels)
// TODO: It could be required to change to a collection of territories (for city+ and neighbourhood areas
function getEntidadesZonaConEventos($cadena,$idTerritorio,$alrededores,$cantidad=10)
{
  $link=connect();
  // Sanitize inputs
  $cadena=safe($link, $cadena);
  $cantidad=safe($link, filter_var($cantidad,FILTER_SANITIZE_NUMBER_INT));
  $idTerritorio=safe($link, $idTerritorio);
  $nivel=getNivelTerritorio($idTerritorio);
  $alrededores=safe($link,$alrededores);
  $lugares=array();
  
  mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT entidades.tipo, entidades.entidad, entidades.idEntidad, territorios.nombre
            FROM entidades 
            JOIN eventos
              ON entidades.idEntidad=eventos.idEntidad
            JOIN places 
              ON eventos.idPlace=places.idPlace
            JOIN territorios
              ON places.idCiudad=territorios.id             
           WHERE entidad LIKE '%$cadena%'";

    if ($nivel<8) // Levels above city, searches will be done on a city-basis
    {    
      $hijos=getDescendantsOfLevel($idTerritorio,8);
      $sql.=" AND places.idCiudad IN ('".join($hijos,"','")."')";
    }
    else if ($nivel==8) {
      $territorios=$idTerritorio;
      if ($alrededores!=0) {
        $territorios.=",".$alrededores;
      }
      $sql.=" AND places.idCiudad IN ($territorios)";      
    }
    else if ($nivel==9) // Map under city, searches done on a district neighborhood-basis
    {
        $hijos=getAllChildren(array($idTerritorio),9);
        $sql.=" AND places.idDistrito IN ('".join($hijos,"','")."')";
    }
    else { // Level 10, neighborhood
        $territorios=$idTerritorio;
        if ($alrededores!=0) {
          $territorios.=",".$alrededores;
        }
        $sql.=" AND places.idBarrio IN ($territorios)";  
    }

    $sql.=" GROUP BY entidades.idEntidad
            LIMIT 0,$cantidad";
    
// Ejemplo de query producida:
// SELECT *	FROM entidades 
//	JOIN eventos ON entidades.idEntidad=eventos.idEntidad
//  JOIN places ON eventos.idPlace=places.idPlace
//	WHERE entidad LIKE '%centro%' 
//    AND places.idDistrito IN ('901280005','901280006')    
//  GROUP BY entidades.idEntidad
//  LIMIT 0,10;

    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
}

// Gets the information about Entities to be displayed, taking into account all filters and   
function getEntidades($filtros, $idTerritorio, $alrededores, $itemsStart=0, $itemsLimit=500)
   // TODO: Code changed to allow including District filters from up to Comarca level.
   // 
   // Therefore, this piece of code (that assumes districts only applied on lower levels) does no longer work, needs fixing.
{
    $link=connect();
    
    // Sanitize inputs
    $itemsStart=safe($link, filter_var($itemsStart,FILTER_SANITIZE_NUMBER_INT));
    $itemsLimit=safe($link, filter_var($itemsLimit,FILTER_SANITIZE_NUMBER_INT));
    $idTerritorio=safe($link,$idTerritorio);
    $alrededores=safe($link,$alrededores);
    $nivel= getNivelTerritorio($idTerritorio);
    $nivelHijo=strval($nivel+1);
    $sinDireccion=safe($link,'[sin direccion]');

    $hayFiltroLugar=false;
    $busqueda=$tematica=$lugar="";
    $lugares=array();
    $filtrosHermanos=array();
    $filtrosHijos=array();
    $filtrosNietos=array();

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
               $hayFiltroLugar=true;
               if ($id[0]==$nivel[0]) { // Apaño para resolver el que nivel de barrio sea 10  TODO: Simplificar niveles
                  array_push($filtrosHermanos,$id);
               } elseif ($id[0]==$nivelHijo[0]) {
                  array_push($filtrosHijos,$id);
               } else {
                  array_push($filtrosNietos,$id);
               }
               break;
        }
    }

// Base query for entities that have an address defined
     $sql="SELECT entidades.*,places.direccion as domicilio, places.lng, places.lat, places.idComarca, places.idCiudad, places.idDistrito, places.idBarrio, territorios.nombre as nombreLugar, territorios.nombreCorto,
            (SELECT GROUP_CONCAT(tematicas.tematica)
               FROM entidades_tematicas, tematicas
              WHERE entidades_tematicas.idTematica=tematicas.idTematica
                AND entidades_tematicas.idEntidad = entidades.idEntidad) AS tematicas
           FROM entidades, entidades_tematicas,places, territorios
          WHERE entidades.idPlace=places.idPlace
            AND entidades.idEntidad=entidades_tematicas.idEntidad 
            AND ";
 
 // Base query for entities that have no address, but are assigned to a cityId or metropoliId
   $sql_2=" UNION
            SELECT entidades.*, '$sinDireccion',0,0,entidades.idComarca, entidades.idCiudad, 0, 0, territorios.nombre as nombreLugar, territorios.nombreCorto,
                  (SELECT GROUP_CONCAT(tematicas.tematica)
                     FROM entidades_tematicas, tematicas   
                     WHERE entidades_tematicas.idTematica=tematicas.idTematica
                     AND entidades_tematicas.idEntidad = entidades.idEntidad) AS tematicas
              FROM entidades, entidades_tematicas,territorios
             WHERE entidades.idPlace = 0
               AND ((entidades.idCiudad <> 0 AND territorios.nivel = 8 AND territorios.id = entidades.idCiudad)
                     OR
                    (entidades.idComarca <> 0 AND territorios.nivel = 7 AND territorios.id = entidades.idComarca))
               AND entidades.idEntidad=entidades_tematicas.idEntidad 
               AND ";
   // entidades.idCiudad contains the ID of the city where an Entity with no direction operates (idComarca=0 in this case)
   // entidades.idComarca contains the ID of the metropoli where an Entity with no direction operates (idCiudad=0 in this case)
   
   // In case there is no territory filter, the base territory (+ neighbour territories if surroundings are shown) are used
   if (!$hayFiltroLugar) {
    $lugares[]=$idTerritorio;
    if ($alrededores!=0) {
      $lugares=array_merge($lugares,explode(',',$alrededores));
    }
  }  

  if ($nivel<6) {
      // Levels above province, searches will be done on a city-basis for with address case, comarca and city Id for no address case    
      $sql.=" places.idCiudad=territorios.id AND "; // TODO: Problem: we are returning name of City/Great District, but for metropolis we would need Comarca/Metropoli name
      if ($hayFiltroLugar) {
         $lugares=array_merge($filtrosHijos,$filtrosNietos);
      }
      $comarcas=getAllDescendantsOfLevel($lugares,7);
      $ciudades=getAllDescendantsOfLevel($lugares,8);
      $lugar="places.idComarca IN ('".join($comarcas,"','")."')";
      $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($comarcas,"','")."')";
  } elseif ($nivel==6) {
      // Level province, searches will be done on a city-basis for with address case, comarca and city Id for no address case    
      $sql.=" places.idCiudad=territorios.id AND "; // TODO: We are returning name of City/Great District; for metropolis it could be better Comarca/Metropoli name. But it is not horrible
      if ($hayFiltroLugar) {
         $lugares=array_merge($filtrosHijos,$filtrosNietos);
      }
      $comarcas=getAllDescendantsOfLevel($lugares,7);
      $ciudades=getAllDescendantsOfLevel($lugares,8);
      $lugar="places.idCiudad IN ('".join($ciudades,"','")."')";
      $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($comarcas,"','")."')";
  } elseif ($nivel==7 && $alrededores!=0) {
      // Map at a Comarca and surroundings level, searches will be done on a city- and district- basis for with adresses case, comarca and city Id for no address case
      $sql.=" places.idCiudad=territorios.id AND "; // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( OR places.idComarca IN ('".join($filtrosHermanos,"','")."') OR places.idCiudad IN ('".join($filtrosHijos,"','")."') OR places.idDistrito IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHijos)>0 || count($filtrosHermanos)>0) {
            $ciudades=getAllDescendantsOfLevel($filtrosHermanos,8);
            $lugar_2="territorios.id IN ('".join($filtrosHermanos,"','")."','".join($filtrosHijos,"','")."','".join($ciudades,"','")."')";
         }
      } else {
         $lugar="places.idComarca IN ('".join($lugares,"','")."')";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }
  } elseif ($nivel==7) {
      // Map at a comarca level, searches will be done on a city- and district- basis for with adresses case, comarca and city Id for no address case
      $sql.=" places.idCiudad=territorios.id AND "; // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idCiudad IN ('".join($filtrosHijos,"','")."') OR places.idDistrito IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHijos)>0) {
            $lugar_2="territorios.id IN ('".join($filtrosHijos,"','")."')";
         }
      } else {
         $lugar="places.idComarca = $idTerritorio ";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }        
  } elseif ($nivel==8 && $alrededores!=0) { 
      // Map at City and surroundings level, searches based on City, District and Neighbourhood level for "with addres" case, cityId for "no address" case
      $sql.=" places.idCiudad=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idCiudad IN ('".join($filtrosHermanos,"','")."') OR places.idDistrito IN ('".join($filtrosHijos,"','")."') OR places.idBarrio IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHermanos)>0) {
            $lugar_2="territorios.id IN ('".join($filtrosHermanos,"','")."')";
         }
      } else {
         $lugar="places.idCiudad IN ('".join($lugares,"','")."')";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }            
  } elseif ($nivel==8) { 
      //Map at city level, searches based on District and Neighbourhood level for "with address" case, cityId for "no address" case 
      $sql.=" places.idDistrito=territorios.id AND "; // District name will be displayed
      if ($hayFiltroLugar) {
         $lugar="( places.idDistrito IN ('".join($filtrosHijos,"','")."') OR places.idBarrio IN ('".join($filtrosNietos,"','")."') )";       
      } else {
         $lugar="places.idCiudad = $idTerritorio ";
         $lugar_2="territorios.id = $idTerritorio ";         
      }
  } elseif ($nivel==9 && $alrededores!=0) { 
      // Map at District and surroundings level, searches based on District and Neighbourhood level for "with addres" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idDistrito=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idDistrito IN ('".join($filtrosHermanos,"','")."') OR places.idBarrio IN ('".join($filtrosHijos,"','")."') )";
      } else {
         $lugar="places.idDistrito IN ('".join($lugares,"','")."')";
      }            
  } elseif ($nivel==9) { 
      //Map at district level, searches done on district, neighborhood basis for "with address" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND "; //Neighborhood name will be displayed
      if ($hayFiltroLugar) {
         $lugar="( places.idBarrio IN ('".join($filtrosHijos,"','")."') )";       
      } else {
         $lugar="places.idDistrito = $idTerritorio ";         
      }
   } elseif ($nivel==10 && $alrededores!=0)  { 
      // Map at Neighborhood and surroundings level, search done on idBarrio basis for "with addres" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idBarrio IN ('".join($filtrosHermanos,"','")."') )";
      } else {
         $lugar="places.idBarrio IN ('".join($lugares,"','")."')";
      }            
  } else { 
      //Map at neighborhood level, searches done on idBarrio basis for "with address" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND "; //Neighborhood name will be displayed
      // There cannot be any Territory Filter
      $lugar="places.idDistrito = $idTerritorio ";
   }
               
    if($busqueda!="") {
        $sql.="($busqueda) AND ";
        $sql_2.="($busqueda) AND ";
    }
    if($tematica!="") {
        $sql.="($tematica) AND ";
        $sql_2.="($tematica) AND ";
    }

    $sql.="($lugar) ";    
    if ($nivel<=8 && $lugar_2) { // The second SQL is only used in case of search based on idCiudad
       $sql_2.="($lugar_2) ";
       $sql.=$sql_2;
    }
//    $sql.=" GROUP BY entidades.idEntidad ORDER BY points DESC LIMIT $itemsStart,$itemsLimit";
    $sql.=" GROUP BY entidades.idEntidad ORDER BY points DESC LIMIT $itemsStart,800";
      
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

/* This function creates an entity and its associated "tematic" assignments 
      $entityData is an array that provides all required data */
function createEntity($entityData)
{
    $link=connect();
    //Sanitize inputs
    
    $entidad = safe($link, $entityData["entidad"]);
    $nombreCorto = safe($link, $entityData["nombreCorto"]);
    $tipo = safe($link, $entityData["tipo"]);
    $idCiudad = safe($link, $entityData["idCiudad"]);
    $idComarca = safe($link, $entityData["idComarca"]);
    $idPlace = safe($link, $entityData["idPlace"]);
    $telefono = safe($link, $entityData["telefono"]);
    $email = safe($link, $entityData["email"]);
    $points = safe($link, $entityData["points"]);
    $url = safe($link, $entityData["url"]);
    $twitter = safe($link, $entityData["twitter"]);
    $facebook = safe($link, $entityData["facebook"]);
    $etiquetas = safe($link, $entityData["etiquetas"]);
    $descBreve = safe($link, $entityData["descBreve"]);
    $texto = safe($link, $entityData["texto"]);
    $fechaConstitucion = safe($link, $entityData["fechaConstitucion"]);
    $tematicas=array();
    foreach($entityData["tematicas"] as $tematica)
        array_push($tematicas,safe($link, $tematica));

   //    INSERT INTO `entidades` (`entidad`, `nombreCorto`, `tipo`, `idCiudad`, `idComarca`, `idPlace`, `telefono`, `email`, `points`, `url`, `twitter`, `facebook`, `etiquetas`, `descBreve`, `texto`, `fechaConstitucion`, `created`, `updated`) VALUES
   //    ('Asociación Gallega Corredor del Henares', '', 'organizacion', '801280005', `0`, 869, '670588667', 'galiciahenares@hotmail.com', 0, '', '', '', '', '', '', NULL, '2015-07-15 08:01:34', '2015-07-27 10:20:36'),
    
    mysqli_query($link, 'SET CHARACTER SET utf8');

    $sql="INSERT INTO entidades (entidad, nombreCorto, tipo, idCiudad, idComarca, idPlace, telefono, email, points, 
                                  url, twitter, facebook, etiquetas, descBreve, texto, fechaConstitucion, created)
                       VALUES ('$entidad','$nombreCorto','$tipo','$idCiudad','$idComarca','$idPlace','$telefono','$email','$points',
                                '$url','$twitter','$facebook','$etiquetas','$descBreve','$texto','$fechaConstitucion', NULL)";
    mysqli_query($link, $sql);

    $idEntidad=mysqli_insert_id($link);
    $sql="INSERT INTO entidades_tematicas (idEntidad, idTematica) VALUES ";
    $firstTematica=true;
    if (count($tematicas)>0) {
       foreach($tematicas as $tematica) {
          if ($firstTematica) {
             $sql.=" ('$idEntidad', '$tematica')";
             $firstTematica=false;
          }
          else
             $sql.=", ('$idEntidad','$tematica')";
       }
    } else
       $sql.=" ('$idEntidad','38')"; // Assign topic "Others", for those cases with no tematic
   
    mysqli_query($link, $sql);

    return $idEntidad;
}

/* This function creates an event and its associated "tematic" assignments 
      $eventData is an array that provides all required data */
function createEvent($eventData)
{
    $link=connect();
    //Sanitize inputs
    $fecha=safe($link, $eventData["fecha"]);
    $fechaFin=safe($link, $eventData["fechaFin"]);
    
    if($fechaFin=="")
        $fechaFin='NULL';
    else
        $fechaFin="'$fechaFin'";
    $clase=safe($link, $eventData["clase"]);
    $tipo=safe($link, $eventData["tipo"]);
    $titulo=safe($link, $eventData["titulo"]);
    $texto=safe($link, $eventData["texto"]);
    $idEntidad=safe($link, $eventData["idEntidad"]);
    $temperatura=safe($link, $eventData["temperatura"]);
    $tematicas=array();
    foreach($eventData["tematicas"] as $tematica)
        array_push($tematicas,safe($link, $tematica));
    $idPlace=safe($link, $eventData["idPlace"]);
    $idCiudad=safe($link, $eventData["idCiudad"]);
    $idComarca=safe($link, $eventData["idComarca"]);
    $detalleDireccion=safe($link, $eventData["detalleDireccion"]);
    $url=safe($link, filter_var($eventData["url"], FILTER_SANITIZE_URL));
    $email=safe($link, filter_var($eventData["email"], FILTER_SANITIZE_EMAIL));
    $etiquetas=safe($link, $eventData["etiquetas"]);
    $organizador=safe($link, $eventData["organizador"]);
    $repeatsAfter=safe($link, $eventData["repeatsAfter"]);
    $eventoActivo=safe($link, $eventData["eventoActivo"]);

   //    INSERT INTO `eventos` (`idEvento`, `fecha`, `fechaFin`, `clase`, `tipo`, `titulo`, `texto`, `temperatura`, `idEntidad`, `idPlace`, `url`, `email`, `etiquetas`, `repeatsAfter`, `eventoActivo`) VALUES
   //    (667, '2014-05-27 20:00:00', NULL, 'eventos', 'convocatoria', 'Bicicrítica Torrejón ¡Usa la bici todos los días, celébralo una vez al mes!', 'Bicicrítica Torrejón ¡Usa la bici todos los días, celébralo una vez al mes!', 1, 31, 266, NULL, NULL, '', 0, 1),

    
    mysqli_query($link, 'SET CHARACTER SET utf8');

    $sql="INSERT INTO eventos (fecha,fechaFin,clase,tipo,titulo,texto,temperatura,
                                idEntidad,idComarca,idCiudad,detalleDireccion,
                                idPlace,url,email,etiquetas,organizador,repeatsAfter,eventoActivo,created)
                       VALUES ('$fecha',$fechaFin,'$clase','$tipo','$titulo','$texto','$temperatura',$idEntidad,
                                $idComarca, $idCiudad,'$detalleDireccion', $idPlace,
                                '$url','$email','$etiquetas','$organizador','$repeatsAfter',$eventoActivo,NULL)";
    mysqli_query($link, $sql);
    $idEvento=mysqli_insert_id($link);
    $sql="INSERT INTO eventos_tematicas (idEvento, idTematica) VALUES ";
    $firstTematica=true;
    if (count($tematicas)>0) {
       foreach($tematicas as $tematica) {
          if ($firstTematica) {
             $sql.=" ('$idEvento', '$tematica')";
             $firstTematica=false;
          }
          else
             $sql.=", ('$idEvento','$tematica')";
       }
    } else
       $sql.=" ('$idEvento','38')"; // Assign topic "Others", for those cases with no tematic
   
    mysqli_query($link, $sql);

    return $idEvento;
}

function updateEvent($eventData)
{
    $link=connect();
    //Sanitize inputs
    $fecha=safe($link, $eventData["fecha"]);
    $fechaFin=safe($link, $eventData["fechaFin"]);
    
    if($fechaFin=="")
        $fechaFin='NULL';
    else
        $fechaFin="'$fechaFin'";
    $clase=safe($link, $eventData["clase"]);
    // SI NO ESTÁ DEFINIDO TIPO, no se considera para el update.
    // Tal vez sería mejor construir el sql al vuelo teniendo en cuenta sólo lo que se ha enviado.
    if (isset($eventData["tipo"])) {
      $tipo=safe($link, $eventData["tipo"]);
    }
    $titulo=safe($link, $eventData["titulo"]);
    $texto=safe($link, $eventData["texto"]);
    $lugar=safe($link, $eventData["lugar"]);
    $idEntidad=safe($link, $eventData["idEntidad"]);
    $temperatura=safe($link, $eventData["temperatura"]);
    $tematicas=array();
    foreach($eventData["tematicas"] as $tematica)
        array_push($tematicas,safe($link, $tematica));
    $idPlace=safe($link, $eventData["idPlace"]);
    $idCiudad=safe($link, $eventData["idCiudad"]);
    $idComarca=safe($link, $eventData["idComarca"]);
    $detalleDireccion=safe($link, $eventData["detalleDireccion"]);
    $url=safe($link, filter_var($eventData["url"], FILTER_SANITIZE_URL));
    $email=safe($link, filter_var($eventData["email"], FILTER_SANITIZE_EMAIL));
    $etiquetas=safe($link, $eventData["etiquetas"]);
    $organizador=safe($link, $eventData["organizador"]);
    $repeatsAfter=safe($link, $eventData["repeatsAfter"]);
    $eventoActivo=safe($link, $eventData["eventoActivo"]);

    $idEvento=safe($link,$eventData["idEvento"]);   

    mysqli_query($link, 'SET CHARACTER SET utf8');

    // Campo "tipo"
    $sql="UPDATE eventos SET fecha='$fecha',fechaFin=$fechaFin,clase='$clase',titulo='$titulo',texto='$texto', 
            temperatura='$temperatura',idEntidad=$idEntidad,idComarca=$idComarca,
            idCiudad=$idCiudad,detalleDireccion='$detalleDireccion',idPlace= $idPlace,url='$url',email='$email',
            etiquetas='$etiquetas',organizador='$organizador',repeatsAfter='$repeatsAfter',eventoActivo=$eventoActivo";
    if (isset($eventData["tipo"])) {
       $sql.=",tipo='$tipo'";
    } 
    $sql.="WHERE idEvento=$idEvento";
             
    mysqli_query($link, $sql);

    $sql="DELETE FROM eventos_tematicas WHERE idEvento='$idEvento'";
    mysqli_query($link, $sql);
    
    $sql="INSERT INTO eventos_tematicas (idEvento, idTematica) VALUES ";
    if (count($tematicas)>0) {
       $firstTematica=true;
       foreach($tematicas as $tematica) {
          if ($firstTematica) {
             $sql.=" ('$idEvento', '$tematica')";
             $firstTematica=false;
          } else
             $sql.=", ('$idEvento','$tematica')";
       }
    } else
       $sql.=" ('$idEvento','38')"; // Assign topic "Others", for those cases with no tematic
   
    mysqli_query($link, $sql);
}

function getEvento($idEvento)
{
    //sanitize input
    $link=connect();
    $idEvento=safe($link, $idEvento);
    
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $sql="SELECT * FROM eventos WHERE idEvento='$idEvento' AND eventos.eventoActivo='1'";
    $result=mysqli_query($link, $sql);
    if($evento=mysqli_fetch_assoc($result))
    {
        $sql="SELECT * FROM places WHERE idPlace='{$evento['idPlace']}'";
        $result=mysqli_query($link, $sql);
        if($place=mysqli_fetch_assoc($result))
        {
            $evento['place']=$place;
        }
        else
        {
            $evento['place']['direccion']="Sin dirección";
            $evento['place']['idPlace']="0";
            $evento['place']['idPadre']="0";   // idPadre? TODO: Review
            $evento['place']['lat']=0;
            $evento['place']['lng']=0;
            $evento['place']['nombre']="Sin nombre";
            $evento['place']['zoom']="15";
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

// Main function to get the list of events, considering filters applied and other parameters
// By default is returns a maximum of 50 events, showing events between today and the next complete weekend 
// (ie: in a Friday the whole next week is included) 
function getEventos($filtros,$idTerritorio,$alrededores,$itemsStart=0, $itemsLimit=50)
// TODO: Code changed to allow including District filters from up to example, Comarca level. Therefore, this does no longer work, needs fixing.
{
    $link=connect();
    //Sanitize inputs
    $itemsStart=safe($link, filter_var($itemsStart, FILTER_SANITIZE_NUMBER_INT));
    $itemsLimit=safe($link, filter_var($itemsLimit, FILTER_SANITIZE_NUMBER_INT));
    $idTerritorio=safe($link,$idTerritorio);
    $alrededores=safe($link,$alrededores);
    $nivel= getNivelTerritorio($idTerritorio);
    $nivelHijo=strval($nivel+1);
    
    $hayFiltroLugar=false;
    $busqueda=$tematica=$lugar=$organizacion=$tiempo="";
    $startDate=$endDate=0;
    $lugares=array();
    $filtrosHermanos=array();
    $filtrosHijos=array();
    $filtrosNietos=array();

    foreach($filtros as $filtro)
    {
        $tipo=safe($link,$filtro["tipo"]);
        $texto=safe($link,$filtro["texto"]);
        $id=safe($link,$filtro["id"]);
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
               $hayFiltroLugar=true;
               if ($id[0]==$nivel[0]) { // Apaño para resolver el que nivel de barrio sea 10  TODO: Simplificar niveles
                  array_push($filtrosHermanos,$id);
               } elseif ($id[0]==$nivelHijo[0]) {
                  array_push($filtrosHijos,$id);
               } else {
                  array_push($filtrosNietos,$id);
               }
               break;
            case "organizacion":
            case "institucion":
            case "colectivo":
                if($organizacion!="")
                    $organizacion.=" OR ";
                $organizacion.="idEntidad='$id'";
                break;
            case "tiempo":
                $startDate=safe($link,$filtro["start"]);
                
                if ($startDate=="") {
                   $startDate=date('Y-m-d');
                }
                $endDate=safe($link,$filtro["end"]);
                if ($endDate=="") {
                   $endDate=new DateTime($startDate);
                   $endDate->modify('next Friday + 10 days');
                   $endDate=$endDate->format('Y-m-d');
                }
                if ($tiempo!="")
                   $tiempo.=" OR ";
                $tiempo.="(eventos.fecha>'".$startDate."' AND eventos.fecha<'".$endDate."')";
                break;
        }
    }
    
    if ($tiempo=="") {
       $startDate=date('Y-m-d');

       $endDate=new DateTime($startDate);
       $endDate->modify('next Friday + 10 days');
       $endDate=$endDate->format('Y-m-d');
       $tiempo="eventos.fecha>'".$startDate."' AND eventos.fecha<'".$endDate."'";
    }
    
    // TODO: the query could be optimised if ", eventos_tematicas" and "eventos.idEntidad=entidades_tematicas.idEntidad" only where included in case there was a thematic filter.
    // The $sql strings would need to be divided in two. Currently... it is not worth it.
    
    // Base query for events that have an address defined
     $sql="SELECT eventos.*, places.lng, places.lat, places.idComarca, places.idCiudad, places.idDistrito, places.idBarrio, territorios.nombre as nombreLugar, territorios.nombreCorto,
            (SELECT GROUP_CONCAT(tematicas.tematica)
               FROM eventos_tematicas, tematicas
              WHERE eventos_tematicas.idTematica=tematicas.idTematica
                AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
           FROM eventos, eventos_tematicas, places, territorios
          WHERE eventos.eventoActivo=1
            AND eventos.idPlace=places.idPlace
            AND eventos.idEvento=eventos_tematicas.idEvento  
            AND ";
 
     
 // Base query for entities that have no address, but are assigned to a cityId or metropoliId
   $sql_2=" UNION
            SELECT eventos.*,0,0,eventos.idComarca,eventos.idCiudad, 0, 0, territorios.nombre as nombreLugar, territorios.nombreCorto,
                  (SELECT GROUP_CONCAT(tematicas.tematica)
                     FROM eventos_tematicas, tematicas   
                     WHERE eventos_tematicas.idTematica=tematicas.idTematica
                     AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
              FROM eventos, eventos_tematicas, territorios
             WHERE eventos.eventoActivo=1
               AND eventos.idPlace = 0
               AND ((eventos.idCiudad <> 0 AND territorios.nivel = 8 AND territorios.id = eventos.idCiudad)
                     OR
                    (eventos.idComarca <> 0 AND territorios.nivel = 7 AND territorios.id = eventos.idComarca))
               AND eventos.idEvento=eventos_tematicas.idEvento 
               AND ";
   // eventos.idCiudad contains the ID of the city where an Entity with no direction operates (idComarca=0 in this case)
   // enventos.idComarca contains the ID of the metropoli where an Entity with no direction operates (idCiudad=0 in this case)
   
//TODO: Por qué forzamos que exista una temática? (Podría haber eventos sin una temática clara - aunque podría definirse como: otros)

      // In case there is no territory filter, the base territory (+ neighbour territories if surroundings are shown) are used
   if (!$hayFiltroLugar) {
      $lugares[]=$idTerritorio;
      if ($alrededores!=0) {
         $lugares=array_merge($lugares,explode(',',$alrededores));
      }
   }
          
  if ($nivel<6) {
      // Levels above province, searches will be done on a city-basis for with address case, comarca and city Id for no address case    
      $sql.=" places.idCiudad=territorios.id AND "; // TODO: Problem: we are returning name of City/Great District, but for metropolis we would need Comarca/Metropoli name
      if ($hayFiltroLugar) {
         $lugares=array_merge($filtrosHijos,$filtrosNietos);
      }
      $comarcas=getAllDescendantsOfLevel($lugares,7);
      $ciudades=getAllDescendantsOfLevel($lugares,8);
      $lugar="places.idComarca IN ('".join($comarcas,"','")."')";
      $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($comarcas,"','")."')";
  } elseif ($nivel==6) {
      // Level province, searches will be done on a city-basis for with address case, comarca and city Id for no address case    
      $sql.=" places.idCiudad=territorios.id AND "; // TODO: We are returning name of City/Great District; for metropolis it could be better Comarca/Metropoli name. But it is not horrible
      if ($hayFiltroLugar) {
         $lugares=array_merge($filtrosHijos,$filtrosNietos);
      }
      $comarcas=getAllDescendantsOfLevel($lugares,7);
      $ciudades=getAllDescendantsOfLevel($lugares,8);
      $lugar="places.idCiudad IN ('".join($ciudades,"','")."')";
      $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($comarcas,"','")."')";
  } elseif ($nivel==7 && $alrededores!=0) {
      // Map at a Comarca and surroundings level, searches will be done on a city- and district- basis for with adresses case, comarca and city Id for no address case
      $sql.=" places.idCiudad=territorios.id AND "; // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( OR places.idComarca IN ('".join($filtrosHermanos,"','")."') OR places.idCiudad IN ('".join($filtrosHijos,"','")."') OR places.idDistrito IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHijos)>0 || count($filtrosHermanos)>0) {
            $ciudades=getAllDescendantsOfLevel($filtrosHermanos,8);
            $lugar_2="territorios.id IN ('".join($filtrosHermanos,"','")."','".join($filtrosHijos,"','")."','".join($ciudades,"','")."')";
         }
      } else {
         $lugar="places.idComarca IN ('".join($lugares,"','")."')";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }
  } elseif ($nivel==7) {
      // Map at a comarca level, searches will be done on a city- and district- basis for with adresses case, comarca and city Id for no address case
      $sql.=" places.idCiudad=territorios.id AND "; // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idCiudad IN ('".join($filtrosHijos,"','")."') OR places.idDistrito IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHijos)>0) {
            $lugar_2="territorios.id IN ('".join($filtrosHijos,"','")."')";
         }
      } else {
         $lugar="places.idComarca = $idTerritorio ";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }        
  } elseif ($nivel==8 && $alrededores!=0) { 
      // Map at City and surroundings level, searches based on City, District and Neighbourhood level for "with addres" case, cityId for "no address" case
      $sql.=" places.idCiudad=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idCiudad IN ('".join($filtrosHermanos,"','")."') OR places.idDistrito IN ('".join($filtrosHijos,"','")."') OR places.idBarrio IN ('".join($filtrosNietos,"','")."') )";
         if (count($filtrosHermanos)>0) {
            $lugar_2="territorios.id IN ('".join($filtrosHermanos,"','")."')";
         }
      } else {
         $lugar="places.idCiudad IN ('".join($lugares,"','")."')";
         $ciudades=getAllDescendantsOfLevel($lugares,8);
         $lugar_2="territorios.id IN ('".join($ciudades,"','")."','".join($lugares,"','")."')";
      }            
  } elseif ($nivel==8) { 
      //Map at city level, searches based on District and Neighbourhood level for "with address" case, cityId for "no address" case 
      $sql.=" places.idDistrito=territorios.id AND "; // District name will be displayed
      if ($hayFiltroLugar) {
         $lugar="( places.idDistrito IN ('".join($filtrosHijos,"','")."') OR places.idBarrio IN ('".join($filtrosNietos,"','")."') )";       
      } else {
         $lugar="places.idCiudad = $idTerritorio ";
         $lugar_2="territorios.id = $idTerritorio ";         
      }
  } elseif ($nivel==9 && $alrededores!=0) { 
      // Map at District and surroundings level, searches based on District and Neighbourhood level for "with addres" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idDistrito=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idDistrito IN ('".join($filtrosHermanos,"','")."') OR places.idBarrio IN ('".join($filtrosHijos,"','")."') )";
      } else {
         $lugar="places.idDistrito IN ('".join($lugares,"','")."')";
      }            
  } elseif ($nivel==9) { 
      //Map at district level, searches done on district, neighborhood basis for "with address" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND "; //Neighborhood name will be displayed
      if ($hayFiltroLugar) {
         $lugar="( places.idBarrio IN ('".join($filtrosHijos,"','")."') )";       
      } else {
         $lugar="places.idDistrito = $idTerritorio ";         
      }
   } elseif ($nivel==10 && $alrededores!=0)  { 
      // Map at Neighborhood and surroundings level, search done on idBarrio basis for "with addres" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND ";   // name of city/great district returned
      if ($hayFiltroLugar) {
         $lugar="( places.idBarrio IN ('".join($filtrosHermanos,"','")."') )";
      } else {
         $lugar="places.idBarrio IN ('".join($lugares,"','")."')";
      }            
  } else { 
      //Map at neighborhood level, searches done on idBarrio basis for "with address" case; No "no address" displayed, as they are linked to Comarca or City
      $sql.=" places.idBarrio=territorios.id AND "; //Neighborhood name will be displayed
      // There cannot be any Territory Filter
      $lugar="places.idDistrito = $idTerritorio ";
   }
        
    $sql.="($tiempo) AND ";
    $sql_2.="($tiempo) AND";
    if($busqueda!="") {
        $sql.="($busqueda) AND ";
        $sql_2.="($busqueda) AND ";
    }
    if($tematica!="") {
        $sql.="($tematica) AND ";
        $sql_2.="($tematica) AND ";
    }
    if($organizacion!="") {
       $sql.="($organizacion) AND ";
       $sql_2.="($organizacion) AND ";
    }
    $sql.="($lugar) ";    
    if ($nivel<=8 && $lugar_2) { // The second SQL is only used in case of search based on idCiudad
       $sql_2.="($lugar_2) ";
       $sql.=$sql_2;
    }
    
    $sql.=" GROUP BY eventos.idEvento ORDER BY fecha ASC LIMIT $itemsStart,$itemsLimit";
    
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    {
        unset($fila["texto"]);  // Unset to reduce the size of the data transferred, as it was not shown in lists. If it would, it would need to be included.
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
                 places.nombre as nombreDireccion,
                 places.direccion as direccion,
                 places.placeStatus as placeStatus,
                 (SELECT GROUP_CONCAT(tematicas.tematica)
                 FROM eventos_tematicas, tematicas
                 WHERE eventos_tematicas.idTematica=tematicas.idTematica 
                 AND eventos_tematicas.idEvento = eventos.idEvento) AS tematicas
            FROM eventos, places, territorios, entidades
            WHERE eventoActivo=0
            AND eventos.idPlace=places.idPlace 
            AND places.idPadre=territorios.id 
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


function getChildren ($idTerritorio, $nivelBase=5, $nivelDestino=10)
{
    $territorios=array();
    array_push($territorios,$idTerritorio);
    return getAllChildren ($territorios,$nivelBase, $nivelDestino);
}


// Returns all descendants of a list of territories, with levels from $nivelBase to $nivelFinal
// $nivelBase initialised to 5 (country) by default, to cover all territories.
// $nivelFinal initialised to 10 (neighborhood) by default, to cover all territories.

function getAllChildren($territorios, $nivelBase=5, $nivelFinal=10)
{
    $link=connect();
    for($nivel=$nivelBase;$nivel<=$nivelFinal;$nivel++)
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
    
    while($idPadre!=0)
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

// Returns "Nivel" (level) of the territory with id "idTerritorio" 
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

// Returns parent territory ID (idPadre) of the territory with id "idTerritorio" 
function getParentID($idTerritorio)
{
    //Sanitize input
    $link=connect();
    $idTerritorio=safe($link, $idTerritorio);  
    
    $sql="SELECT idPadre 
            FROM  territorios 
            WHERE id='$idTerritorio'";

    $result=mysqli_query($link, $sql);
    $fila=mysqli_fetch_assoc($result);
    return $fila["idPadre"];
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

// Returns max min coordinates from the surrounding territories of an idLugar. 
function getCoordenadasVecinos($type,$vecinos)
{
    $link=connect();
    //sanitize inputs
    $type=safe($link, $type);
    $vecinos=safe($link, $vecinos);


    $sql="SELECT min(xmin) as xmin, max(xmax) as xmax, min(ymin) as ymin, max(ymax) as ymax FROM territorios WHERE
            nivel='$type'
            AND id IN ($vecinos)";

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
    $nivel=$fila["nivel"];
    if ($nivel!=8 && (!isset($descendiente) || ($descendiente!=0 && $descendiente!=2))) // In case it is not city level and it is mono-child
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

//Is this function used/needed?
function getLugares($cadena,$territorioOriginal,$nivel,$cantidad=3,$inSet=array())
{
    //Sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $territorioOriginal=safe($link, $territorioOriginal);
    $nivel=safe($link, $nivel);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));
    $sql="SELECT * FROM territorios WHERE
            nivel='$nivel' AND
            provincia=28 AND
            nombre LIKE '%$cadena%' AND
            id<>'$territorioOriginal' ORDER BY nombre";
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

function getTerritoriosSuggestions($cadena,$idTerritorio,$alrededores,$cantidad=4)
{
    //Sanitize input
    $link=connect();
    $cadena=safe($link, $cadena);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));
    $idTerritorio=safe($link, $idTerritorio);
    $alrededores=safe($link,$alrededores);
    $lugares=array();
    
    $nivel=getNivelTerritorio($idTerritorio);
    $lugares[]=$idTerritorio;

    if ($alrededores!=0) { // When in surroundings navigation, elements of level $nivel are shown. 
      $nivelHijos=$nivel;
      $nivelNietos=$nivel+1;
      $hijos=array_merge($lugares,explode(',',$alrededores));
    } else { 
      $nivelHijos=$nivel+1;
      $nivelNietos=$nivel+2;
      $hijos=getAllDescendantsOfLevel($lugares,$nivelHijos,$nivelHijos);
    }
    
    $sql="SELECT id, nombre, '' as nombreCortoPadre
            FROM territorios
           WHERE nivel = $nivelHijos
             AND id IN (".implode(",",$hijos).")
             AND nombre LIKE '%$cadena%' ";
    
    if ($nivelNietos<=10) {
      $nietos=getAllDescendantsOfLevel($hijos,$nivelNietos, $nivelNietos);
      // idDescendiente < 3 : We exclude those children that only have a child (as they would appear twice)
      $sql.=" AND idDescendiente < 3
          UNION
          SELECT t.id, t.nombre, t2.nombreCorto
            FROM territorios t, territorios t2
           WHERE t.nivel = $nivelNietos
             AND t.id IN (".implode(",",$nietos).")
             AND t.nombre LIKE '%$cadena%'
             AND t.idPadre = t2.id ";
    }
    $sql.=" ORDER BY nombre
            LIMIT 0,$cantidad";
    // Little BUG: a nieto with no brothers (eg: a city with one only district) is shown together with the short name of the father. Eg: "Torres de la Alameda (Torres)". It is not that serious.

    //echo $sql;

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
        array_push($returnData,$fila);
    return $returnData;
    
}

function getIrA($cadena,$lugarOriginal)
{
    //echo $lugarOriginal;      
    //Sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $lugarOriginal=safe($link, $lugarOriginal);
    //Datos del idProvincia
    $datosLugar=getDatosLugar($lugarOriginal);
    $nivel= $datosLugar["nivel"];
                      
    if ($nivel>5)
    {
        if ($nivel==6)    
            $idProvincia=$lugarOriginal;
        else if  ($nivel==7)
            $idProvincia=$datosLugar['idPadre'];
        else
        {
            $ancestors=getAllAncestors($lugarOriginal);
            $idProvincia=$ancestors["6"]["id"];
            
            $idCiudad=$ancestors["8"]["id"];
            $inSet=getAllChildren(array($idCiudad));

            $sqlCity="OR id IN (".implode(",",$inSet).") ";           
        }
        $sqlRegion="OR idPadre=$idProvincia"; // OR (nivel=7 AND idPadre=$idProvincia)"
    }           
        $sql="SELECT id, nombre, activo, 1 as flag 
                FROM territorios 
                WHERE nombre LIKE '$cadena%' AND "
                . "(nivel<7 "
                . "OR nivel=8 )"
      . " UNION SELECT id, nombre, activo, 2 as flag 
                FROM territorios 
                WHERE nombre LIKE '%$cadena%' AND "
                . "(nivel<7 "
                . "OR nivel=8 "
                . "$sqlRegion "
                . "$sqlCity )"
                . "ORDER BY flag, nombre, activo DESC, id DESC "
                . "LIMIT 0,1";

   
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

function getPlaceSuggestions($cadena,$idTerritorio,$cantidad=5)
{

    //sanitize inputs
    $link=connect();
    $cadena=safe($link, $cadena);
    $idTerritorio=safe($link, $idTerritorio);
    $cantidad=safe($link, filter_var($cantidad, FILTER_SANITIZE_NUMBER_INT));

    //echo $lugarOriginal;
    $inSet=getAllChildren(array($idTerritorio));
    
    //unset($inSet[0]);   //Quitamos el original
    $sql="SELECT * FROM places WHERE 
            (nombre LIKE '%$cadena%' OR direccion LIKE '%$cadena%') AND
            idPadre IN (".implode(",",$inSet).")  AND places.placeStatus='1' 
            LIMIT 0,$cantidad"; // Needs to be updated (idPadre)
    //echo $sql;
    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result))
    {
        if($fila["nombre"]==="")
            $fila["nombre"]="Dirección";
        array_push($returnData,array($fila["idPlace"],$fila["nombre"],$fila["direccion"],$fila["lat"],$fila["lng"],$fila["zoom"]));
    }
    return $returnData;

}

function getDistritoPadreDireccion($idPlace) 
//TODO: Needs to be reviewed and updated (idPadre y dirección -> place)??
{
    $link=connect();
    $idPlace=safe($link, $idPlace);

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM places WHERE 
                    idPlace='$idPlace'";
   
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

function getDireccion($idPlace)
{
    //Sanitize input
    $link=connect();
    $idPlace=safe($link, $idPlace);

    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="SELECT * FROM places WHERE idPlace='$idPlace'";
    $result=mysqli_query($link, $sql);
    return mysqli_fetch_assoc($result);
}

function createPlace($placeData)
{
    //Parameter were $nombreLugar,$direccion,$lat,$lng,$idPadre

   $link=connect();
   mysqli_query($link, 'SET CHARACTER SET utf8');

   // Sanitize inputs
   $placeData['idComarca']=safe($link,$placeData['idComarca']);
   $placeData['idCiudad']=safe($link,$placeData['idCiudad']);
   $placeData['idDistrito']=safe($link,$placeData['idDistrito']);
   $placeData['idBarrio']=safe($link,$placeData['idBarrio']);
   $placeData['nombre']=safe($link,$placeData['nombre']);
   $placeData['direccion']=safe($link,$placeData['direccion']);
   $placeData['indicacion']=safe($link,$placeData['indicacion']);
   $placeData['cp']=safe($link,$placeData['cp']);
   $placeData['lat']=safe($link,$placeData['lat']);
   $placeData['lng']=safe($link,$placeData['lng']);
   $placeData['zoom']=safe($link,$placeData['zoom']);
   $placeData['placeStatus']=safe($link,$placeData['placeStatus']);
   
   $sql="INSERT INTO places (idComarca, idCiudad, idDistrito, idBarrio, nombre, direccion, indicacion, cp, lat, lng, zoom, placeStatus,created)
       VALUES  ('{$placeData["idComarca"]}', '{$placeData["idCiudad"]}', '{$placeData["idDistrito"]}', '{$placeData["idBarrio"]}', '{$placeData["nombre"]}', '{$placeData["direccion"]}', '{$placeData["indicacion"]}', '{$placeData["cp"]}',
       '{$placeData["lat"]}', '{$placeData["lng"]}', '{$placeData["zoom"]}', '{$placeData["placeStatus"]}',NULL)";    
   
    mysqli_query($link, $sql);
    return mysqli_insert_id($link);
}

function updatePlace($placeData)
{
    $link=connect();
    mysqli_query($link, 'SET CHARACTER SET utf8');
    
   // Sanitize inputs    
   $placeData['idPlace']=safe($link,$placeData['idPlace']);
   $placeData['idCiudad']=safe($link,$placeData['idCiudad']);
   $placeData['idDistrito']=safe($link,$placeData['idDistrito']);
   $placeData['idBarrio']=safe($link,$placeData['idBarrio']);
   $placeData['nombre']=safe($link,$placeData['nombre']);
   $placeData['direccion']=safe($link,$placeData['direccion']);
   $placeData['indicacion']=safe($link,$placeData['indicacion']);
   $placeData['cp']=safe($link,$placeData['cp']);
   $placeData['lat']=safe($link,$placeData['lat']);
   $placeData['lng']=safe($link,$placeData['lng']);
   $placeData['zoom']=safe($link,$placeData['zoom']);
   $placeData['placeStatus']=safe($link,$placeData['placeStatus']);
    
    $sql="UPDATE places 
        SET idCiudad={$placeData["idCiudad"]}, idDistrito={$placeData["idDistrito"]}, idBarrio={$placeData["idBarrio"]}, nombre='{$placeData["nombre"]}', direccion='{$placeData["direccion"]}',
            indicacion='{$placeData["indicacion"]}', cp='{$placeData["cp"]}', lat='{$placeData["lat"]}', lng='{$placeData["lng"]}', zoom='{$placeData["zoom"]}', placeStatus='{$placeData["placeStatus"]}'
        WHERE idPlace={$placeData["idPlace"]}";
    mysqli_query($link, $sql);
}

function validarDireccion($idPlace,$status)
{
    //Sanitize inputs
    $link=connect();
    $idPlace=safe($link, $idPlace);
    $status=safe($link, $status);
   
    mysqli_query($link, 'SET CHARACTER SET utf8');

    //Buscar el padre según las coordenadas
    $sql="UPDATE places SET placeStatus='$status' WHERE idPlace='$idPlace'";
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
function getTerritoriosColindantes($territoriosExcluidos,$type,$xmin,$xmax,$ymin,$ymax)
{
    $link=connect();
    //sanitize inputs
    $territoriosExcluidos=safe($link, $territoriosExcluidos);
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
            OR $ymax < ymin)
            AND id NOT IN ($territoriosExcluidos)";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result)) {
      array_push($returnData,$fila);
    }
    return $returnData;
}

// Gets the territories in $territorios (either a territoryID or a comma separated collection of territoryIDs
function getTerritorios($territorios)
{
    $link=connect();
    //sanitize inputs
    $territorios=safe($link, $territorios);

    $sql="SELECT * FROM territorios WHERE
            id IN ($territorios)";

    mysqli_query($link, 'SET CHARACTER SET utf8');
    $result=mysqli_query($link, $sql);
    $returnData=array();
    while($fila=mysqli_fetch_assoc($result)) {
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

    //TODO: eventos.idDistritoPadre está obsoleto. Es a través del lugar que se localizan los eventos.
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