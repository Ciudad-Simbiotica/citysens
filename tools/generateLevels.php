<?php
/*
// PHP part commented to prevent usage unless required.
// Allows to generate regions by selecting a set of municipalites. 
 
error_reporting(E_ERROR);
if($_POST["regionIDs"]!="")
{
	include_once "../db.php";
	$ids=json_decode($_POST["regionIDs"],true);
    $provincia=$_POST["provincia"];
        // Region: level(1)+country(2)+Province(2)+region(4) (in province) CORRECTO - Pero faltan casi todas.
        //           701280002 (Corredor del Henares)
    $idregionmin=701000000+10000*$provincia;
    $idregionmax=701009999+10000*$provincia;
	$sql='SELECT MAX(id)+1 AS nextID FROM lugares_shp WHERE id>='.$idregionmin.' AND id<'.$idregionmax;
	$link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
	$result=mysql_query($sql,$link);
	$fila=mysql_fetch_assoc($result);
    $nextID=$fila["nextID"];
	if ($nextID==null)
      $nextID=701000001+10000*$provincia;
    
    
    $sql="SELECT id as ccaaID from lugares_shp where nivel=6 and provincia=$provincia";	
	mysql_query($sql,$link);
    $result=mysql_query($sql,$link);
    $fila=mysql_fetch_assoc($result);
    $ccaaID=$fila["ccaaID"];
    
	$sql="INSERT INTO lugares_shp (id,nombre,provincia,nivel,idPadre) VALUES ('$nextID','{$_POST["nombre"]}','$provincia','7','$ccaaID')";	
	mysql_query($sql,$link);
	foreach($ids as $id)
	{
		$sql="UPDATE lugares_shp SET idPadre='$nextID' WHERE id='$id'";
		mysql_query($sql,$link);
	}
}
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <link href="../css/style.css" rel="stylesheet" type="text/css" />
 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
 <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
 <link rel="stylesheet" type="text/css" href="../js/tagsinput/jquery.tagsinput.css" />
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
 <script src="../js/jquery.custom-animations.js"></script>
 <script type="text/javascript" src="../js/tagsInput/jquery.tagsinput.js"></script>
 <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
 <script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
 <script src="../js/leaflet-plugins-master/layer/tile/Google.js"></script>

</head>

<body>
 <div id="map" style="float:left"></div>
  <div style="float:left;padding:10px;">
  <form method=POST id=formulario>
	Región: <input type=text name=nombre id=nombre size=80><input type=submit value=Guardar>
	<input type=hidden name=regionIDs id=regionIDs>
    <input type=hidden name=provincia id=provincia value=3>
  </form>
	 <ul id='sitios'>
	 </ul>
 </div>

 <script type="text/javascript">
	function addPolygonToMap(id,url,texto,color,idPadre)
	{
      var colorSeleccionado='#ff0000';
      if(idPadre>0)
      {
        // Quick & dirty way to get several colours.
		switch(idPadre%10)
		{
			case 1:
				color='#444444';
				break;
			case 2:
				color='#00ff00';
				break;
			case 3:
				color='#0000ff';
				break;
			case 4:
				color='#ffff00';
				break;
			case 5:
				color='#ff00ff';
				break;
			case 6:
				color='#00ffff';
				break;
			case 7:
				color='#44ff00';
				break;
            case 8:
                color='#ff44ff';
                break;
            case 9:
                color='#ffff44';
                break;
            case 0:
                color='#44ff44';
                
        }
		colorSeleccionado='#ff9900';
      }
      $.ajax({
	    type: "POST",
	    url: url,
	    dataType: 'json',
	    success: function (response) 
	    {
	      //alert(response);
	      geojsonLayer = L.geoJson(response,{fillColor: color,weight: 1}).addTo(map);
	      geojsonLayer.on('click',function(e)
	      {
      		if(jQuery.inArray(id,arrayIDs)>=0)
	      	{
	      		//Ya estaba
		      	this.setStyle({fillColor: color});
		      	arrayIDs = jQuery.grep(arrayIDs, function(value) 
	    		{
	      			return value != id;
	    		});
	    		$("li:contains('"+texto+"')").remove();
	      	}
	      	else
	      	{
				if(idPadre>0)
					alert("Cuidado, "+texto+" ya está asignado a otra región");
				this.setStyle({fillColor: colorSeleccionado});
		      	arrayIDs.push(id);
		      	$('#sitios').append(
				    $('<li>').append(
				        $('<span>').append(texto)
				));    
	      	}
	      	$("#regionIDs").val(JSON.stringify(arrayIDs));
	      	
	      });
	    }
      });  
	}

	function cargarMapa(coordinates,zoom)
	{
	  //Creamos el mapa
	  var map = L.map('map',{zoomControl: false,attributionControl: false}).setView(coordinates,zoom);
	  /*
      map.on('click', function(e) 
	  {
	    alert('Has hecho click en: '+e.latlng.toString());
	  });
      */

	  /*
	  map.dragging.disable();
	  map.touchZoom.disable();
	  map.doubleClickZoom.disable();
	  map.scrollWheelZoom.disable();
	  map.boxZoom.disable();
	  map.keyboard.disable();
	  */

	  window.map=map;
	  var ggl = new L.Google();
	  L.Google('roadmap');
	  map.addLayer(ggl);

      //addPolygonToMap("Madrid","../shp/geoJSON/6/Madrid.geojson","Madrid",'#aaaaff',"-1");

	  
	  $.getJSON("getLevels.php", 
      {
          dataType: 'json',
          tipo:'8',
          provincia:$("#provincia").val()
      })
      .done(function(data) 
      {
        $.each(data, function(i,datos)
        {
          addPolygonToMap(datos[0],"../shp/geoJSON/8/"+datos[0]+".geojson",datos[1],'#aaaaff',datos[5]);
        });
      });
	 

	}

	function irACoordenadas(coordinates,zoom)
	{
	  window.map.panTo(coordinates);
	}

$('#map').width(800);
$('#map').height(600);

cargarMapa([40,-3], 6);
// TODO: Instead of this: use coordinates of the provice: xmax, ymin?

var arrayIDs = new Array();

$("#formulario").submit(function()
{    
    if($("#nombre").val()=="")
    { 
    	alert("No te olvides de darle un nombre a la regi&oacute;n");
    	return false;
    }
    if(arrayIDs.length==0)
    { 
    	alert("No has seleccionado ninguna ciudad");
    	return false;
    }
    return true;
});

 </script>
</body>
</html>