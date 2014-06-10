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
 <div id="map"></div>
 <script type="text/javascript">
	function addPolygonToMap(url,texto,color)
	{
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
	        alert(texto+": "+e.latlng.toString());
	      });
	    }
	});  
	}

	function cargarMapa(coordinates,zoom)
	{
	  //Creamos el mapa
	  var map = L.map('map',{zoomControl: false,attributionControl: false}).setView(coordinates,zoom);
	  map.on('click', function(e) 
	  {
	    alert('Has hecho click en: '+e.latlng.toString());
	  });
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

	  //Cargamos los perímetros
	  /*
	  addPolygonToMap("/citysens/mapItDownloads/Madrid/579300.geojson",'alcalá','#ffffff');



	  	

		$.getJSON("/citysens/mapItDownloads/Madrid/579300.geojson", 
		  {
		    format: "json"
		  })
		    .done(function(data) 
		    {
		      //Esperamos a que se hayan borrado los grupos (por si acaba antes) antes de clonar
		      $.each(data.coordinates, function(grupo,filas)
		      {
		      	i=0;
		        $.each(filas,function(i,item)
		        {
		          //L.marker([item[1],item[0]],{opacity:0.5}).addTo(map).bindPopup(""+i);//.openPopup();
		          i++;
		        });
		      
		      });
				
		    });  
		
		$.getJSON("/citysens/mapItDownloads/Madrid/Distrito II.geojson", 
		  {
		    format: "json"
		  })
		    .done(function(data) 
		    {
		      //Esperamos a que se hayan borrado los grupos (por si acaba antes) antes de clonar
		      $.each(data.coordinates, function(grupo,filas)
		      {
		      	i=0;
		        $.each(filas,function(i,item)
		        {
		          //L.marker([item[1],item[0]]).addTo(map).bindPopup(""+i);//.openPopup();
		          i++;
		        });
		      
		      });
				
		    });  
	   
		
	   addPolygonToMap("/citysens/mapItDownloads/DistritoV.geojson",'Distrito V','#aaffff');
	   addPolygonToMap("/citysens/mapItDownloads/DistritoIV.geojson",'Distrito IV','#aaffff');
	   addPolygonToMap("/citysens/mapItDownloads/DistritoIII.geojson",'Distrito III','#aaffff');
	   addPolygonToMap("/citysens/mapItDownloads/DistritoII.geojson",'Distrito II','#aaffff');


		

	  addPolygonToMap("mapItDownloads/Madrid/Distrito I.geojson",'Distrito I','#aaffff');
	  //addPolygonToMap("mapItDownloads/Madrid/Distrito II.geojson",'Distrito II','#aaffff');
	  //addPolygonToMap("mapItDownloads/Madrid/Distrito III.geojson",'Distrito III','#aaffff');
	  //addPolygonToMap("mapItDownloads/Madrid/Distrito IV.geojson",'Distrito IV','#aaffff');
	  //addPolygonToMap("mapItDownloads/Madrid/Distrito V.geojson",'Distrito V','#aaffff');
	  */
	  //addPolygonToMap("http://localhost:8888/citysens/shp/200001442.json",'Torrejón','#aaffff');
	  //addPolygonToMap("http://localhost:8888/citysens/shp/AlcalaShp.geojson",'Alcalá','#aaffff');
	  //addPolygonToMap("http://localhost:8888/citysens/shp/geoJSON/8/4284.geojson",'Alcalá','#aaffff');

      //Aquí cargaríamos los distritos
      /*
      addPolygonToMap("shp/geoJSON/9/00501.geojson","Distrito I",'#aaaaff',true);
      addPolygonToMap("shp/geoJSON/9/00502.geojson","Distrito II",'#aaaaff',true);
      addPolygonToMap("shp/geoJSON/9/00503.geojson","Distrito III",'#aaaaff',true);
      addPolygonToMap("shp/geoJSON/9/00504.geojson","Distrito IV",'#aaaaff',true);
      addPolygonToMap("shp/geoJSON/9/00505.geojson","Distrito V",'#aaaaff',true);
		*/
	  
	  <?

	  	//http://localhost:8888/citysens/mapChecker.php?type=8&xmin=-3.64643&ymin=40.37454&xmax=-3.10192&ymax=40.60744
	  	include_once "../db.php";
	  	error_reporting(0);
		$link=connect();

		$sql="SELECT * FROM lugares_shp WHERE 
				nivel='{$_GET["type"]}'";
				 //AND	provincia='28'";
				/*AND
				
				 NOT(   xmin 			> {$_GET["xmax"]} 
			  		OR {$_GET["xmin"]} 	> xmax
			  		OR ymax     		< {$_GET["ymin"]} 
			  		OR {$_GET["ymax"]} 	< ymin)
				 ";*/
				
		$result=mysql_query($sql,$link);
		while($fila=mysql_fetch_assoc($result))
		{
			$idFichero=str_pad($fila["geocodigo"],5,0,STR_PAD_LEFT);
			echo "addPolygonToMap('../shp/geoJSON/{$_GET["type"]}/$idFichero.geojson','{$fila["desbdt"]}','#aaaaff');".PHP_EOL;
			echo "L.marker([{$fila["ycentroid"]}, {$fila["xcentroid"]}],{draggable:true,title:'{$fila["id"]}'}).addTo(map)
			.on('dragend',function(e){

				var y=this.getLatLng().lat;
				var x=this.getLatLng().lng;
				var title=this.options.title;

				$.getJSON(\"updateLatLongCentroide.php\", 
			    {
		          dataType: 'json',
		          xcentroid:x,
		          ycentroid:y,
		          id:title
		      	})
		      	.done(function(data) 
		      	{
		        	console.log(data);
		      	});


			});".PHP_EOL;
		}

		//echo "addPolygonToMap('AlcalaTorrejon.geojson','{$fila["desbdt"]}','#aaaaff');".PHP_EOL;

	  
	  ?>
	  
	}

	function irACoordenadas(coordinates,zoom)
	{
	  window.map.panTo(coordinates);
	}

$('#map').width(1600);
$('#map').height(900);

cargarMapa([40.49166,-3.364136], 12);




 </script>
</body>
</html>

















