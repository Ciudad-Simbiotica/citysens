<?php
include_once "db.php";
error_reporting(E_ERROR);

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

if($data["idLugar"]==0)
{
	//Es una direccion nueva
	$datosNuevoLugar=crearNuevaDireccion($data["nombreLugar"],$data["lugar"],$data["coordenadas"]["lat"],$data["coordenadas"]["lng"],$data["idCiudad"]);;
	$data["idLugar"]=$datosNuevoLugar["idLugar"];
	$data["idDistritoPadre"]=$datosNuevoLugar["idDistritoPadre"];
}
else
{
	$data["idDistritoPadre"]=getDistritoPadreDireccion($data["idLugar"]);
}

//print_r($data);


$datosNuevoEvento["fecha"]=substr($data["fecha"],6,4)."-".substr($data["fecha"],3,2)."-".substr($data["fecha"],0,2)." ".$data["horaInicio"];
if($data["horaFinal"]!="")
	$datosNuevoEvento["fechaFin"]=substr($data["fecha"],6,4)."-".substr($data["fecha"],3,2)."-".substr($data["fecha"],0,2)." ".$data["horaFinal"];
else
	$datosNuevoEvento["fechaFin"]=NULL;

$datosNuevoEvento["clase"]="eventos";
$datosNuevoEvento["tipo"]="convocatoria";
$datosNuevoEvento["titulo"]=$data["titulo"];
$datosNuevoEvento["texto"]=$data["descripcion"];
$datosNuevoEvento["lugar"]=$data["nombreLugar"];
$datosNuevoEvento["x"]=$data["coordenadas"]["lng"];
$datosNuevoEvento["y"]=$data["coordenadas"]["lat"];
$datosNuevoEvento["idDistritoPadre"]=$data["idDistritoPadre"];
$datosNuevoEvento["idAsociacion"]=158;	//Forzado de CLUB DARDOS TREBOL
$datosNuevoEvento["temperatura"]=1;
$datosNuevoEvento["tematicas"]=$tematicas;
$datosNuevoEvento["idDireccion"]=$data["idLugar"];
$datosNuevoEvento["url"]=$data["webEvento"];
$datosNuevoEvento["email"]=$data["email"];
$datosNuevoEvento["etiquetas"]=$cadenaEtiquetas;
$datosNuevoEvento["repeatsAfter"]=0;
$datosNuevoEvento["eventoActivo"]=0;

crearNuevoEvento($datosNuevoEvento);

?>