function createGroup(grupo,left,center,right)
{
	var clone=$("#grupo-template").clone();
  clone.hide();
  clone.removeClass("grupo-template");
  clone.addClass("grupo");
	clone.attr('id',grupo.replace(" ",""));
	clone.find('.grupo-filas').empty();

	clone.find('.grupo-cabecera-izq').html(left);
	clone.find('.grupo-cabecera-cntr').html(center);
	clone.find('.grupo-cabecera-dch').html(right);

	clone.appendTo(".agenda");

	//clone.show();
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


	clone.appendTo("#"+grupo.replace(" ","")+">.grupo-filas");

	if(animated>0)
		clone.slideDown("fast", function() {});
	else
		clone.show();
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

$("#switch-participantes").click(function()
{
  $("#switch-participantes").toggleClass("switch-filas-off");
  //$(".grupo-fila-colectivo").slideToggle("fast", function() {});
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

  cargarDatos("organizaciones");

});

function cargarDatos(clase)
{

  $(".grupo").attr('id',"");  //Para que no se inserten en esta les quitamos el ID
  $(".grupo").fadeOut("slow",function()
  {
    $(this).remove();
  });  

  var getAgenda = "getAgendaXML.php?";
  $.getJSON(getAgenda, 
  {
    clase: clase,
    date: "any",
    format: "json"
  })
    .done(function(data) 
    {
      //Esperamos a que se hayan borrado los grupos (por si acaba antes) antes de clonar
      $.each(data.grupos, function(grupo,filas)
      {
        createGroup(grupo,filas.cabeceraIzq,filas.cabeceraCntr,filas.cabeceraDch);
        $.each(filas.filas,function(i,item)
        {
          createLine(grupo,item,0);
        });
      });
    $(".grupo").fadeIn(1000);
    });  
}

cargarDatos("eventos");
