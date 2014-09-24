<?php
error_reporting(E_ERROR);
include_once "db.php";
$query=json_decode($_GET["query"],true);

$entidades=getAsociacionesQuery($query,50);


$i=0;

$tipoGrupos=$_GET["orden"];


if($tipoGrupos=="puntuacion")
{
	if(count($entidades)>0)
	{
		$grupoActual="Top 10";
		$filas=array();
		$grupos[$grupoActual]["totalFilas"]["institucion"]=0;
		$grupos[$grupoActual]["totalFilas"]["organizacion"]=0;
		$grupos[$grupoActual]["totalFilas"]["colectivo"]=0;

		foreach($entidades as $asociacion)
		{
			//print_r($asociacion);
			$i++;
			$datos["id"]=$asociacion["idAsociacion"];
			$datos["clase"]="organizaciones";
			$datos["tipo"]=utf8_encode($asociacion["tipoAsociacion"]);	
			$grupos[$grupoActual]["totalFilas"][$datos["tipo"]]++;


			$datos["tituloOrg"]=utf8_encode($asociacion["asociacion"]);
			$datos["textoOrg"]=utf8_encode($asociacion["domicilio"]);
			$datos["lugarOrg"]="Distr. ".($asociacion["idDistritoPadre"]-999000004);
			$datos["puntos"]=$asociacion["points"];
			$datos["x"]=$asociacion["long"];
			$datos["y"]=$asociacion["lat"];
			$datos["idDistritoPadre"]=$asociacion["idPadre"];
			$datos["tematica"]=utf8_encode($asociacion["tematica"]);
			array_push($filas,$datos);

			if((($i%10)==0))
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
				$inicio=$i+1;
				$fin=$inicio+9;
				$grupoActual="Top $inicio-$fin";
				if($i==50)break;
				$grupos[$grupoActual]["totalFilas"]["institucion"]=0;
				$grupos[$grupoActual]["totalFilas"]["organizacion"]=0;
				$grupos[$grupoActual]["totalFilas"]["colectivo"]=0;
			}

		}
		if((($i%10)!=0))	//No hemos añadido las últimas
		{
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
		}
	}
}
else
{
	
	foreach($entidades as $asociacion)
	{
		if($tipoGrupos=="tematica")
			$grupoActual=utf8_encode($asociacion["tematica"]);
		else if($tipoGrupos=="lugar")
			$grupoActual="Distrito ".($asociacion["idDistritoPadre"]-999000004);			
		
		$datos["id"]=$asociacion["idAsociacion"];
		$datos["clase"]="organizaciones";
		$datos["tipo"]=utf8_encode($asociacion["tipoAsociacion"]);	
		$grupos[$grupoActual][""]["totalFilas"][$datos["tipo"]]++;


		$datos["tituloOrg"]=utf8_encode($asociacion["asociacion"]);
		$datos["textoOrg"]=utf8_encode($asociacion["domicilio"]);
		$datos["lugarOrg"]="Distr. ".($asociacion["idDistritoPadre"]-999000004);
		$datos["puntos"]=$asociacion["points"];
		$datos["tematica"]=utf8_encode($asociacion["tematica"]);
		$datos["x"]=$asociacion["long"];
		$datos["y"]=$asociacion["lat"];
		$datos["idDistritoPadre"]=$asociacion["idPadre"];

		if(!is_array($grupos[$grupoActual][""]["filas"]))
		{
			$grupos[$grupoActual][""]["cabeceraIzq"]="";
			$grupos[$grupoActual][""]["cabeceraCntr"]="";
			$grupos[$grupoActual][""]["cabeceraDch"]="";
			$grupos[$grupoActual][""]["filas"]=array();
		}
		array_push($grupos[$grupoActual][""]["filas"],$datos);
		
	}	
	
}
if($tipoGrupos=="lugar")
	ksort($grupos);

$returnData["tipo"]="organizaciones";
$returnData["orden"]=$tipoGrupos;
if(count($grupos)>0)
{
	if($tipoGrupos=="puntuacion")
		$returnData["grupos"][""]=$grupos;
	else
		$returnData["grupos"]=$grupos;
}

?>