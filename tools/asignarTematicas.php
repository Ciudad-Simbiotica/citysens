<?php
include_once "../../db.php";
error_reporting(0);

ini_set('default_charset', 'utf-8');
// script deactivated unless needed
//exit();

$link=connect();
mysqli_query($link,"SET NAMES 'utf8'");
$sql="SELECT * FROM entidades where idEntidad<554";
$result=mysqli_query($link,$sql);
$entidades=array();
$tematicasAsignadas=array();
$etiquetasAsignadas=array();

while($fila=mysqli_fetch_assoc($result))
{
	array_push($entidades,$fila);
        $etiquetasAsignadas[$fila['etiquetas']]="";
}

foreach($entidades as $entidad)
{ 
	
    switch($entidad['clasificacion']) {
	case "CASAS REGIONALES":         
            $asignacion=[$entidad['etiquetas'],8];            
            $tematicasAsignadas[]=$asignacion;
            $asignacion=[$entidad['etiquetas'],35];
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Culturales":
            $asignacion=[$entidad['etiquetas'],8];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Deportivas":
            $asignacion=[$entidad['etiquetas'],10];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "EDUCATIVAS":
            $asignacion=[$entidad['etiquetas'],6];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Social":
            $asignacion=[$entidad['etiquetas'],2];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "INTERCULTURAL Y ONG":
            $asignacion=[$entidad['etiquetas'],7];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Juveniles":
            $asignacion=[$entidad['etiquetas'],16];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Padres":
        case "Infancia":
            $asignacion=[$entidad['etiquetas'],34];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Medio Ambiente":
            $asignacion=[$entidad['etiquetas'],20];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Mujeres":
            $asignacion=[$entidad['etiquetas'],21];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "PEÑAS":
            $asignacion=[$entidad['etiquetas'],35];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "POLITICAS":
            $asignacion=[$entidad['etiquetas'],9];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Religiosas":
            $asignacion=[$entidad['etiquetas'],33];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Mayores":
            $asignacion=[$entidad['etiquetas'],19];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Vecinos":
            $asignacion=[$entidad['etiquetas'],3];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "EMPRESARIOS, COMERCIANTES Y CONSUMIDORES":
            $asignacion=[$entidad['etiquetas'],13];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "Sanitarias":
            $asignacion=[$entidad['etiquetas'],23];            
            $tematicasAsignadas[]=$asignacion;
            break;
        default:
            $asignacion=[$entidad['etiquetas'],38];            
            $tematicasAsignadas[]=$asignacion;
            break;
    }

    switch($entidad["tipo"]) {
        case "asociaciones de consumidores":
            $asignacion=[$entidad["etiquetas"],6];            
            $tematicasAsignadas[]=$asignacion;
            break;
        case "AMPAS CC":
        case "AMPAS CP":
        case "AMPAS EI":
        case "AMPAS IES":
        case "AMPAS varias":
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="ampa";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",ampa";   
            }
            break;
        case "escuela de teatro":     
             if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="teatro,escuela";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",teatro,escuela";   
            }
            break;
        case "escuela de animación sociocultural":
             if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="animación sociocultural,escuela";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",animación sociocultural,escuela";   
            }
            break;
        case "escuela de música":
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="música,escuela";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",música,escuela";   
            }
            break;
        case "":
        case "festiva":
        case "taurina":
        case "partidos políticos con representación municipal":
        case "general":
        case "varios":
        case "asociaciones de comerciantes":
        case "asociaciones de empresarios":
            break;
        case "federaciones":
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="federación";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",federación";   
            }
            break;
        case "universitarias":
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="universidad";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",universidad";   
            }
            break;

        case "futbolistica":
        case "futbol":
        case "futbolística":
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="fútbol";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",fútbol";   
            }
            break;
        case "sindicatos":       
             if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]="sindicato";
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",sindicato";   
            }
            break;    
        default:
            if ($etiquetasAsignadas[$entidad["etiquetas"]]==""){
                $etiquetasAsignadas[$entidad["etiquetas"]]=$entidad["tipo"];
            }
            else{
                $etiquetasAsignadas[$entidad["etiquetas"]]=$etiquetasAsignadas[$entidad["etiquetas"]].",".$entidad["tipo"];   
            }
            break;  
    }            
        
            
        
}
echo "<pre>";

	foreach($tematicasAsignadas as $asignacion)
	{
		$sql="INSERT INTO entidades_tematicas (idEntidad, idTematica) VALUES ('$asignacion[0]','$asignacion[1]')";
                echo $sql."\n".PHP_EOL;
		mysqli_query($link,$sql);
	}
        
   foreach($etiquetasAsignadas as $idEntidad => $valor)
	{
		$sql="UPDATE entidades SET etiquetas='$valor' where idEntidad='$idEntidad'";
                echo $sql."\n".PHP_EOL;
		mysqli_query($link,$sql);
	}

?>