<?php
	error_reporting(0);
	include_once "db.php";
	
    $idTerritorio=$_GET["idTerritorio"];
    if($_GET["alrededores"]!=0)
      $respuesta=getDatosLugar($idTerritorio);
    else
      $respuesta=getDatosLugarBase($idTerritorio);
    
// For the case of districts, whose surface normally is only partially covered by the neighbourhoods within, zoom is adjusted 
// to the surface covered by the neighbourhood polygons 
    if ($respuesta["nivel"]==9 && $respuesta["idDescendiente"]!=0) 
    {
      $coordenadasInteriores=getCoordenadasInteriores($respuesta["id"]);
      $respuesta["xmax"]=$coordenadasInteriores["xmax"];
      $respuesta["ymax"]=$coordenadasInteriores["ymax"];
      $respuesta["xmin"]=$coordenadasInteriores["xmin"];
      $respuesta["ymin"]=$coordenadasInteriores["ymin"];      
    }
// For the case of neighborhoods and city surroundings, we want a special navigation with no uncles
    if ($respuesta["nivel"]==10 ||($_GET["alrededores"]!=0 && $respuesta["nivel"]==8)) 
    {
      //$coordenadasColindantes=getCoordenadasColindantes($respuesta["nivel"],$respuesta["xmin"],$respuesta["xmax"],$respuesta["ymin"],$respuesta["ymax"]); 
      //$coordenadasColindantes=getCoordenadasCentroidesColindantes($respuesta["nivel"],$respuesta["xmin"],$respuesta["xmax"],$respuesta["ymin"],$respuesta["ymax"]);
      $vecindad=$respuesta["vecinos"];
      if ($vecindad<>'') {
        $vecindad.=",".$idTerritorio;
      }
      else {
        $vecindad=$idTerritorio;
      }
        
      $coordenadasColindantes=getCoordenadasVecinos($respuesta["nivel"],$vecindad);      

      // In case of cities having a very big neighbour, coordinates are too wide and territory loses central position. Check for it and correct, using a 0,05 margin 
//      if ($respuesta["nivel"]==8)
//      {
//        if ($coordenadasColindantes["xmax"]>$respuesta["xmax"]+0.05)
//          $coordenadasColindantes["xmax"]=$respuesta["xmax"]+0.05;
//        if ($coordenadasColindantes["ymax"]>$respuesta["ymax"]+0.05)
//          $coordenadasColindantes["ymax"]=$respuesta["ymax"]+0.05;
//        if ($coordenadasColindantes["xmin"]<$respuesta["xmin"]-0.05)
//          $coordenadasColindantes["xmin"]=$respuesta["xmin"]-0.05;
//        if ($coordenadasColindantes["ymin"]<$respuesta["ymin"]-0.05)
//          $coordenadasColindantes["ymin"]=$respuesta["ymin"]-0.05;  
//      }
      
      // Coordinates might not be satisfactory for a peripheric territory, as they get cut. Check for it and correct.
//      $respuesta["xmax"]=($respuesta["xmax"]>$coordenadasColindantes["xmax"]?$respuesta["xmax"]:$coordenadasColindantes["xmax"]);
//      $respuesta["ymax"]=($respuesta["ymax"]>$coordenadasColindantes["ymax"]?$respuesta["ymax"]:$coordenadasColindantes["ymax"]);
//      $respuesta["xmin"]=($respuesta["xmin"]<$coordenadasColindantes["xmin"]?$respuesta["xmin"]:$coordenadasColindantes["xmin"]);
//      $respuesta["ymin"]=($respuesta["ymin"]<$coordenadasColindantes["ymin"]?$respuesta["ymin"]:$coordenadasColindantes["ymin"]);
      
      $respuesta["xmax"]=$coordenadasColindantes["xmax"];
      $respuesta["ymax"]=$coordenadasColindantes["ymax"];
      $respuesta["xmin"]=$coordenadasColindantes["xmin"];
      $respuesta["ymin"]=$coordenadasColindantes["ymin"];
    }
    
    //Data for the Breadcrumbs
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