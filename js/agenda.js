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
	var clone=$("#grupo-fila-template-"+datos.tipo).clone();
	clone.hide();

   	$.each(datos,function(clase,contenido)
   	{
   		if(clase!="tipo")
			clone.find('.grupo-elemento-'+clase).html(contenido);
	});

	clone.appendTo("#"+grupo+">.grupo-filas");
	if(animated>0)
		clone.slideDown("fast", function() {});
	else
		clone.show();
};

function mostrarEntidades()
{
  console.log("Mostrando Entidades");
  $("#boton-accion-entidad").unbind('click',mostrarEntidades);

  $("#imagen-accion-entidad").fadeTo(150,0,function()
    {
      $("#imagen-accion-entidad").attr("src", "icons/icon_entidad-accion.png");
      $("#imagen-accion-entidad").fadeTo(150,1,function()
      {
        //Aquí ya no hay que hacer nada
      });
    });

  $(".filtros-accion").animate({
    marginLeft:-152,
  }, 150, function() 
   {
    $(".filtros-entidad").animate({
      marginLeft:0
    }, 150, function() 
     {
        $("#boton-accion-entidad").click(mostrarAcciones);
     });
   });
};

function mostrarAcciones()
{
  console.log("Mostrando Acciones");
  $("#boton-accion-entidad").unbind('click',mostrarAcciones);

  $("#imagen-accion-entidad").fadeTo(150,0,function()
    {
      $("#imagen-accion-entidad").attr("src", "icons/icon_accion-entidad.png");
      $("#imagen-accion-entidad").fadeTo(150,1,function()
      {

      });
    });


  $(".filtros-entidad").animate({
    marginLeft:-304
  }, 150, function() 
   {
    $(".filtros-accion").animate({
      marginLeft:0
    }, 150, function() 
     {
        $("#boton-accion-entidad").click(mostrarEntidades);
     });
   });
};

function ocultarGruposSinElementos()
{
  $(".grupo").each(function(index) 
  {
    var atLeastOneVisible=false;
    $(this).find("div[class^=grupo-fila-]").each(function(index2) 
    {
      if($(this).css('display')=="block")
        atLeastOneVisible=true;
    });
    if(!atLeastOneVisible)
    {      
      console.log($(this).attr("id"));
    }
  });
};

$("#boton-accion-entidad").click(mostrarEntidades);
$('<img/>')[0].src="icons/icon_entidad-accion.png"; //Precargamos la otra imagen

$("#switch-puntuales").click(function()
{
  $("#switch-puntuales").toggleClass("switch-filas-off");
  $(".grupo-fila-convocatoria").slideToggle("fast", function() {});
  ocultarGruposSinElementos();
});

$("#switch-recurrentes").click(function()
{
  $("#switch-recurrentes").toggleClass("switch-filas-off");
  $(".grupo-fila-recurrente").slideToggle("fast", function() {});
});

$("#switch-iniciativas").click(function()
{
  $("#switch-iniciativas").toggleClass("switch-filas-off");
  $(".grupo-fila-iniciativa").slideToggle("fast", function() {});
});


var getAgenda = "http://localhost:8888/citysens/getAgendaXML.php?";
$.getJSON(getAgenda, 
{
  location: "Alcalá",
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
      $(".grupo-fila-iniciativa").slideUp(0, function() {});

  });