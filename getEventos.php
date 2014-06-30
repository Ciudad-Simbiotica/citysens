<?php
error_reporting(E_ERROR);
include_once "db.php";
$query=json_decode($_GET["query"],true);
$eventos=getEventos($query,50);
foreach($eventos as $evento)
{
	/*
    [idEvento] => 1769
    [fecha] => 2014-05-13 18:00:00
    [clase] => eventos
    [tipo] => convocatoria
    [titulo] => Mesa redonda sobre las elecciones europeas y el delegacionismo
    [texto] => Mesa redonda sobre las elecciones europeas y el delegacionismo
    [lugar] => Guadalajara
    [temperatura] => 3
    */
	$datos["id"]=$evento["idEvento"];
	$datos["clase"]="eventos";

	//echo $id."<BR>";

	$datos["tipo"]=utf8_encode($evento["tipo"]);
	

	$grupo=date("Y-m-d",strtotime($evento["fecha"]));

	
	if(!isset($returnData["grupos"][$grupo]["totalFilas"]))
	{
		$returnData["grupos"][$grupo]["totalFilas"]["convocatoria"]=0;
		$returnData["grupos"][$grupo]["totalFilas"]["recurrente"]=0;
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

	$returnData["grupos"][$grupo]["cabeceraIzq"]=$cabeceraIzq;
	$returnData["grupos"][$grupo]["cabeceraCntr"]="";//ucfirst(strftime("%A %e",$event["start_time"]));
	$returnData["grupos"][$grupo]["cabeceraDch"]="";//ucfirst(strftime("%B",$event["start_time"]));
	$returnData["grupos"][$grupo]["totalFilas"][$datos["tipo"]]++;

	
	if(!is_array($returnData["grupos"][$grupo]["filas"]))
		$returnData["grupos"][$grupo]["filas"]=array();
	array_push($returnData["grupos"][$grupo]["filas"],$datos);
}

foreach($returnData["grupos"] as $id=>$grupo)
foreach($grupo["totalFilas"] as $key=>$value)
{
	if($value==0)
	{
		unset($returnData["grupos"][$id]["totalFilas"][$key]);			
	}
}
$returnData["tipo"]="eventos";
$returnJSON=json_encode($returnData);
echo $returnJSON;


?>