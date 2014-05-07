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

$("#switch-iniciativas").click(function()
{
  $("#switch-iniciativas").toggleClass("switch-filas-off");
  $(".grupo-fila-iniciativa").slideToggle("fast", function() {});
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
});

$(".cabecera-pestania-ctr").click(function()
{
  console.log("Mostrando Organizaciones");
  $("#cabecera-pestania-ctr").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);

  $(".subcabecera-pestania-izq").slideUp("fast");
  $(".subcabecera-pestania-dch").slideUp("fast");
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
});




var getAgenda = "getAgendaXML.php?";
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