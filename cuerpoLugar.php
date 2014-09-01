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
	  <div class='agenda-segunda-linea'>
	   <div class='div-avisos'>
	  	<input type=text id="email-avisos" placeholder="escribe tu mail">
	  	<input type=button id="boton-avisos" value="Recibir avisos">
	   </div>
	   <div class='div-ordenar'>
	  	Ordenar por: 
	  	<select id="select_ordenar">
	  	 <option value="fecha">Fecha</option>
	  	 <option value="tematica">Temática</option>
	  	 <option value="lugar">Lugar</option>
	  	 <option value="popularidad">Popularidad</option>
		</select>
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
	  	<div class='informacion-cabecera' title='Haz click para ver el evento completo'>
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
				<div class='informacion-cuerpo-tematicas-elemento'><img src='/citysens/icons/etiqueta30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Tematicas: </B></div> 
		  		<div class='informacion-cuerpo-tematicas-listado'>Temática 1, Temática 2, Temática 3</div>
		 	</div>
		 	<div class='informacion-cuerpo-etiquetas'>
		  		<div class='informacion-cuerpo-etiquetas-elemento'><img src='/citysens/icons/etiquetas30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Etiquetas: </B></div>
		  		<div class='informacion-cuerpo-etiquetas-listado'>Etiqueta 1, Etiqueta 2, Etiqueta 3</div>
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
			
			<a class='share-mail' href="mailto:?subject=Asunto&amp;body=Cuerpo"
			   onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
			   target="_blank" title="Share on Google+"><img width="16px" src='/citysens/icons/mail.png'></a>

			<a class='share-facebook' href="https://www.facebook.com/sharer/sharer.php?u=http://www.google.com&t=Titulo&s=Texto"
			   onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;"
			   target="_blank" title="Share on Facebook"><img width="16px" src='/citysens/icons/facebook.ico'></a>

			<a class='share-twitter' href="https://twitter.com/share?url=http://www.google.com&text=Titulo"
			   onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;"
			   target="_blank" title="Share on Twitter"><img width="16px" src='/citysens/icons/twitter.ico'></a>

			<a class='share-googleplus' href="https://plus.google.com/share?url=http://www.google.com"
			   onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
			   target="_blank" title="Share on Google+"><img width="16px" src='/citysens/icons/googleplus.ico'></a>

			<a class='share-linkedin' href="http://www.linkedin.com/shareArticle?mini=true&url=http://www.google.com&title=Titulo&summary=Texto&source=http://www.citysens.net"
			   onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
			   target="_blank" title="Share on Google+"><img width="16px" src='/citysens/icons/linkedin.ico'></a>

			<!--<a class='share-link' href="#"
			   onclick=''
			   target="_blank" title="Share on Google+"><img width="16px" src='/citysens/icons/link.png'></a>-->

		 </div>
	 </div>

 