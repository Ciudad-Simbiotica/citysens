<?php
error_reporting(E_ERROR);
include_once "db.php";
$filtros=json_decode($_GET["filtros"],true);
$idTerritorio=$_GET["idTerritorioOriginal"];
$alrededores=$_GET["alrededores"];


$entidades=getEntidades($filtros,$idTerritorio,$alrededores,50);

$tipoGrupos=$_GET["orden"];

$filtrosTematica = array();

if($tipoGrupos=="tematica") {
    if (count($filtros)!=0){
        //Verificar si hay filtros antes
        foreach ($filtros as $filtro) {
            if($filtro["tipo"]=="tematica")
                $filtrosTematica[]=html_entity_decode($filtro["texto"]);                              	     
        }        
    }
}

$i=0;
if($tipoGrupos=="puntuacion")
{
    if(count($entidades)>0)
    {
        $grupoActual="Top 10";
        $filas=array();
        $returnData["grupos"][""][$grupoActual]["totalFilas"]["institucion"]=0;
        $returnData["grupos"][""][$grupoActual]["totalFilas"]["organizacion"]=0;
        $returnData["grupos"][""][$grupoActual]["totalFilas"]["colectivo"]=0;

        foreach($entidades as $entidad)
        {
            //print_r($entidad);
            $i++;
            $datos["id"]=$entidad["idEntidad"];
            $datos["clase"]="organizaciones";
            $datos["tipo"]=utf8_encode($entidad["tipo"]);	
            $returnData["grupos"][""][$grupoActual]["totalFilas"][$datos["tipo"]]++;
            $datos["tituloOrg"]=utf8_encode($entidad["entidad"]);
            $datos["textoOrg"]=utf8_encode($entidad["domicilio"]);
            if($entidad["nombreCorto"]!="")
                $datos["lugarOrg"]=utf8_encode($entidad["nombreCorto"]);
            else
                $datos["lugarOrg"]=utf8_encode($entidad["nombreLugar"]);
            $datos["puntos"]=$entidad["points"];
            $datos["x"]=$entidad["lng"];
            $datos["y"]=$entidad["lat"];
            $datos["idCiudad"]=$entidad["idCiudad"];
            $datos["idDistrito"]=$entidad["idDistrito"];
            $datos["idBarrio"]=$entidad["idBarrio"];
            $datos["tematica"]=utf8_encode($entidad["tematica"]);
            $datos["primeraOcurrencia"]=1; //Usado para contabilizar entidades en mapa (necesario para evitar duplicados en ordenación Temáticas)

            array_push($filas,$datos);

            if ((($i%10)==0)) {
                //echo $grupoActual;
                $returnData["grupos"][""][$grupoActual]["cabeceraIzq"]="";
                $returnData["grupos"][""][$grupoActual]["cabeceraCntr"]=$grupoActual;
                $returnData["grupos"][""][$grupoActual]["cabeceraDch"]="";
                $returnData["grupos"][""][$grupoActual]["filas"]=$filas;
                foreach($returnData["grupos"][""][$grupoActual]["totalFilas"] as $key=>$value)
                {
                    //Quitamos los que valgan cero
                    if($value==0)
                        unset($returnData["grupos"][""][$grupoActual]["totalFilas"][$key]);
                }
                unset($filas);
                $filas=array();
                $inicio=$i+1;
                $fin=$inicio+9;
                $grupoActual="Top $inicio-$fin";
                if($i==50)break;
                $returnData["grupos"][""][$grupoActual]["totalFilas"]["institucion"]=0;
                $returnData["grupos"][""][$grupoActual]["totalFilas"]["organizacion"]=0;
                $returnData["grupos"][""][$grupoActual]["totalFilas"]["colectivo"]=0;
            }
	}
        if((($i%10)!=0))	//No hemos añadido las últimas
        {
            $returnData["grupos"][""][$grupoActual]["cabeceraIzq"]="";
            $returnData["grupos"][""][$grupoActual]["cabeceraCntr"]=$grupoActual;
            $returnData["grupos"][""][$grupoActual]["cabeceraDch"]="";
            $returnData["grupos"][""][$grupoActual]["filas"]=$filas;
            foreach($returnData["grupos"][""][$grupoActual]["totalFilas"] as $key=>$value)
            {
                //Quitamos los que valgan cero
                if($value==0)
                    unset($returnData["grupos"][""][$grupoActual]["totalFilas"][$key]);
            }
        }
    }
}
else {
    foreach($entidades as $entidad)
    {
			
        $datos["id"]=$entidad["idEntidad"];
        $datos["clase"]="organizaciones";
        $datos["tipo"]=utf8_encode($entidad["tipo"]);

        $datos["tituloOrg"]=utf8_encode($entidad["entidad"]);
        $datos["textoOrg"]=utf8_encode($entidad["domicilio"]);
        if($evento["nombreCorto"]!="")
            $datos["lugarOrg"]=utf8_encode($entidad["nombreCorto"]);
        else
            $datos["lugarOrg"]=utf8_encode($entidad["nombreLugar"]);
        $datos["puntos"]=$entidad["points"];
        $datos["tematicas"]=utf8_encode($entidad["tematicas"]);
        $datos["x"]=$entidad["lng"];
        $datos["y"]=$entidad["lat"];
        $datos["idCiudad"]=$entidad["idCiudad"];
        $datos["idDistrito"]=$entidad["idDistrito"];
        $datos["idBarrio"]=$entidad["idBarrio"];
        
        unset($nombreGrupos);
        $nombreGrupos=array();

        if($tipoGrupos=="lugar")
            array_push($nombreGrupos,utf8_encode($entidad["nombreLugar"]));
        else if($tipoGrupos=="tematica") {
            //Si no hay filtros de temáticas
            if(count($filtrosTematica)==0)                       
                $nombreGrupos=split(',',utf8_encode($entidad["tematicas"]));
            else {
                $nombreGruposTemp=split(',',utf8_encode($entidad["tematicas"]));
                foreach ($nombreGruposTemp as $nombre) {
                    if (in_array($nombre, $filtrosTematica))
                        $nombreGrupos[]=$nombre;
                }
            } 
        }
        
        $datos["primeraOcurrencia"]=1; //Para evitar que se cuente varias veces en el mapa
        foreach($nombreGrupos as $nombreGrupo)
        {		
            $returnData["grupos"][$nombreGrupo][""]["totalFilas"][$datos["tipo"]]++;

            if(!is_array($returnData["grupos"][$nombreGrupo][""]["filas"]))
            {
                $returnData["grupos"][$nombreGrupo][""]["cabeceraIzq"]="";
                $returnData["grupos"][$nombreGrupo][""]["cabeceraCntr"]="";
                $returnData["grupos"][$nombreGrupo][""]["cabeceraDch"]="";
                
                $returnData["grupos"][$nombreGrupo][""]["filas"]=array();
            }
            array_push($returnData["grupos"][$nombreGrupo][""]["filas"],$datos);
            
            if ($datos["primeraOcurrencia"]==1)
                $datos["primeraOcurrencia"]=0;
        }
  		
    }		
	
}
if($tipoGrupos=="lugar") //Is it needed for lugar? 
    ksort($returnData["grupos"][""]);
else if ($tipoGrupos=="tematica")
    ksort($returnData["grupos"]);

$returnData["tipo"]="organizaciones";
$returnData["orden"]=$tipoGrupos;

?>