<?php
error_reporting(E_ERROR);
include "settings.php";
//Página por defecto
if(($_GET["idTerritorio"]=="")&($_GET["idEvento"]=="")&($_GET["idEntidad"]==""))
{
    header('Location: '.BASE_URL.'?idTerritorio=701280002');
    exit();
}

include "loadSession.php";
include "preload.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <base href="<?php echo BASE_URL; //Base URL?>">
 
 <link rel="shortcut icon" href="css\icons\CitYsens.monigote.color.fw.png" />
     
 <link href="css/style.css" rel="stylesheet" type="text/css" />
 <link href="css/cabecera.css" rel="stylesheet" type="text/css" />
<!-- <link href="css/grupos.css" rel="stylesheet" type="text/css" />-->
 <link href="css/gruposresponsive.css" rel="stylesheet" type="text/css" />
 <link href="css/newEvent.css" rel="stylesheet" type="text/css" />
 <link href="css/informacion.css" rel="stylesheet" type="text/css" />
 <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
 <link rel="stylesheet" href="css/leafletCustom.css" />
 <link rel="stylesheet" type="text/css" href="css/jNotify.jquery.css" media="screen" />
 <link rel="stylesheet" type="text/css" href="js/datetimepicker/jquery.datetimepicker.css"/ >
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">

 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js" type="text/javascript"></script>
 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js" type="text/javascript"></script>
 <script src="js/datepicker-es.js" type="text/javascript"></script>
 <script src="js/jquery.custom-animations.js" type="text/javascript"></script>
 <script src="js/spin.min.js" type="text/javascript"></script>
 <script src="js/live.js" type="text/javascript"></script> <!-- solution for CSS change in live Only for Production-->
 <script src="js/jquery.spin.js" type="text/javascript"></script>
 <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js" type="text/javascript"></script>
 <script src="http://maps.google.com/maps/api/js?v=3&sensor=false" type="text/javascript"></script>
 <script src="js/leaflet-plugins-master/layer/Leaflet.ContinuousZoom.js" type="text/javascript"></script>
 <script src="js/leaflet-plugins-master/layer/tile/Google.js" type="text/javascript"></script>
 <script src="js/wysihtml5/parser_rules/advanced.js" type="text/javascript"></script>
 <script src="js/wysihtml5/dist/wysihtml5-0.3.0.min.js" type="text/javascript"></script>
 <script src="js/jNotify.jquery.js" type="text/javascript"></script>
 <script src="js/datetimepicker/jquery.datetimepicker.js" type="text/javascript"></script>
 <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5405f16570d4251b" type="text/javascript"></script>
       <!-- Latest compiled and minified JavaScript 
 <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>-->
   
</head>

<body>  

<input type="hidden" id="notificacion" value="<?php echo $_SESSION['notificacion']; unset($_SESSION['notificacion']); //Exponemos la variable notificación?>"></input>
<input type="hidden" id="logged" value="<?php echo $_SESSION['logged']; //Exponemos si estamos loggeados para JS?>"></input>

 <div class='darkOverlay'>
 	<div id='overlay' class='overlay'></div>
 </div>
 <div class='cabecera'>
 	<div class='cabecera-cuerpo'>
	 	<div class='cabecera-logo'><A HREF=''><IMG SRC='css/icons/citysens.logoPrincipal.png' width='49px' height='40px'></A></div>
	 	<div class='cabecera-pestanias'>
	 		<div id='cabecera-pestania-izq' class='cabecera-pestania-izq'>Eventos</div>
	 		<div class='subcabecera-pestania-izq'>
	 			<div class='switch-filas switch-filas-convocatoria' id='switch-puntuales'>
	 				<img src='css/icons/Event-Unique.64.png' width="32px">
	 			</div>
	 			<div class='switch-filas switch-filas-recurrente' id='switch-recurrentes'>
		 			<img src='css/icons/Event-Recurring.64.png' width="32px">
		 		</div>
	 		</div>
	 		<div id='cabecera-pestania-dch'  class='cabecera-pestania-dch'>Entidades</div>
	 		<div class='subcabecera-pestania-dch'>
	 			<div class='switch-filas switch-filas-instituciones' id='switch-instituciones'>
	 				<img src='css/icons/icon_CitYsens.institucion.png' width="32px">	
	 			</div>
				<div class='switch-filas switch-filas-organizaciones' id='switch-organizaciones'>
		 			<img src='css/icons/icon_CitYsens.organizacion.png' width="32px">	 		
		 		</div>
		 		<div class='switch-filas switch-filas-colectivos' id='switch-colectivos'>
		 			<img src='css/icons/CitYsens.people.png' width="32px">	 		
				</div>
	 		</div>
	 		<!--
	 		<div id='cabecera-pestania-ctr'  class='cabecera-pestania-ctr'>Iniciativas</div>
	 		<div class='subcabecera-pestania-ctr'>
	 			<img class='switch-filas' id='switch-puntuales' src='css/icons/icon_iniciativa.png' width="32px">
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
	 	<div class='cabecera-derecha'>
					<div class='cabecera-derecha-micitysens logged-item'>Mi CitYsens</div>
				 	<div class='cabecera-derecha-logout logged-item'>(Salir)</div>

				 	<div class='cabecera-derecha-inicia public-item'>Inicia Sesión</div>
				 	<div class='cabecera-derecha-registrate public-item'>(Regístrate)</div>
	 	</div>
	</div>
 </div>



 
 <div class='cuerpo'>
	<?php
		if($_GET["idTerritorio"]!="")
			include "cuerpoLugar.php";
		else if($_GET["idEvento"]!="")
			include "cuerpoEvento.php";
		else if($_GET["idEntidad"]!="")
			include "cuerpoEntidad.php";
	?>
 </div>

<FORM METHOD=POST id="logout_form">
	<INPUT TYPE=hidden id='post_form' name='post_form' value='logout_form'>
</FORM>

 </body>
</html>