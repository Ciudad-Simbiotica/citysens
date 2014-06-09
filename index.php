<?php
include "preload.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>CitYsens</title>
 <link href="css/style.css" rel="stylesheet" type="text/css" />
 <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
 <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
 <link rel="stylesheet" type="text/css" href="js/tagsinput/jquery.tagsinput.css" />
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />
 <link rel="stylesheet" href="css/leafletCustom.css" />


 <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
 <script src="js/jquery.custom-animations.js"></script>
 <script type="text/javascript" src="js/tagsInput/jquery.tagsinput.js"></script>
 <script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
 <script src="http://maps.google.com/maps/api/js?v=3&sensor=false"></script>
 <script src="js/leaflet-plugins-master/layer/tile/Google.js"></script>

</head>

<body>  
 
 <div class='cabecera'>
 	<div class='cabecera-cuerpo'>
	 	<div class='cabecera-logo'><IMG SRC='icons/citysens.logoPrincipal.png' width='49px' height='40px'></div>
	 	<div class='cabecera-pestanias'>
	 		<div id='cabecera-pestania-izq' class='cabecera-pestania-izq cabecera-pestania-seleccionada'>Eventos</div>
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
	 		<div id='cabecera-pestania-ctr'  class='cabecera-pestania-ctr'>Procesos</div>
	 		<div class='subcabecera-pestania-ctr'>
	 			<img class='switch-filas' id='switch-puntuales' src='icons/icon_iniciativa.png' width="32px">
	 		</div>
	 	</div> 
	 	<div class='cabecera-busqueda'>
	 		<INPUT TYPE=TEXT class='input-busqueda' id='input-busqueda' placeholder="Filtrar eventos..."></INPUT> 
	 	</div>
	 	<div class='cabecera-suggest' id='cabecera-suggest'>
	 	</div>
	 	<div class='cabecera-lupa'>
	 	</div>
	 	<div class='cabecera-derecha'>Mi CitYsens</div>
	</div>
 </div>
 
 <div class='cuerpo'>
 	
 	<div class='botonesSuperiores'>
	 	
	 	<div class='nuevoEvento'>
	 		Propón un evento
	 	</div>

	 	<div class='correo'>
	 		Recibir por correo
	 	</div>
	 	
	</div>

<div class='scroll-curtain'></div>
<div class='scroll-curtain-gradient'></div>

 
 <div class='agenda'>

  <div class='agenda-primera-linea'>
  	&nbsp;
  </div>

  <div class='grupo-template' id='grupo-template'>
	 <div class='grupo-cabecera'>
	  <div class='grupo-cabecera-izq'>
	    Izq
	  </div>
	  <div class='grupo-cabecera-cntr'>
	   Centro
	  </div>
	  <div class='grupo-cabecera-dch'>
	   Dch
	  </div>
	 </div>
	 
	 <div class='grupo-filas'>
		 <div class='grupo-fila-eventos' id='grupo-fila-template-eventos'>
			<div class='grupo-elemento-tipo' id='grupo-fila-template-tipo'>
		 		<img class="imagen-tipo"  src='icons/Event-Unique.64.png' width="20px">
			</div>
		 	<div class='grupo-elemento-hora'>
		 		11:00
			</div>
		 	<div class='grupo-elemento-handup'>
		 		<IMG SRC='icons/flecha_arriba.png'>
			</div>
		 	<div class='grupo-elemento-temp'>
		 		<IMG class='imagen-temp' SRC='icons/termometro_3.png' height='32px'>
			</div>
		 	<div class='grupo-elemento-titulo'>
		 		Convocatoria
			</div>
		 	<div class='grupo-elemento-lugar'>
		 		Lugar
			</div>
		 	<div class='grupo-elemento-texto'>
		 		Descripción
			</div>
		 </div>

		<div class='grupo-fila-procesos' id='grupo-fila-template-procesos'>
			<div class='grupo-elemento-tipo' id='grupo-fila-template-tipo'>
		 		<img class="imagen-tipo" src='icons/Event-Unique.64.png' width="20px">
			</div>
		 	<div class='grupo-elemento-hora'>
		 		11:00
			</div>
		 	<div class='grupo-elemento-handup'>
		 		<IMG SRC='icons/flecha_arriba.png'>
			</div>
		 	<div class='grupo-elemento-temp'>
		 		<IMG class='imagen-temp' SRC='icons/termometro_1.png' height='32px'>
			</div>
		 	<div class='grupo-elemento-titulo'>
		 		Convocatoria
			</div>
		 	<div class='grupo-elemento-lugar'>
		 		Lugar
			</div>
		 	<div class='grupo-elemento-texto'>
		 		Descripción
			</div>
		 </div>

		<div class='grupo-fila-organizaciones' id='grupo-fila-template-organizaciones'>
		 	<div class='grupo-elemento-puntos'>
		 		9999
			</div>
		 	<div class='grupo-elemento-copa'></div>
			<div class='grupo-elemento-tipo' id='grupo-fila-template-tipo'>
		 		<img class="imagen-tipo"  src='icons/UniqueEvent.64.png' width="20px">
			</div>
		 	<div class='grupo-elemento-logo'>
		 		<IMG SRC='icons/icon_logo1.png' height='40px'>
			</div>
		 	<div class='grupo-elemento-tituloOrg'>
		 		Convocatoria
			</div>
		 	<div class='grupo-elemento-lugarOrg'>
		 		Lugar
			</div>
		 	<div class='grupo-elemento-textoOrg'>
		 		Descripción
			</div>
		 </div>

	 </div>
	 
	 <div class='grupo-pie'>
	  Texto en el pie
	 </div>
	 
  </div>
 
</div>
  <script src="js/agenda.js"></script>


<?php include "map.php";?>

  <div class='informacion'>
  	<div class='informacion-cabecera'>
	 	Ventana de información
	 </div>
 </div>

 
 </div>


 </body>
</html>