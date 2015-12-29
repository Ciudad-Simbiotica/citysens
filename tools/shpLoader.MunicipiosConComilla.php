<?php
function ToLL($north, $east, $utmZone)
{ 
  // This is the lambda knot value in the reference
  $LngOrigin = Deg2Rad($utmZone * 6 - 183);

  // The following set of class constants define characteristics of the
  // ellipsoid, as defined my the WGS84 datum.  These values need to be
  // changed if a different dataum is used.    

  $FalseNorth = 0;   // South or North?
  //if (lat < 0.) FalseNorth = 10000000.  // South or North?
  //else          FalseNorth = 0.   

  $Ecc = 0.081819190842622;       // Eccentricity
  $EccSq = $Ecc * $Ecc;
  $Ecc2Sq = $EccSq / (1. - $EccSq);
  $Ecc2 = sqrt($Ecc2Sq);      // Secondary eccentricity
  $E1 = ( 1 - sqrt(1-$EccSq) ) / ( 1 + sqrt(1-$EccSq) );
  $E12 = $E1 * $E1;
  $E13 = $E12 * $E1;
  $E14 = $E13 * $E1;

  $SemiMajor = 6378137.0;         // Ellipsoidal semi-major axis (Meters)
  $FalseEast = 500000.0;          // UTM East bias (Meters)
  $ScaleFactor = 0.9996;          // Scale at natural origin

  // Calculate the Cassini projection parameters

  $M1 = ($north - $FalseNorth) / $ScaleFactor;
  $Mu1 = $M1 / ( $SemiMajor * (1 - $EccSq/4.0 - 3.0*$EccSq*$EccSq/64.0 - 5.0*$EccSq*$EccSq*$EccSq/256.0) );

  $Phi1 = $Mu1 + (3.0*$E1/2.0 - 27.0*$E13/32.0) * sin(2.0*$Mu1);
    + (21.0*$E12/16.0 - 55.0*$E14/32.0)           * sin(4.0*$Mu1);
    + (151.0*$E13/96.0)                          * sin(6.0*$Mu1);
    + (1097.0*$E14/512.0)                        * sin(8.0*$Mu1);

  $sin2phi1 = sin($Phi1) * sin($Phi1);
  $Rho1 = ($SemiMajor * (1.0-$EccSq) ) / pow(1.0-$EccSq*$sin2phi1,1.5);
  $Nu1 = $SemiMajor / sqrt(1.0-$EccSq*$sin2phi1);

  // Compute parameters as defined in the POSC specification.  T, C and D

  $T1 = tan($Phi1) * tan($Phi1);
  $T12 = $T1 * $T1;
  $C1 = $Ecc2Sq * cos($Phi1) * cos($Phi1);
  $C12 = $C1 * $C1;
  $D  = ($east - $FalseEast) / ($ScaleFactor * $Nu1);
  $D2 = $D * $D;
  $D3 = $D2 * $D;
  $D4 = $D3 * $D;
  $D5 = $D4 * $D;
  $D6 = $D5 * $D;

  // Compute the Latitude and Longitude and convert to degrees
  $lat = $Phi1 - $Nu1*tan($Phi1)/$Rho1 * ( $D2/2.0 - (5.0 + 3.0*$T1 + 10.0*$C1 - 4.0*$C12 - 9.0*$Ecc2Sq)*$D4/24.0 + (61.0 + 90.0*$T1 + 298.0*$C1 + 45.0*$T12 - 252.0*$Ecc2Sq - 3.0*$C12)*$D6/720.0 );

  $lat = Rad2Deg($lat);

  $lon = $LngOrigin + ($D - (1.0 + 2.0*$T1 + $C1)*$D3/6.0 + (5.0 - 2.0*$C1 + 28.0*$T1 - 3.0*$C12 + 8.0*$Ecc2Sq + 24.0*$T12)*$D5/120.0) / cos($Phi1);

  $lon = Rad2Deg($lon);

  // Create a object to store the calculated Latitude and Longitude values
  $PC_LatLon['lat'] = $lat;
  $PC_LatLon['lon'] = $lon;

  // Returns a PC_LatLon object
  return $PC_LatLon;
}

include_once "ShapeFile.inc.php";
include "../db.php";
set_time_limit(0);
error_reporting(E_ALL);

//Script not active, unless needed.
//exit();

//$shp = new ShapeFile("lineas_limite/SHP_ETRS89/poligonos_municipio_etrs89/poligonos_municipio_etrs89.shp"); // along this file the class will use file.shx and file.dbf
//$shp = new ShapeFile("Distritos-1/Distritos.shp"); // along this file the class will use file.shx and file.dbf
//$shp = new ShapeFile("lineas_limite/SHP_ETRS89/poligonos_provincia_etrs89/poligonos_provincia_etrs89.shp"); // along this file the class will use file.shx and file.dbf
$shp = new ShapeFile("lineas_limite/SHP_ETRS89/poligonos_municipio_etrs89/poligonos_municipio_etrs89.shp"); // along this file the class will use file.shx and file.dbf

$link=connect();
$i=0;
while ($record = $shp->getNext()) 
{
	$i++; 
    //echo $i;

	$datos=$record->getDbfData();

// does not apply	$codbdt=trim(utf8_encode($datos["CODBDT"]));
// does not apply	$geocodigo=trim(utf8_encode($datos["GEOCODIGO"]));
// does not apply	$desbdt=trim(utf8_encode($datos["DESBDT"]));
// not needed	$deleted=trim(utf8_encode($datos["deleted"]));
  $nombre = trim(utf8_encode($datos["NOMBRE"]));
  $findme="'";
 // if (strpos($nombre,$findme) !== false){
   if ($nombre=="Nules"){
    
   // $nombreformat = str_replace("\'", "\\'", $datos("NOMBRE"));   
     
   
    $codine = trim(utf8_encode($datos["CODGOINE"]));
    $cod_ccaa = trim(utf8_encode($datos["COD_CCAA"]));
    $provincia = trim(utf8_encode($datos["PROVINCIA"]));
    
    $nombre = addslashes($nombre);  
    

    $coordenadas=$record->getShpData();   

   
    /* There is no need to convert coordinates. They can be used directly for the geoJSON
    $latlong=ToLL($coordenadas["ymin"],$coordenadas["xmin"],30);
    $xmin=$latlong["lon"];
    $ymin=$latlong["lat"];

    
    $latlong=ToLL($coordenadas["ymax"],$coordenadas["xmax"],30);

    $xmax=$latlong["lon"];
    $ymax=$latlong["lat"];

    //print_r($coordenadas);

     */
    
    // Seems the transformation is not needed. The existing data from Madrid is consistent with the one in the source file.
    $xmin=$coordenadas["xmin"];
    $ymin=$coordenadas["ymin"];
    $xmax=$coordenadas["xmax"];
    $ymax=$coordenadas["ymax"];
       
    $geoJSON="";
    $firstPart=true;
    if($coordenadas["numparts"]==1)
        $geoJSON='{"type":"Polygon","coordinates":[[';
    else
        $geoJSON='{"type":"MultiPolygon","coordinates":[[[';

    foreach($coordenadas["parts"] as $idPart=>$part)
    {
        $first=true;
        if(!$firstPart)
            $geoJSON.="]],[[";
        $firstPart=false;
        foreach($part["points"] as $point)
        {
            /*$latlong=ToLL($point["y"],$point["x"],30);
            $x=$latlong["lon"];
            $y=$latlong["lat"];
            ////print_r($latlong);
            //exit();
            */
            $x=$point["x"];
            $y=$point["y"];
            $coordenada="[$x,$y]";
            if(!$first)
                $geoJSON.=",";
            $first=false;
            $geoJSON.=$coordenada;
        }
    }

	 	if($coordenadas["numparts"]==1)
			$geoJSON.=']]}';
		else
			$geoJSON.=']]]}';

		// id codes are going to be:
        // CCAA: level(1)+country(2)+CCAA(6)    CORRECTO
        //           401000009
        // Province: level(1)+country(2)+CCAA(2)+province(4)  CORRECTO
        //           601XX0028 (Madrid)
        // Region: level(1)+country(2)+Province(2)+region(4) (in province) CORRECTO - Pero faltan casi todas.
        //           701280002 (Corredor del Henares)
        // City: level(1)+country(2)+Province(2)+cityNumber(4) (in province) CORRECTO //Faltan asignaciones a región
        //           801280005
        // District: level(1)+country(2)+Province(2)+DistrictNumber(4)(in province) CORRECTO
        //           901280009
		//$id=999000000+$i;
    $idmunicipiomin=801000000+10000*$provincia;
    $idmunicipiomax=801009999+10000*$provincia;
	$sql='SELECT MAX(id)+1 AS nextID FROM lugares_shp WHERE id>='.$idmunicipiomin.' AND id<'.$idmunicipiomax;
	$link=connect();
    mysqli_query($link, 'SET CHARACTER SET utf8');
	$result=mysqli_query($link, $sql);
	$fila=mysqli_fetch_assoc($result);
    $nextID=$fila["nextID"];
	if ($nextID==null)
      $nextID=801000001+10000*$provincia;
        $id=801000000+$provincia*10000+$i;
		//echo $id.PHP_EOL;
		file_put_contents("geoJSON/8new/$nextID.geojson", $geoJSON);
		$sql=utf8_decode("INSERT INTO lugares_shp 
				(id,nombre,provincia,ine,
					xmin,ymin,xmax,ymax,nivel,activo)
				VALUES ('$nextID','$nombre','$provincia','$codine',
					'$xmin','$ymin','$xmax','$ymax','8','0')");	
		//echo $sql;
		
		mysqli_query($link,$sql);
		
		//echo $geoJSON;

		//echo $id."\t".$nombre."\t".$ine."\t".$jurisdiccion."\t".$provincia."\t".$deleted.PHP_EOL;
		//exit();
    }
}   
    //echo PHP_EOL;}	

?>
