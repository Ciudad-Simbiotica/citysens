<?php
	error_reporting(0);
	include_once "db.php";
	$respuesta=getDatosLugar($_GET["idLugar"]);

	//Breadcrumbs

	$lugares=getAllAncestors($_GET["idLugar"]);
	$primera=true;
	$cantidad=0;
	$breadcrumbs=array();
	$cantidad=0;
	for($i=1;$i<=10;$i++)
	{
		if(isset($lugares[$i]))
		{
			if($lugares[$i]["nombreCorto"]!="")
				$nombre=$lugares[$i]["nombreCorto"];
			else
				$nombre=$lugares[$i]["nombre"];

			if($cantidad<count($lugares)-2)	//Todos menos el último
				if(strlen($nombre)>7)		//Si es de más de 7 caracteres lo acortamos a 7
					$nombre=substr($lugares[$i]["nombre"],0,4)."...";					
			
			array_push($breadcrumbs,array($lugares[$i]["id"],$nombre));
			$cantidad++;
		}
	}
	$respuesta["breadcrumbs"]=$breadcrumbs;



	echo json_encode($respuesta);
?>