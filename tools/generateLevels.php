<?php
error_reporting(E_ERROR);
if($_POST["regionIDs"]!="")
{
	include_once "../db.php";
	$ids=json_decode($_POST["regionIDs"],true);
	$sql='SELECT MAX(id)+1 AS nextID FROM lugares_shp WHERE id>=777000000 AND id<778000000';
	$link=connect();
    mysql_query('SET CHARACTER SET utf8',$link);
	$result=mysql_query($sql,$link);
	$fila=mysql_fetch_assoc($result);
	$nextID=$fila["nextID"];
	$sql="INSERT INTO lugares_shp (id,nombre,provincia,nivel) VALUES ('$nextID','{$_POST["nombre"]}','28','7')";	
	mysql_query($sql,$link);
	foreach($ids as $id)
	{
		$sql="UPDATE lugares_shp SET idPadre='$nextID' WHERE id='$id'";
		mysql_query($sql,$link);
	}
}

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
		color='#00ff00';
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
	  /*map.on('click', function(e) 
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

	  $.getJSON("getLevels.php", 
      {
          dataType: 'json',
          tipo:'8',
          provincia:'28'
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

cargarMapa([40.49166,-3.364136], 10);

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

















