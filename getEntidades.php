<?php
error_reporting(E_ERROR);
include_once "db.php";
$filtros=json_decode($_GET["filtros"],true);
$itemsNumber=$_GET["itemsNumber"];
$itemsLimit=$_GET["itemsLimit"];
$idTerritorio=$_GET["idTerritorioOriginal"];
$alrededores=$_GET["alrededores"];


//Por que no funciona

$entidades=getEntidades($filtros,$idTerritorio,$alrededores,$itemsNumber,$itemsLimit);

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
//variables para rankings

$maximosRangos=['10','30','50'];
$rangoSucesivo=50;
$nombresRangos=['Top 10','Top 11-30','Top 31-50'];

if($tipoGrupos=="puntuacion")
{
    
    //TODO: Generalizar para que grupos sean de 100 a partir de ahí    
    $contadorFila=0;
    $contadorGrupo=0;
    $grupoActual=$nombresRangos[$contadorGrupo];
    $maximoGrupoActual=$maximosRangos[$contadorGrupo];
//----------------------------
    $filas=array();

    foreach($entidades as $entidad)
    {
        $contadorFila++;                 
        //print_r($entidad);

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

        if($contadorFila==$maximoGrupoActual) {     
               
            //echo $grupoActual;
            $returnData["grupos"][""][$grupoActual]["cabeceraIzq"]="";
            $returnData["grupos"][""][$grupoActual]["cabeceraCntr"]=$grupoActual;
            $returnData["grupos"][""][$grupoActual]["cabeceraDch"]="";

            $returnData["grupos"][""][$grupoActual]["filas"]=$filas;

            unset($filas);
            $filas=array();
            
            $contadorGrupo++; 
            
            if ($contadorGrupo<count($maximosRangos)) {
                $maximoGrupoActual=$maximosRangos[$contadorGrupo];
                $grupoActual=$nombresRangos[$contadorGrupo];
            }
            else {
                $grupoActual="Top ".($maximoGrupoActual+1)."-".($maximoGrupoActual+$rangoSucesivo);
                $maximoGrupoActual=$maximoGrupoActual+$rangoSucesivo;
            }
        }    
    }
    if(count($filas)>0)	//Hay filas aún no asignadas a un grupo
    {
        $returnData["grupos"][""][$grupoActual]["cabeceraIzq"]="";
        $returnData["grupos"][""][$grupoActual]["cabeceraCntr"]=$grupoActual;
        $returnData["grupos"][""][$grupoActual]["cabeceraDch"]="";

        $returnData["grupos"][""][$grupoActual]["filas"]=$filas;
    }
}
else {
    foreach($entidades as $entidad) {

        $datos["id"]=$entidad["idEntidad"];
        $datos["clase"]="organizaciones";
        $datos["tipo"]=utf8_encode($entidad["tipo"]);

        $datos["tituloOrg"]=utf8_encode($entidad["entidad"]);
        $datos["textoOrg"]=utf8_encode($entidad["domicilio"]);
        if($entidad["nombreCorto"]!="")
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

        unset($nombreCategorias);
        $nombreCategorias=array();

        if($tipoGrupos=="lugar")
            array_push($nombreCategorias,$datos["lugarOrg"]);
        else if($tipoGrupos=="tematica") {
            //Si no hay filtros de temáticas
            if(count($filtrosTematica)==0)                       
                $nombreCategorias=split(',',utf8_encode($entidad["tematicas"]));
            else {
                $nombreGruposTemp=split(',',utf8_encode($entidad["tematicas"]));
                foreach ($nombreGruposTemp as $nombre) {
                    if (in_array($nombre, $filtrosTematica))
                        $nombreCategorias[]=$nombre;
                }
            } 
        }

        $datos["primeraOcurrencia"]=1; //Para evitar que se cuente varias veces en el mapa
        foreach($nombreCategorias as $nombreCategoria)
        {   

            if (!is_array($returnData["grupos"][$nombreCategoria])) {        
                $contadorFila[$nombreCategoria]=0;
                $contadorGrupo[$nombreCategoria]=0;
                $grupoActual[$nombreCategoria]=$nombresRangos[0];
                $maximoGrupoActual[$nombreCategoria]=$maximosRangos[0];

                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraIzq"]="";
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraCntr"]=$grupoActual[$nombreCategoria];
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraDch"]="";
            }
            $contadorFila[$nombreCategoria]++;

            if ($contadorFila[$nombreCategoria]>$maximoGrupoActual[$nombreCategoria]) {
            // Current fila bigger than the maximum for current group. New group initialised

                $contadorGrupo[$nombreCategoria]++;

                if($contadorGrupo[$nombreCategoria]<count($maximosRangos)) { 
                //First groups, whose maximum is configured in maximosRangos
                    $grupoActual[$nombreCategoria]=$nombresRangos[$contadorGrupo[$nombreCategoria]];
                    $maximoGrupoActual[$nombreCategoria]=$maximosRangos[$contadorGrupo[$nombreCategoria]];
                }
                else {
                // Subsequent groups, of rangoSucesivo size
                    $grupoActual[$nombreCategoria]="Top ".($maximoGrupoActual[$nombreCategoria]+1)."-".($maximoGrupoActual[$nombreCategoria]+$rangoSucesivo);
                    $maximoGrupoActual[$nombreCategoria]=$maximoGrupoActual[$nombreCategoria]+$rangoSucesivo;                
                }
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraIzq"]="";
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraCntr"]=$grupoActual[$nombreCategoria];
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["cabeceraDch"]="";
            }

            $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["totalFilas"][$datos["tipo"]]++;

            if(!is_array($returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["filas"]))
                $returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["filas"]=array();

            array_push($returnData["grupos"][$nombreCategoria][$grupoActual[$nombreCategoria]]["filas"],$datos);

            if ($datos["primeraOcurrencia"]==1)
                $datos["primeraOcurrencia"]=0;
        }
        
    }
    
}
if($tipoGrupos=="lugar"||$tipoGrupos=="tematica") //Is it needed for lugar? 
    ksort($returnData["grupos"]);

$returnData["tipo"]="organizaciones";
$returnData["orden"]=$tipoGrupos;

?>