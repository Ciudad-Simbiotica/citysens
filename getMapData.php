<?php
	error_reporting(0);
	include_once "db.php";
	
    $respuesta=getDatosLugarBase($_GET["idLugar"]); //
//  $respuesta=getDatosLugar($_GET["idLugar"]);
    $nivelColindantes=getNivelTerritorio($_GET["idLugar"]);
	$respuesta["nivelColindantes"]=$nivelColindantes;

        //Breadcrumbs

	$lugares=  getFertileAncestors($respuesta["id"]);
	$cantidad=0;
	$breadcrumbs=array();
	for($i=1;$i<=10;$i++)
	{
		if(isset($lugares[$i]))
		{
                        $cantidad++;
                        if($cantidad<count($lugares))	//Todos menos el último                         
                        {
                            if($lugares[$i]["nombreCorto"]!="")
				$nombreBreadcrumb=$lugares[$i]["nombreCorto"];
                            else
				$nombreBreadcrumb=$lugares[$i]["nombre"];

				if(strlen($nombreBreadcrumb)>9)		//Si es de más de 9 caracteres lo acortamos a 6 y puntos suspensivos
					$nombreBreadcrumb=mb_substr($lugares[$i]["nombre"],0,6)."...";					
                        }
                        else {
                             if($lugares[$i]["nombre"]!="")
				$nombreBreadcrumb=$lugares[$i]["nombre"];
                            else
				$nombreBreadcrumb=$lugares[$i]["nombreCorto"];
                        
                        }
			array_push($breadcrumbs,array($lugares[$i]["id"],$nombreBreadcrumb,$lugares[$i]["nombre"]));
		}
	}
	$respuesta["breadcrumbs"]=$breadcrumbs;

	echo json_encode($respuesta);
?>