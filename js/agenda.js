/* 
---------------------------------------------------------------------------------------------
------------------------------------Creado de grupos/filas-----------------------------------
---------------------------------------------------------------------------------------------
*/
function createGroup(grupo,left,center,right,totalFilas)
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


  clone.find('.grupo-pie').html("");

  if(!(typeof totalFilas==='undefined'))
  {
    $.each(totalFilas,function(tipo,cantidad)
    {
      var cadenaTipo=tipo;
      if(cantidad>1)
      {
        if((tipo=="organizacion")||(tipo=="institucion"))
          cadenaTipo+="es";
        else
          cadenaTipo+="s";
      }

      clone.find('.grupo-pie').append("<div class='grupo-pie-"+tipo+"'><div class='grupo-pie-"+tipo+"-imagen'></div><div class='grupo-pie-"+tipo+"-texto'>+"+cantidad+" "+cadenaTipo+"</div></div>");
      clone.find(".grupo-pie-"+tipo).hide();
      clone.find(".grupo-pie-"+tipo).click(function()
      {
        $("#"+grupo.replace(" ","")).find(".grupo-fila-"+tipo).slideDown("fast");
        $(this).slideUp("fast");
      });
    });
  }
  
	clone.appendTo(".agenda");

	//clone.show();
}

function createLine(grupo,datos,animated)
{
	var clone=$("#grupo-fila-template-"+datos.clase).clone();
	clone.hide();
    
  $.each(datos,function(clase,contenido)
  {
   		if(clase=="id")
      {
        clone.attr("id",contenido); //Ponemos ID a la fila
        clone.find(".grupo-elemento-handup").click(function() //Añadimos la función de click al botón
        {
          clickHandUp(contenido);
        });
        clone.click(function() //Añadimos la función de click a la fila
        {
          clickFila(contenido);
        });
        clone.mouseenter(function() //Añadimos la función de click a la fila
        {
          enterFila(contenido);
        })
        .mouseleave(function() //Añadimos la función de click a la fila
        {
          leaveFila(contenido);
        });
      }
      else if(clase=="tipo")
      {
        //Cambiamos las imágenes según el tipo
        //ToDo: Sustituir el IMG por DIV y cambiar el background por CSS
        if(contenido=="convocatoria")
          clone.find(".imagen-tipo").attr("src", "icons/Event-Unique.64.png");
        else if(contenido=="recurrente")
          clone.find(".imagen-tipo").attr("src", "icons/Event-Recurring.64.png");
        else if(contenido=="institucion")
          clone.find(".imagen-tipo").attr("src", "icons/icon_CitYsens.institucion.png");
        else if(contenido=="organizacion")
          clone.find(".imagen-tipo").attr("src", "icons/icon_CitYsens.organizacion.png");
        else if(contenido=="colectivo")
          clone.find(".imagen-tipo").attr("src", "icons/CitYsens.People.png");
      }
      /*
      else if(clase=="participante")
      {
        //Si no es participante escondemos el icono
        if(contenido==0)
          clone.find(".grupo-elemento-participante").hide();
      }
      */
      else if(clase=="temperatura")
      {
        //Cambiamos el termometro
        clone.find(".imagen-temp").attr("src", "icons/termometro_"+contenido+".png");
      }
      else
      {
        //El resto se sustituye el contenido por lo que se pasa
  			clone.find('.grupo-elemento-'+clase).html(contenido);
      }
	});

  clone.removeClass("grupo-fila-template");
  clone.addClass("grupo-fila-"+datos.tipo);


	clone.appendTo("#"+grupo.replace(" ","")+">.grupo-filas");

	if(animated>0)
		clone.slideDown("fast", function() {});
	else
		clone.show();
};

/* 
---------------------------------------------------------------------------------------------
------------------------------------Clicks/Hovers en filas-----------------------------------
---------------------------------------------------------------------------------------------
*/

function clickHandUp(id)
{
  alert("Click 'Me gusta': "+id);
  event.stopPropagation();
}

function clickFila(id)
{
  //alert("Click Fila: "+id);
  $("[class^=grupo-fila-]").removeClass("grupo-fila-selected");
  $("#"+id).addClass("grupo-fila-selected");
  $(".informacion-cabecera").html("Cargando contenido: "+id);
  cargarContenido(id);
  $.each(markers, function( index, value ) 
  {
    if(markers[index]!=null)
      markers[index].setOpacity(0);
  });
  if(markers[id]!=null)
    markers[id].setOpacity(0.9);
}

function enterFila(id)
{
  if(markers[id]!=null)
    markers[id].setOpacity(0.6);  
}

function leaveFila(id)
{
  if(!($("#"+id).hasClass("grupo-fila-selected")))
    if(markers[id]!=null)
      markers[id].setOpacity(0);  
}

function switchFilas(clase,tipo)
{
  console.log("Switch "+clase);
  $("#switch-"+clase).toggleClass("switch-filas-off");
  if($("#switch-"+clase).hasClass("switch-filas-off"))
  {
    $(".grupo-fila-"+tipo).slideUp("fast", function() {});
    $(".grupo-pie-"+tipo).slideDown("fast", function() {});
  }
  else
  {
    $(".grupo-fila-"+tipo).slideDown("fast", function() {});
    $(".grupo-pie-"+tipo).slideUp("fast", function() {});    
  }  
}


/* 
---------------------------------------------------------------------------------------------
-----------------------------------Cargado contenido agenda----------------------------------
---------------------------------------------------------------------------------------------
*/


function cargarContenido(id)
{
  $.ajax({

     type: "GET",
     url: 'getDatos.php',
     data: "id=" + id, // appears as $_GET['id'] @ ur backend side
     success: function(data) {
           // data is ur summary
          $(".informacion-cabecera").html(data);
          $(".informacion").slideDown("fast");
     }

   });

}


function cargarDatos(clase)
{

  var query=JSON.stringify(arrayTags);
  console.log(arrayTags);
  console.log(query);

  /*
  $.each(arrayTags, function(i, object) 
  {
    $.each(object, function(property, value) 
    {
        console.log(property + "=" + value);
    });
  });
  */
  $(".grupo").attr('id',"");  //Para que no se inserten en esta les quitamos el ID
  $(".grupo").fadeOut("1000",function()
  {
    $(this).remove();
  });  

  var getAgenda = "getAgendaXML.php?";
  $.getJSON(getAgenda, 
  {
    clase: clase,
    date: "any",
    query: query,
    format: "json"
  })
    .done(function(data) 
    {
      //Esperamos a que se hayan borrado los grupos (por si acaba antes) antes de clonar
      if(jQuery.isEmptyObject(data.grupos))
      {
        $(".agenda-primera-linea").html("Ningun evento satisface los filtros de búsqueda:");      
      }
      else
      {
        $(".agenda-primera-linea").html("Mostrando EVENTOS en las proximas semanas, que satisfacen los filtros de búsqueda:");
      }
      $.each(data.grupos, function(grupo,filas)
      {
        createGroup(grupo,filas.cabeceraIzq,filas.cabeceraCntr,filas.cabeceraDch,filas.totalFilas);
        $.each(filas.filas,function(i,item)
        {
          createLine(grupo,item,0);

        });
      });
    $(".grupo").fadeIn(1000);
    });  
}

/* 
---------------------------------------------------------------------------------------------
-----------------------------------Nuevo evento, subscribir----------------------------------
---------------------------------------------------------------------------------------------
*/


function newEvent()
{
  alert("Creando nuevo evento");
}

function subscribe()
{
  alert("Subscribiéndose");
}

$(".nuevoEvento").click(function()
{
  newEvent();
});

$(".correo").click(function()
{
  subscribe();
});


/* 
---------------------------------------------------------------------------------------------
---------------------------------BINDS switch filas/tipos------------------------------------
---------------------------------------------------------------------------------------------
*/


$("#switch-puntuales").click(function()
{
  switchFilas("puntuales","convocatoria");
});

$("#switch-recurrentes").click(function()
{
  switchFilas("recurrentes","recurrente");
});

$("#switch-instituciones").click(function()
{
  switchFilas("instituciones","institucion");
});

$("#switch-organizaciones").click(function()
{
  switchFilas("organizaciones","organizacion");
});

$("#switch-colectivos").click(function()
{
  switchFilas("colectivos","colectivo");
});


$(".cabecera-pestania-izq").click(function()
{
  console.log("Mostrando Eventos");

  $("#cabecera-pestania-izq").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-puntuales").removeClass("switch-filas-off");
  $("#switch-recurrentes").removeClass("switch-filas-off");
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
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);

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
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-instituciones").removeClass("switch-filas-off");
  $("#switch-organizaciones").removeClass("switch-filas-off");
  $("#switch-colectivos").removeClass("switch-filas-off");

  $(".subcabecera-pestania-izq").slideUp("fast",function() 
  {
    // Animation complete.
    $(".subcabecera-pestania-dch").slideDown("fast");
  });

  cargarDatos("organizaciones");

});


$(".cabecera-pestania-noticias").click(function()
{
  console.log("Mostrando Noticias");

  $("#cabecera-pestania-noticias").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-puntuales").removeClass("switch-filas-off");
  $("#switch-recurrentes").removeClass("switch-filas-off");
  $(".subcabecera-pestania-dch").slideUp("fast");
  $(".subcabecera-pestania-izq").slideUp("fast");
  cargarDatos("eventos");

});

$(".cabecera-ordenar").click(function()
{

  $("#cabecera-ordenar").toggleClass("cabecera-pestania-seleccionada",150);


  if($("#cabecera-ordenar").hasClass("cabecera-pestania-seleccionada"))
    $(".cabecera-ordenar-flecha").html("&#x25BC");
  else
    $(".cabecera-ordenar-flecha").html("&#x25B2");
  
  $(".subcabecera-pestania-ordenar").slideToggle("fast");
});

$(".subcabecera-pestania-ordenar-row").click(function()
{

  $("#cabecera-ordenar").removeClass("cabecera-pestania-seleccionada",150);
  $(".cabecera-ordenar-flecha").html("&#x25BC"); 
  $(".subcabecera-pestania-ordenar").slideUp("fast");
  cargarDatos("eventos"); //Coger parámetro de orden del texto de la fila
});



/* 
---------------------------------------------------------------------------------------------
------------------------------------BIND KEYUP SUGERENCIAS-----------------------------------
---------------------------------------------------------------------------------------------
*/


$('#input-busqueda').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
      if(window.selectedSuggestion==0)
        window.selectedSuggestion=1;
      var fila="#cabecera-suggest-fila-"+(window.selectedSuggestion-1);
      $(fila).trigger("click");
      /*
      var icono=$(fila).find(".cabecera-suggest-icono").css('background-image');
      icono=icono.substring(4,icono.length-1);
      var texto=$(fila).find(".cabecera-suggest-texto1").text();
      clickSuggestion(icono,texto,'busqueda',0);
      */
        return;
        break;
    case 27:  //Escape
        $("#cabecera-suggest").empty();
        $(this).val("");
        break;
    case 38:  //Up
        prevSuggestion();
        //return;
        break;
    case 40:  //Down
        nextSuggestion();
        //return;
        break;
    default:
        suggestBusqueda($(this).val());
  }
});


/* 
---------------------------------------------------------------------------------------------
----------------------------------------Inicialización---------------------------------------
---------------------------------------------------------------------------------------------
*/


var arrayTags = new Array();
cargarDatos("eventos");




