<?
error_reporting(E_ALL);
$lorem="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
date_default_timezone_set("Europe/Madrid");
$url="http://agendadelhenares.org/widget-json?uid=3";
$raw_data=file_get_contents($url);
$data=json_decode($raw_data,true);


$i=0;
foreach($data["events"] as $id=>$event)
{
	$dia=date("Y-m-d",$event["start_time"]);
	$datos["titulo"]=$event["title"];
	$datos["texto"]=$event["title"];//$lorem;
	$datos["hora"]=date("H:i",$event["start_time"]);
	$datos["lugar"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["name"];
	switch(rand(1,4))
	{
		case 1:
			$datos["tipo"]="puntual";
			break;
		case 2:
			$datos["tipo"]="periodico";
			break;
		case 3:
			$datos["tipo"]="iniciativa";
			break;
		case 4:
			$datos["tipo"]="reunion";
			break;
		default:
			break;
	}

	if(!is_array($returnData["dias"][$dia]))
		$returnData["dias"][$dia]=array();
	array_push($returnData["dias"][$dia],$datos);
	$i++;
	if($i==50)break;
}
echo json_encode($returnData);
?>