<?php
error_reporting(E_ERROR);
include_once "db.php";
$query=json_decode($_GET["query"],true);
$eventos=getEventos($query,50,$_GET["orden"]);

$tipoGrupos=$_GET["orden"];

if($tipoGrupos=="popularidad")
{
	$returnData["grupos"]["Los más populares"]=array();
	$returnData["grupos"]["Muy populares"]=array();
	$returnData["grupos"]["Bastante populares"]=array();
	$returnData["grupos"]["Populares"]=array();
	$returnData["grupos"]["Poco populares"]=array();
}
foreach($eventos as $evento)
{
	$datos["id"]=$evento["idEvento"];
	$datos["clase"]="eventos";
	$datos["tipo"]=utf8_encode($evento["tipo"]);
	$grupo=date("Y-m-d",strtotime($evento["fecha"]));


	$datos["titulo"]=utf8_encode($evento["titulo"]);
	$datos["texto"]=utf8_encode($evento["texto"]);//$lorem;
	$datos["hora"]=date("H:i",strtotime($evento["fecha"]));
	$datos["lugar"]=utf8_encode($evento["lugar"]);
	$datos["temperatura"]=$evento["temperatura"];
	$datos["tematicas"]=utf8_encode($evento["tematicas"]);

	if($grupo==date("Y-m-d",strtotime("2014-05-13")))
		$cabeceraIzq="Hoy, ";
	else if($grupo==date("Y-m-d",strtotime("2014-05-13")+86400))
		$cabeceraIzq="Mañana, ";
	else
		$cabeceraIzq="";

	$cabeceraIzq.=ucfirst(strftime("%A %e",strtotime($evento["fecha"])));
	$cabeceraIzq.=" de ".ucfirst(strftime("%B",strtotime($evento["fecha"])));


	unset($nombreGrupos);
	$nombreGrupos=array();

	if($tipoGrupos=="fecha")
		array_push($nombreGrupos,"");
	else if($tipoGrupos=="lugar")
		array_push($nombreGrupos,utf8_encode($evento["lugar"]));
	else if($tipoGrupos=="tematica")
	{
		$nombreGrupos=split(',',utf8_encode($evento["tematicas"]));
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
				$popularidad="Poco populares";
				break;
		}
		array_push($nombreGrupos,$popularidad);		
	}


	
	foreach($nombreGrupos as $nombreGrupo)
	{		
		if(!isset($returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]))
		{
			$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]["convocatoria"]=0;
			$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]["recurrente"]=0;
		}

		$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraIzq"]=$cabeceraIzq;
		$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraCntr"]="";//ucfirst(strftime("%A %e",$event["start_time"]));
		$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraDch"]="";//ucfirst(strftime("%B",$event["start_time"]));
		$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"][$datos["tipo"]]++;

		
		if(!is_array($returnData["grupos"][$nombreGrupo][$grupo]["filas"]))
			$returnData["grupos"][$nombreGrupo][$grupo]["filas"]=array();
		array_push($returnData["grupos"][$nombreGrupo][$grupo]["filas"],$datos);
		
	}
	
}

if($tipoGrupos=="tematica")
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
	if(count($returnData["grupos"]["Poco populares"])==0)	
		unset($returnData["grupos"]["Poco populares"]);
}

foreach($returnData["grupos"] as $nombreGrupo=>$datosGrupo)
foreach($datosGrupo as $id=>$grupo)
foreach($grupo["totalFilas"] as $key=>$value)
{
	if($value==0)
	{
		unset($returnData["grupos"][$nombreGrupo][$id]["totalFilas"][$key]);			
	}
}
$returnData["tipo"]="eventos";
$returnData["orden"]=$_GET["orden"];

$returnJSON=json_encode($returnData);
echo $returnJSON;


?>