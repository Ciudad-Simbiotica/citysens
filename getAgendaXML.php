<?php
error_reporting(E_ALL);
$lorem="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
date_default_timezone_set("Europe/Madrid");
setlocale(LC_ALL, 'es_ES');


$return_data=file_get_contents("returnCache.txt");

if($return_data!="")
{
	echo $return_data;
	exit;
}

$url="http://agendadelhenares.org/widget-json?uid=3";
$raw_data=file_get_contents($url);

$data=json_decode($raw_data,true);


$i=0;
foreach($data["events"] as $id=>$event)
{
	switch(rand(1,3))
	{
		case 1:
			$datos["tipo"]="convocatoria";
			break;
		case 2:
			$datos["tipo"]="recurrente";
			break;
		case 3:
			$datos["tipo"]="iniciativa";
			break;
		/*
		case 4:
			$datos["tipo"]="reunion";
			break;
		*/
		default:
			break;
	}

	$grupo=date("Y-m-d",$event["start_time"]);
	$datos["titulo"]=$event["title"];
	$datos["texto"]=$event["title"];//$lorem;
	$datos["hora"]=date("H:i",$event["start_time"]);
	$datos["lugar"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["name"];

	if($grupo==date("Y-m-d"))
		$cabeceraIzq="Hoy";
	else if($grupo==date("Y-m-d",time()+86400))
		$cabeceraIzq="Mañana";
	else
		$cabeceraIzq="";

	$returnData["grupos"][$grupo]["cabeceraIzq"]=$cabeceraIzq;
	$returnData["grupos"][$grupo]["cabeceraCntr"]=ucfirst(strftime("%A %e",$event["start_time"]));
	$returnData["grupos"][$grupo]["cabeceraDch"]=ucfirst(strftime("%B",$event["start_time"]));

	if(!is_array($returnData["grupos"][$grupo]["filas"]))
		$returnData["grupos"][$grupo]["filas"]=array();
	array_push($returnData["grupos"][$grupo]["filas"],$datos);
	$i++;
	if($i==50)break;
}
$returnData["tipo"]="eventos";
$returnJSON=json_encode($returnData);
echo $returnJSON;
file_put_contents("returnCache.txt", $returnJSON);
?>