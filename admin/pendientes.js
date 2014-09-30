function hideOverlay()
{
  $(".darkOverlay").fadeOut("fast",function()
  {
    $('#overlay').removeClass('overlayPeque');
    $('#overlay').removeClass('overlayPeque2');
    $("#overlay").html('');
  });
}

//--------------------Cargamos los eventos pendientes--------------------
$.getJSON( "getEventosPendientes.php")
.done(function(data) 
{
  console.log(data);
  $.each(data, function(key,evento)
  {
  	console.log(evento);
	var clone=$("#eventoPendienteTemplate").clone();
	clone.hide();
	clone.attr('id',"eventoPorValidar-"+evento.idEvento);

	clone.find(".eventoPendiente-titulo").html(evento.titulo);
	clone.find(".eventoPendiente-asociacion").html(evento.asociacion);
	clone.find(".eventoPendiente-fecha").html(evento.fecha);
	if(evento.fechaFin)
		clone.find(".eventoPendiente-fechaFin").html(" - "+evento.fechaFin);
	else
		clone.find(".eventoPendiente-fechaFin").html("");
	clone.find(".eventoPendiente-distritoPadre").html(evento.nombreLugar);
	clone.find(".eventoPendiente-lugar").html(evento.lugar+",");

	clone.find(".eventoPendiente-texto").html(evento.texto);
	clone.find(".eventoPendiente-textoSwitch").on("click",function()
	{
		$(this).parent("div").find(".eventoPendiente-texto").toggle("fast");
	});

	clone.find(".eventoPendiente-tematicaText").html(evento.tematicas);
	clone.find(".eventoPendiente-etiquetasText").html(evento.etiquetas);
	if(evento.url!=null)
		if((evento.url.indexOf("http://") < 0) & (evento.url.indexOf("https://") < 0))
		  evento.url="http://"+evento.url;
	clone.find(".eventoPendiente-url-link").attr("href", evento.url);
	clone.find(".eventoPendiente-url-link").html(evento.url);
	clone.find(".eventoPendiente-email-link").attr("href", "mailto:"+evento.email);
	clone.find(".eventoPendiente-email-link").html(evento.email);

	clone.find(".eventoPendiente-direccionNombre").html(evento.nombreDireccion);
	clone.find(".eventoPendiente-direccionText").html(evento.direccion);
	if(evento.direccionActiva==="0")
	{
		clone.find(".eventoPendiente-direccionValidar").show().on("click",function()
		{
			console.log("Validar direccion");
		    $("#overlay").addClass("overlayPeque");
			$(".darkOverlay").fadeIn("fast");
  			$("#overlay").load("validarDireccion.html", function() 
      		{
				$.getJSON("getDireccion.php",{idDireccion:evento.idDireccion})
				.done(function(direccion)
				{
					//Cargamos los parámetros
					$(".validarDireccion-nombre").html(direccion.nombre);
					$(".validarDireccion-direccion").html(direccion.direccion+" esto es un texto largo que estoy probando");

					//Creamos el mapa
					var map = L.map('validarDireccion-map',
					    {
					        zoomControl: false,
					        attributionControl: false,
					    }).setView([direccion.lat,direccion.lng], 15);

					var marker = L.marker([direccion.lat,direccion.lng]).addTo(map);


					map.dragging.disable();
					map.touchZoom.disable();
					map.doubleClickZoom.disable();
					map.scrollWheelZoom.disable();
					map.boxZoom.disable();
					map.keyboard.disable();

					window.map=map;
					var ggl = new L.Google();
					L.Google('roadmap');
					map.addLayer(ggl);

					//Acciones botones por Ajax
					$(".validarDireccion-botonesAceptar").on("click",function()
					{
					    $.post( "validarDireccion.php", { idDireccion: direccion.idDireccion, status: 1 } )
					    .done(function(data)
					    {
							console.log("Dirección Validada");
							clone.find(".eventoPendiente-botonesRechazar").show();
							clone.find(".eventoPendiente-botonesAceptar").show();							
							clone.find(".eventoPendiente-direccionValidar").hide();
							hideOverlay();
							//Actualizar TODOS los que tengan esta dirección
					    });

					});
					$(".validarDireccion-botonesRechazar").on("click",function()
					{
						if(confirm('¿Estás seguro de que quieres rechazar esta dirección?\n\nNo se podrá aceptar el evento si la dirección no es válida'))
					    $.post( "validarDireccion.php", { idDireccion: direccion.idDireccion, status: -1 } )
					    .done(function(data)
					    {
						    console.log("Dirección Rechazada");
							clone.find(".eventoPendiente-botonesRechazar").show();
							clone.find(".eventoPendiente-direccionValidar").hide();
							clone.find(".eventoPendiente-direccionRechazada").show();
							hideOverlay();
						});
					});


				});  
			});
		});
	}
	else if(evento.direccionActiva==="-1")
	{
		clone.find(".eventoPendiente-botonesRechazar").show();		
		clone.find(".eventoPendiente-direccionRechazada").show();		
	}
	else
	{
		clone.find(".eventoPendiente-botonesRechazar").show();
		clone.find(".eventoPendiente-botonesAceptar").show();
	}

	//Acciones botones por Ajax
	clone.find(".eventoPendiente-botonesAceptar").on("click",function()
	{
	    $.post( "validarEvento.php", { idEvento: evento.idEvento, status: 1 } )
	    .done(function(data)
	    {
			$("#eventoPorValidar-"+evento.idEvento).slideUp("fast");
	    });

	});
	clone.find(".eventoPendiente-botonesRechazar").on("click",function()
	{
	    $.post( "validarEvento.php", { idEvento: evento.idEvento, status: -1 } )
	    .done(function(data)
	    {
			$("#eventoPorValidar-"+evento.idEvento).slideUp("fast");
		});
	});
	

	$(".cuerpo").append(clone);
	clone.show();

  });

});
