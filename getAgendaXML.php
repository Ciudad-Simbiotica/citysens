<?php

error_reporting(E_ERROR);
include "loadSession.php";
include "preload.php";

$lorem="Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
date_default_timezone_set("Europe/Madrid");
setlocale(LC_ALL, 'es_ES');



if(!in_array($_GET["clase"], array("eventos","procesos","organizaciones")))
	exit;


if($_GET["clase"]=="eventos")
{
	include "getEventos.php";
}
else if($_GET["clase"]=="organizaciones")
{
	include "getEntidades.php";
}

//Añadimos si el user está siguiendo (si estamos logueados)
if($_SESSION["user"])
{
	$returnData["isFollowing"]=isFollowing($_SESSION["user"]["idUser"],$_GET["query"],$_GET["clase"]);
}
else
{
	$returnData["isFollowing"]=false;	
}

//Añadimos los datos del lugar original
$returnData["lugarOriginal"]=getDatosLugar($_GET["idLugarOriginal"]);

$returnJSON=json_encode($returnData);

echo $returnJSON;


exit();

/*
//A partir de aquí código viejo
if($_GET["regenerar"]=="")
{
	$return_data=file_get_contents("returnCache_{$_GET["clase"]}.txt");
	echo $return_data;
	exit;
}


if($_GET["clase"]=="organizaciones")
{
	//Generar organizaciones

	$entidades=array();

	$fila = 1;
	if (($gestor = fopen("asociaciones.csv", "r")) !== FALSE) 
	{
	    while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) 
	    {
	        $numero = count($datos);
	        for ($c=0; $c < $numero; $c++) 
		    {
			    if($fila==1)
	        	{
	    	    	$variables[$c]=$datos[$c];
	    		}
	    		else
	    		{
	    			$entidad[$variables[$c]]=$datos[$c];
	    		}
	        }
		    if($fila>1)
		        array_push($entidades, $entidad);
	        $fila++;
	    }
	    fclose($gestor);
	}

	$i=0;
	$grupoActual="Top 10";
	$filas=array();
	$grupos[$grupoActual]["totalFilas"]["institucion"]=0;
	$grupos[$grupoActual]["totalFilas"]["organizacion"]=0;
	$grupos[$grupoActual]["totalFilas"]["colectivo"]=0;

	$puntos=5000;

	foreach($entidades as $entidad)
	{
		//echo $grupoActual;
		$i++;
		if((($i%10)==1)&($i>1))
		{
			//echo $grupoActual;
			$grupos[$grupoActual]["cabeceraIzq"]="";
			$grupos[$grupoActual]["cabeceraCntr"]=$grupoActual;
			$grupos[$grupoActual]["cabeceraDch"]="";
			$grupos[$grupoActual]["filas"]=$filas;
			foreach($grupos[$grupoActual]["totalFilas"] as $key=>$value)
			{
				//Quitamos los que valgan cero
				if($value==0)
					unset($grupos[$grupoActual]["totalFilas"][$key]);
			}
			unset($filas);
			$filas=array();
			$inicio=$i;
			$fin=$inicio+9;
			$grupoActual="Top $inicio-$fin";
			if($i==51)break;
			$grupos[$grupoActual]["totalFilas"]["institucion"]=0;
			$grupos[$grupoActual]["totalFilas"]["organizacion"]=0;
			$grupos[$grupoActual]["totalFilas"]["colectivo"]=0;
		}

		$datos["id"]=$i;
		$datos["clase"]="organizaciones";
		switch(rand(1,3))
		{
			case 1:
				$datos["tipo"]="institucion";
				break;
			case 2:
				$datos["tipo"]="organizacion";
				break;
			
			case 3:
				$datos["tipo"]="colectivo";
				break;
		}
		$grupos[$grupoActual]["totalFilas"][$datos["tipo"]]++;

		$puntos-=rand(50,150);

		$datos["tituloOrg"]=$entidad["ASOCIACION"];
		$datos["textoOrg"]=$entidad["DOMICILIO"];
		$datos["lugarOrg"]="Distr. ".$entidad["DISTRITO"];
		$datos["puntos"]=$puntos;
		
		
		array_push($filas,$datos);
	}

	$returnData["tipo"]="organizaciones";
	$returnData["grupos"][""]=$grupos;
	$returnJSON=json_encode($returnData);
	echo $returnJSON;
	file_put_contents("returnCache_organizaciones.txt", $returnJSON);

	exit();

}
else if($_GET["clase"]=="eventos")
{

	//GENERAR EVENTOS

	//$url="http://agendadelhenares.org/widget-json?uid=3";
	$url="eventos.json";
	$raw_data=file_get_contents($url);

	$data=json_decode($raw_data,true);


	$i=0;
	foreach($data["events"] as $id=>$event)
	{
		$datos["id"]=$event["id"];
		$datos["clase"]="eventos";

		//echo $id."<BR>";

		switch(rand(1,2))
		{
			case 1:
				$datos["tipo"]="convocatoria";
				break;
			case 2:
				$datos["tipo"]="recurrente";
				break;
						
			default:
				break;
		}

		$grupo=date("Y-m-d",$event["start_time"]);

		
		if(!isset($returnData["grupos"][$grupo]["totalFilas"]))
		{
			$returnData["grupos"][$grupo]["totalFilas"]["convocatoria"]=0;
			$returnData["grupos"][$grupo]["totalFilas"]["recurrente"]=0;
		}
		

		$datos["titulo"]=$event["title"];
		$datos["texto"]=$event["title"];//$lorem;
		$datos["hora"]=date("H:i",$event["start_time"]);
		$datos["lugar"]=$event["dboUseForeign_place_id"]["dboUseForeign_cityId"]["name"];
		$datos["temperatura"]=rand(1,5);

		if($grupo==date("Y-m-d",strtotime("2014-05-13")))
			$cabeceraIzq="Hoy, ";
		else if($grupo==date("Y-m-d",strtotime("2014-05-13")+86400))
			$cabeceraIzq="Mañana, ";
		else
			$cabeceraIzq="";

		$cabeceraIzq.=ucfirst(strftime("%A %e",$event["start_time"]));
		$cabeceraIzq.=" de ".ucfirst(strftime("%B",$event["start_time"]));

		$returnData["grupos"][$grupo]["cabeceraIzq"]=$cabeceraIzq;
		$returnData["grupos"][$grupo]["cabeceraCntr"]="";//ucfirst(strftime("%A %e",$event["start_time"]));
		$returnData["grupos"][$grupo]["cabeceraDch"]="";//ucfirst(strftime("%B",$event["start_time"]));
		$returnData["grupos"][$grupo]["totalFilas"][$datos["tipo"]]++;

		
		if(!is_array($returnData["grupos"][$grupo]["filas"]))
			$returnData["grupos"][$grupo]["filas"]=array();
		array_push($returnData["grupos"][$grupo]["filas"],$datos);
		
		$i++;
		
		if($i>=50)
			break;
		
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
	file_put_contents("returnCache_eventos.txt", $returnJSON);
}
*/
?>
