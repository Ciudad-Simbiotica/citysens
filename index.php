<?php
include "preload.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <link href="css/style.css" rel="stylesheet" type="text/css" />
 <link href="css/cabecera.css" rel="stylesheet" type="text/css" />
 <link href="css/grupos.css" rel="stylesheet" type="text/css" />
 <link href="css/newEvent.css" rel="stylesheet" type="text/css" />
 <link href="css/informacion.css" rel="stylesheet" type="text/css" />
 <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
 <link rel="stylesheet" href="css/leafletCustom.css" />


 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
 <script src="js/datepicker-es.js"></script>
 <script src="js/jquery.custom-animations.js"></script>
 <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
 <script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
 <script src="js/leaflet-plugins-master/layer/Leaflet.ContinuousZoom.js"></script>
 <script src="js/leaflet-plugins-master/layer/tile/Google.js"></script>
 <script src="js/wysihtml5/parser_rules/advanced.js"></script>
 <script src="js/wysihtml5/dist/wysihtml5-0.3.0.min.js"></script>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5405f16570d4251b"></script>

</head>

<body>  
 <div class='darkOverlay'>
 	<div id='overlay' class='overlay'></div>
 </div>
 <div class='cabecera'>
 	<div class='cabecera-cuerpo'>
	 	<div class='cabecera-logo'><IMG SRC='icons/citysens.logoPrincipal.png' width='49px' height='40px'></div>
	 	<div class='cabecera-pestanias'>
	 		<div id='cabecera-pestania-izq' class='cabecera-pestania-izq'>Eventos</div>
	 		<div class='subcabecera-pestania-izq'>
	 			<div class='switch-filas switch-filas-convocatoria' id='switch-puntuales'>
	 				<img src='icons/Event-Unique.64.png' width="32px">
	 			</div>
	 			<div class='switch-filas switch-filas-recurrente' id='switch-recurrentes'>
		 			<img src='icons/Event-Recurring.64.png' width="32px">
		 		</div>
	 		</div>
	 		<div id='cabecera-pestania-dch'  class='cabecera-pestania-dch'>Entidades</div>
	 		<div class='subcabecera-pestania-dch'>
	 			<div class='switch-filas switch-filas-instituciones' id='switch-instituciones'>
	 				<img src='icons/icon_CitYsens.institucion.png' width="32px">	
	 			</div>
				<div class='switch-filas switch-filas-organizaciones' id='switch-organizaciones'>
		 			<img src='icons/icon_CitYsens.organizacion.png' width="32px">	 		
		 		</div>
		 		<div class='switch-filas switch-filas-colectivos' id='switch-colectivos'>
		 			<img src='icons/CitYsens.people.png' width="32px">	 		
				</div>
	 		</div>
	 		<!--
	 		<div id='cabecera-pestania-ctr'  class='cabecera-pestania-ctr'>Iniciativas</div>
	 		<div class='subcabecera-pestania-ctr'>
	 			<img class='switch-filas' id='switch-puntuales' src='icons/icon_iniciativa.png' width="32px">
	 		</div>
	 		<div id='cabecera-pestania-noticias'  class='cabecera-pestania-noticias'>Noticias</div>
	 		-->
	 	</div> 
	 	<div class='cabecera-busqueda'>
	 		<INPUT TYPE=TEXT class='input-busqueda' id='input-busqueda'></INPUT> 
	 	</div>
	 	<div class='cabecera-suggest' id='cabecera-suggest'>
	 	</div>
	 	<div class='cabecera-lupa'>
	 	</div>
	 	<div class='cabecera-propon'>Propón un evento</div>
	 	<div class='cabecera-derecha'>Mi CitYsens</div>
	</div>
 </div>
 
 <div class='cuerpo'>
	<?php
		if($_GET["idLugar"]!="")
			include "cuerpoLugar.php";
		else if($_GET["idEvento"]!="")
			include "cuerpoEvento.php";
		else if($_GET["idAsociacion"]!="")
			include "cuerpoEntidad.php";
	?>
 </div>


 </body>
</html>