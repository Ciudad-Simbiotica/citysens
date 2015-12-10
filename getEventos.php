<?php
error_reporting(E_ERROR);
include_once "db.php";
/* @var $filtros Array with search filters */
$filtros=json_decode($_GET["filtros"],true);
/* @var $itemsNumber integer - number of items already listed */
$itemsNumber=$_GET["itemsNumber"];
/* @var $itemsLimit integer - number of items to display */
$itemsLimit=$_GET["itemsLimit"];
/* @var $idTerritorio integer - ID of the territory to be displayed*/
$idTerritorio=$_GET["idTerritorioOriginal"];

/* @var $alrededores boolean - Indicates if "alrededores", neighboring territories should be highlighted */
$alrededores=$_GET["alrededores"];

$eventos=getEventos($filtros,$idTerritorio,$alrededores,$itemsNumber,$itemsLimit);

$tipoGrupos=$_GET["orden"];
$filtrosTematica = array();
       
if($tipoGrupos=="popularidad")
{
	$returnData["grupos"]["Los más populares"]=array();
	$returnData["grupos"]["Muy populares"]=array();
	$returnData["grupos"]["Bastante populares"]=array();
	$returnData["grupos"]["Populares"]=array();
	$returnData["grupos"]["Otros"]=array();
} else if($tipoGrupos=="tematica")
{
    if (count($filtros)!=0){
        //Verificar si hay filtros antes
        foreach ($filtros as $filtro) {
            if($filtro["tipo"]=="tematica")
                $filtrosTematica[]=html_entity_decode($filtro["texto"]);                              	     
        }        
    }
}
   
foreach($eventos as $evento)
{
    $grupo=date("Y-m-d",strtotime($evento["fecha"])); // The groups' titles are the dates
    
    $datos["id"]=$evento["idEvento"];
    $datos["clase"]="eventos";
    $datos["tipo"]=utf8_encode($evento["tipo"]);    
    $datos["titulo"]=utf8_encode($evento["titulo"]);
    $datos["texto"]=utf8_encode($evento["texto"]); // It's empty, by now
    $datos["hora"]=date("H:i",strtotime($evento["fecha"]));
    if($evento["nombreCorto"]!="")
      $datos["lugar"]=utf8_encode($evento["nombreCorto"]);
    else
      $datos["lugar"]=utf8_encode($evento["nombreLugar"]);
    $datos["temperatura"]=$evento["temperatura"];
    $datos["tematicas"]=utf8_encode($evento["tematicas"]);
    $datos["x"]=$evento["lng"];
    $datos["y"]=$evento["lat"];
    $datos["idCiudad"]=$evento["idCiudad"];
    $datos["idDistrito"]=$evento["idDistrito"];
    $datos["idBarrio"]=$evento["idBarrio"];
    
    $hoy=date("Y-m-d");
    // $hoy=date("2015-05-02"); // Can be used (temporarily) to display a diferent day
    
    if($grupo==$hoy)
            $cabeceraIzq="Hoy, ";
    else if($grupo==date("Y-m-d",$hoy+86400))
            $cabeceraIzq="Mañana, ";
    else
            $cabeceraIzq="";
    
    $cabeceraIzq.=ucfirst(strftime("%A %d",strtotime($evento["fecha"])));
    $cabeceraIzq.=" de ".ucfirst(strftime("%B",strtotime($evento["fecha"])));
 
    unset($nombreGrupos);
    $nombreGrupos=array();

    if($tipoGrupos=="fecha")
            array_push($nombreGrupos,"");
    else if($tipoGrupos=="lugar")
            array_push($nombreGrupos,$datos["lugar"]);
    else if($tipoGrupos=="tematica")
    {
        //Si no hay filtros de temáticas
        if(count($filtrosTematica)==0)                       
        $nombreGrupos=split(',',utf8_encode($evento["tematicas"]));
        else{
            $nombreGruposTemp=split(',',utf8_encode($evento["tematicas"]));
            foreach ($nombreGruposTemp as $nombre){
                if (in_array($nombre, $filtrosTematica))
                 $nombreGrupos[]=$nombre;
            }
        } 
    }
    else if($tipoGrupos=="popularidad")
    {
            $popularidad="";
            switch ($evento["temperatura"]) 
            {
                    case 5:
                            $popularidad="Los más populares";
                            break;
                    case 4:
                            $popularidad="Muy populares";
                            break;
                    case 3:
                            $popularidad="Bastante populares";
                            break;
                    case 2:
                            $popularidad="Populares";
                            break;
                    case 1:
                            $popularidad="Otros";
                            break;
            }
            array_push($nombreGrupos,$popularidad);		
    }

    $datos["primeraOcurrencia"]=1; //Para evitar que se cuente varias veces en el mapa
    foreach($nombreGrupos as $nombreGrupo)
    {		
        $returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"][$datos["tipo"]]++;

        if(!is_array($returnData["grupos"][$nombreGrupo][$grupo]["filas"])) {
            $returnData["grupos"][$nombreGrupo][$grupo]["cabeceraIzq"]=$cabeceraIzq;
            $returnData["grupos"][$nombreGrupo][$grupo]["cabeceraCntr"]="";//ucfirst(strftime("%A %e",$event["start_time"]));
            $returnData["grupos"][$nombreGrupo][$grupo]["cabeceraDch"]="";//ucfirst(strftime("%B",$event["start_time"]));
            
            $returnData["grupos"][$nombreGrupo][$grupo]["filas"]=array();
        }
        array_push($returnData["grupos"][$nombreGrupo][$grupo]["filas"],$datos);

        if ($datos["primeraOcurrencia"]==1)
            $datos["primeraOcurrencia"]=0;
    }
      
}

if($tipoGrupos=="tematica" || $tipoGrupos=="lugar")
    ksort($returnData["grupos"]);	//Ordenamos por la clave
else if($tipoGrupos=="popularidad")
{
	//Si no tienen ningún evento lo quitamos de los grupos
	if(count($returnData["grupos"]["Los más populares"])==0)
		unset($returnData["grupos"]["Los más populares"]);
	if(count($returnData["grupos"]["Muy populares"])==0)
		unset($returnData["grupos"]["Muy populares"]);
	if(count($returnData["grupos"]["Bastante populares"])==0)
		unset($returnData["grupos"]["Bastante populares"]);
	if(count($returnData["grupos"]["Populares"])==0)
		unset($returnData["grupos"]["Populares"]);
	if(count($returnData["grupos"]["Otros"])==0)	
		unset($returnData["grupos"]["Otros"]);
}
//
//foreach($returnData["grupos"] as $nombreGrupo=>$datosGrupo)
//foreach($datosGrupo as $id=>$grupo)
//foreach($grupo["totalFilas"] as $key=>$value)
//{
//	if($value==0)
//	{
//		unset($returnData["grupos"][$nombreGrupo][$id]["totalFilas"][$key]);			
//	}
//}

$returnData["tipo"]="eventos";
$returnData["orden"]=$tipoGrupos;
?>