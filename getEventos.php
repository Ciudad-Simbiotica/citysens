<?php
error_reporting(E_ERROR);
include_once "db.php";
$query=json_decode($_GET["query"],true);
//exit();
$eventos=getEventos($query,50,$_GET["orden"]);

$tipoGrupos=$_GET["orden"];

foreach($eventos as $evento)
{
	$datos["id"]=$evento["idEvento"];
	$datos["clase"]="eventos";

	if($tipoGrupos=="fecha")
		$nombreGrupo="";
	else if($tipoGrupos=="lugar")
		$nombreGrupo=utf8_encode($evento["lugar"]);

	//echo $id."<BR>";

	$datos["tipo"]=utf8_encode($evento["tipo"]);
	

	$grupo=date("Y-m-d",strtotime($evento["fecha"]));

	
	if(!isset($returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]))
	{
		$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]["convocatoria"]=0;
		$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"]["recurrente"]=0;
	}
	

	$datos["titulo"]=utf8_encode($evento["titulo"]);
	$datos["texto"]=utf8_encode($evento["texto"]);//$lorem;
	$datos["hora"]=date("H:i",strtotime($evento["fecha"]));
	$datos["lugar"]=utf8_encode($evento["lugar"]);
	$datos["temperatura"]=$evento["temperatura"];

	if($grupo==date("Y-m-d",strtotime("2014-05-13")))
		$cabeceraIzq="Hoy, ";
	else if($grupo==date("Y-m-d",strtotime("2014-05-13")+86400))
		$cabeceraIzq="Mañana, ";
	else
		$cabeceraIzq="";

	$cabeceraIzq.=ucfirst(strftime("%A %e",strtotime($evento["fecha"])));
	$cabeceraIzq.=" de ".ucfirst(strftime("%B",strtotime($evento["fecha"])));

	$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraIzq"]=$cabeceraIzq;
	$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraCntr"]="";//ucfirst(strftime("%A %e",$event["start_time"]));
	$returnData["grupos"][$nombreGrupo][$grupo]["cabeceraDch"]="";//ucfirst(strftime("%B",$event["start_time"]));
	$returnData["grupos"][$nombreGrupo][$grupo]["totalFilas"][$datos["tipo"]]++;

	
	if(!is_array($returnData["grupos"][$nombreGrupo][$grupo]["filas"]))
		$returnData["grupos"][$nombreGrupo][$grupo]["filas"]=array();
	array_push($returnData["grupos"][$nombreGrupo][$grupo]["filas"],$datos);
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