<?php
include_once "db.php";
error_reporting(E_ERROR);

//TODO: Needs to be updated to new behaviour

$data=json_decode(utf8_encode(urldecode($_POST["data"])),true);//),true);

$tematicas=array();
$etiquetas=array();

foreach($data["tematicas"] as $tematica)
{
	if($tematica["idTematica"]==0)
	{
		//Es una etiqueta
		array_push($etiquetas,$tematica["tematica"]);
	}
	else
	{
		//Es una temática
		array_push($tematicas,$tematica["idTematica"]);
	}
}
$cadenaEtiquetas=join(",",$etiquetas);

if($data["idTerritorio"]==0)
{
	//Es una direccion nueva
	$datosNuevoLugar=createPlace($data["nombreLugar"],$data["lugar"],$data["coordenadas"]["lat"],$data["coordenadas"]["lng"],$data["idCiudad"]);;
	$data["idTerritorio"]=$datosNuevoLugar["idTerritorio"];
	$data["idDistritoPadre"]=$datosNuevoLugar["idDistritoPadre"];
}
else
{
	$data["idDistritoPadre"]=getDistritoPadreDireccion($data["idTerritorio"]);
}

//print_r($data);


$eventData["fecha"]=substr($data["fecha"],6,4)."-".substr($data["fecha"],3,2)."-".substr($data["fecha"],0,2)." ".$data["horaInicio"];
if($data["horaFinal"]!="")
	$eventData["fechaFin"]=substr($data["fecha"],6,4)."-".substr($data["fecha"],3,2)."-".substr($data["fecha"],0,2)." ".$data["horaFinal"];
else
	$eventData["fechaFin"]=NULL;

$eventData["clase"]="eventos";
$eventData["tipo"]="convocatoria";
$eventData["titulo"]=$data["titulo"];
$eventData["texto"]=$data["descripcion"];
$eventData["lugar"]=$data["nombreLugar"];
$eventData["idEntidad"]=158;	//Forzado de CLUB DARDOS TREBOL
$eventData["temperatura"]=1;
$eventData["tematicas"]=$tematicas;
$eventData["idPlace"]=$data["idTerritorio"];  //  idTerritorio??? Review
$eventData["url"]=$data["webEvento"];
$eventData["email"]=$data["email"];
$eventData["etiquetas"]=$cadenaEtiquetas;
$eventData["repeatsAfter"]=0;
$eventData["eventoActivo"]=0;

createEvent($eventData);

?>