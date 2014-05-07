function createGroup(grupo,left,center,right)
{
	var clone=$("#grupo-template").clone();
	clone.attr('id',grupo);
	clone.find('.grupo-filas').empty();

	clone.find('.grupo-cabecera-izq').html(left);
	clone.find('.grupo-cabecera-cntr').html(center);
	clone.find('.grupo-cabecera-dch').html(right);

	clone.appendTo(".agenda");

	clone.show();
}

function createLine(grupo,datos,animated)
{
	var clone=$("#grupo-fila-template-"+datos.clase).clone();
	clone.hide();
    
  $.each(datos,function(clase,contenido)
  {
   		if(clase!="tipo")
			clone.find('.grupo-elemento-'+clase).html(contenido);
	});

  clone.removeClass("grupo-fila-template");
  clone.addClass("grupo-fila-"+datos.tipo);


	clone.appendTo("#"+grupo+">.grupo-filas");
	if(animated>0)
		clone.slideDown("fast", function() {});
	else
		clone.show();
};


function ocultarGruposSinElementos()
{
  $(".grupo").each(function(index) 
  {
    var atLeastOneVisible=false;
    $(this).find("div[class^=grupo-fila-]").each(function(index2) 
    {
      if($(this).css('display')=="block")
      {
        atLeastOneVisible=true;
      }
      else
      {
      }
    });
    if($(this).attr("id")!="grupo-template")
    {
      if(!atLeastOneVisible)
      {
        $(this).slideUp("fast");      
      }
      else
      {
        $(this).slideDown("fast");      
      }
    }
  });
};

$("#switch-puntuales").click(function()
{
  console.log("Switch Puntuales");
  $("#switch-puntuales").toggleClass("switch-filas-off");
  $(".grupo-fila-convocatoria").slideToggle("fast", function() {});
});

$("#switch-recurrentes").click(function()
{
  $("#switch-recurrentes").toggleClass("switch-filas-off");
  $(".grupo-fila-recurrente").slideToggle("fast", function() {});
});

$("#switch-instituciones").click(function()
{
  $("#switch-instituciones").toggleClass("switch-filas-off");
  $(".grupo-fila-institucion").slideToggle("fast", function() {});
});

$("#switch-organizaciones").click(function()
{
  $("#switch-organizaciones").toggleClass("switch-filas-off");
  $(".grupo-fila-organizacion").slideToggle("fast", function() {});
});

$("#switch-colectivos").click(function()
{
  $("#switch-colectivos").toggleClass("switch-filas-off");
  $(".grupo-fila-colectivo").slideToggle("fast", function() {});
});



$(".cabecera-pestania-izq").click(function()
{
  console.log("Mostrando Eventos");

  $("#cabecera-pestania-izq").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);

  $(".subcabecera-pestania-dch").slideUp("fast",function() 
  {
    // Animation complete.
    $(".subcabecera-pestania-izq").slideDown("fast");
  });

  borrarIniciativas();
  borrarOrganizaciones();
  cargarDatos("eventos");

});

$(".cabecera-pestania-ctr").click(function()
{
  console.log("Mostrando Organizaciones");
  $("#cabecera-pestania-ctr").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);

  $(".subcabecera-pestania-izq").slideUp("fast");
  $(".subcabecera-pestania-dch").slideUp("fast");

  borrarEventos();
  borrarOrganizaciones();
  cargarDatos("procesos");



});

$(".cabecera-pestania-dch").click(function()
{
  console.log("Mostrando Organizaciones");
  $("#cabecera-pestania-dch").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);

  $(".subcabecera-pestania-izq").slideUp("fast",function() 
  {
    // Animation complete.
    $(".subcabecera-pestania-dch").slideDown("fast");
  });

  borrarEventos();
  borrarIniciativas();
  cargarDatos("organizaciones");

});


function borrarEventos()
{
  $(".grupo-fila-convocatoria").slideUp("fast",function() 
  {
    $(".grupo-fila-convocatoria").remove();
  });
  $(".grupo-fila-recurrente").slideUp("fast",function() 
  {
    $(".grupo-fila-recurrente").remove();
  });
}

function borrarIniciativas()
{
  $(".grupo-fila-iniciativa").slideUp("fast",function() 
  {
    $(".grupo-fila-iniciativa").remove();
  });
}

function borrarOrganizaciones()
{
  $(".grupo-fila-institucion").slideUp("fast",function() 
  {
    $(".grupo-fila-institucion").remove();
  });
  $(".grupo-fila-organizacion").slideUp("fast",function() 
  {
    $(".grupo-fila-organizacion").remove();
  });
  $(".grupo-fila-colectivo").slideUp("fast",function() 
  {
    $(".grupo-fila-colectivo").remove();
  });
}



function cargarDatos(clase)
{
  var getAgenda = "getAgendaXML.php?";
  $.getJSON(getAgenda, 
  {
    clase: clase,
    date: "any",
    format: "json"
  })
    .done(function(data) 
    {
      $.each(data.grupos, function(grupo,filas)
      {
        createGroup(grupo,filas.cabeceraIzq,filas.cabeceraCntr,filas.cabeceraDch);
        $.each(filas.filas,function(i,item)
        {
          createLine(grupo,item,0);
        });
      });
    });  
}

cargarDatos("eventos");
