<?php
error_reporting(E_ERROR);
include_once "db.php";
$filtros=json_decode($_GET["filtros"],true);
$idTerritorio=$_GET["idTerritorioOriginal"];
$alrededores=$_GET["alrededores"];


$entidades=getEntidades($filtros,$idTerritorio,$alrededores,50);

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

		foreach($entidades as $entidad)
		{
			//print_r($entidad);
			$i++;
			$datos["id"]=$entidad["idEntidad"];
			$datos["clase"]="organizaciones";
			$datos["tipo"]=utf8_encode($entidad["tipoEntidad"]);	
			$grupos[$grupoActual]["totalFilas"][$datos["tipo"]]++;


			$datos["tituloOrg"]=utf8_encode($entidad["entidad"]);
			$datos["textoOrg"]=utf8_encode($entidad["domicilio"]);
			$datos["lugarOrg"]=utf8_encode($entidad["nombreLugar"]);
			$datos["puntos"]=$entidad["points"];
    // TODO: Verify that these are taken from Direcciones and not from Entidades
			$datos["x"]=$entidad["lng"];
			$datos["y"]=$entidad["lat"];
            $datos["idCiudad"]=$entidad["idCiudad"];
            $datos["idDistrito"]=$entidad["idDistrito"];
            $datos["idBarrio"]=$entidad["idSubCiudad"];
			$datos["tematica"]=utf8_encode($entidad["tematica"]);
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
	foreach($entidades as $entidad)
	{
		if($tipoGrupos=="tematica")
			$grupoActual=utf8_encode($entidad["tematica"]);
		else if($tipoGrupos=="lugar")
			$grupoActual=utf8_encode($entidad["nombreLugar"]);			
		
		$datos["id"]=$entidad["idEntidad"];
		$datos["clase"]="organizaciones";
		$datos["tipo"]=utf8_encode($entidad["tipoEntidad"]);	
		$grupos[$grupoActual][""]["totalFilas"][$datos["tipo"]]++;


		$datos["tituloOrg"]=utf8_encode($entidad["entidad"]);
		$datos["textoOrg"]=utf8_encode($entidad["domicilio"]);
		$datos["lugarOrg"]=utf8_encode($entidad["nombreLugar"]);
		$datos["puntos"]=$entidad["points"];
		$datos["tematica"]=utf8_encode($entidad["tematica"]);
		$datos["x"]=$entidad["lng"];
		$datos["y"]=$entidad["lat"];
		$datos["idDistritoPadre"]=$entidad["idPadre"];

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