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

<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "c0f161c1-78ab-49ab-944a-e0aff8e7159b", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

</head>

<body>  
 <div class='darkOverlay'>
 	<div id='overlay'></div>
 </div>
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
	 		<!--
	 		<div id='cabecera-pestania-ctr'  class='cabecera-pestania-ctr'>Iniciativas</div>
	 		<div class='subcabecera-pestania-ctr'>
	 			<img class='switch-filas' id='switch-puntuales' src='icons/icon_iniciativa.png' width="32px">
	 		</div>
	 		<div id='cabecera-pestania-noticias'  class='cabecera-pestania-noticias'>Noticias</div>
	 		-->
	 	</div> 
	 	<div class='cabecera-busqueda'>
	 		<INPUT TYPE=TEXT class='input-busqueda' id='input-busqueda' placeholder="Filtrar eventos..."></INPUT> 
	 	</div>
	 	<div class='cabecera-suggest' id='cabecera-suggest'>
	 	</div>
	 	<div class='cabecera-lupa'>
	 	</div>
	 	<div id='cabecera-ordenar' class='cabecera-ordenar'>
	 	 <div class='cabecera-ordenar-texto'>Ordenar por</div><div class='cabecera-ordenar-flecha'>&#x25BC</div>
	 	</div>
 		<div class='subcabecera-pestania-ordenar'>
 			<div class='subcabecera-pestania-ordenar-row'>Fecha</div>
 			<div class='subcabecera-pestania-ordenar-row'>Temática</div>
 			<div class='subcabecera-pestania-ordenar-row'>Lugar</div>
 			<div class='subcabecera-pestania-ordenar-row'>Popularidad</div>
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
  <div class='agenda-filtros'>
  	<div class='agenda-filtros-top'>
	  <div class='agenda-filtros-busqueda'>
	  	<div class='tagFiltro' id='tagFiltroTemplate' style="display:none">
	  	 <div class='tagFiltro-imagen'>
	     </div>
	  	 <div class='tagFiltro-texto'>
	  	 	Filtro Ejemplo
	     </div>
	  	 <div class='tagFiltro-x'>
	  	  x
	     </div>
	  	</div>
	  </div>
	  <div class='agenda-filtros-tematica'>
	  </div>
	</div>
	<div class='agenda-filtros-bottom'>
	  <div class='agenda-filtros-lugar'>
	  </div>
	  <div class='agenda-filtros-entidad'>
	  </div>
	</div>
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
			<div class='grupo-elemento-tipo'>
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
			<div class='grupo-elemento-tipo'>
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
			<div class='grupo-elemento-tipo'>
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
  <script src="js/mapa.js"></script>
  <script src="js/sugerencias.js"></script>
  <script src="js/agenda.js"></script>


<?php include "map.php";?>

  <div class='informacion'>
  	<div class='informacion-cabecera'>
	 	<div class='informacion-cabecera-izq'>
	 	 <div class='informacion-cabecera-izq-calendario'>
 	 	  <div class='informacion-cabecera-izq-calendario-top'>
 	 	   MAR
	 	  </div>
 	 	  <div class='informacion-cabecera-izq-calendario-bottom'>
 	 	   20
	 	  </div>
	 	 </div>
	 	 <div class='informacion-cabecera-izq-horas'>
 	 	  <div class='informacion-cabecera-izq-horas-top'>
 	 	   11:00
	 	  </div>
 	 	  <div class='informacion-cabecera-izq-horas-bottom'>
 	 	   16:00
	 	  </div>
	 	 </div>
	 	</div>
	 	<div class='informacion-cabecera-dch'>
	 	 <div class='informacion-cabecera-dch-titulo'>
 	 	  <div class='informacion-cabecera-dch-titulo-top'>
 	 	   Nombre evento no muy largo
	 	  </div>
 	 	  <div class='informacion-cabecera-dch-titulo-bottom'>
 	 	   Dirección del evento - Alcalá
	 	  </div>
	 	 </div>
	 	</div>
	 </div>
	 <div class='informacion-cuerpo'>
	 	<div class='informacion-cuerpo-tematicas'>
			<img src='/citysens/icons/etiqueta30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Tematicas: </B> 
	  		<span class='informacion-cuerpo-tematicas-listado'>Temática 1, Temática 2, Temática 3</span>
	 	</div>
	 	<div class='informacion-cuerpo-etiquetas'>
	  		<img src='/citysens/icons/etiqueta30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Etiquetas: </B> 
	  		<span class='informacion-cuerpo-etiquetas-listado'>Etiqueta 1, Etiqueta 2, Etiqueta 3</span>
	 	</div>
	 	<div class='informacion-cuerpo-contacto'>
	  		<span class='informacion-cuerpo-contacto-elemento'>Web:</span> <a target="_blank" href='http://www.templateurl.es' class='informacion-cuerpo-contacto-url'>http://www.templateurl.es</a><br>
	  		<span class='informacion-cuerpo-contacto-elemento'>e-Mail:</span> <a target="_blank" href='mailto:correo@templateurl.es' class='informacion-cuerpo-contacto-email'>correo@templateurl.es</a>
	 	</div>
	 	<div class='informacion-cuerpo-texto'>
	  		<h3>Aquí iría el texto</h3>
			<p>Eso</p>
	 	</div>
	  
	 </div>
	 <div class='informacion-pie'>
	  	<span class='st_email' displayText=''></span>
		<span class='st_facebook' displayText=''></span>
		<span class='st_linkedin' displayText=''></span>
		<span class='st_googleplus' displayText=''></span>
		<span class='st_twitter' displayText=''></span>
	 </div>
 </div>

 
 </div>


 </body>
</html>