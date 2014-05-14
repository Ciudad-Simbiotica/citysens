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
      }
      else if(clase=="tipo")
      {
        //Cambiamos las imágenes según el tipo
        //ToDo: Sustituir el IMG por DIV y cambiar el background por CSS
        if(contenido=="convocatoria")
          clone.find(".imagen-tipo").attr("src", "icons/Event-Unique.64.png")
        else if(contenido=="recurrente")
          clone.find(".imagen-tipo").attr("src", "icons/Event-Recurring.64.png")
        else if(contenido=="institucion")
          clone.find(".imagen-tipo").attr("src", "icons/icon_CitYsens.institucion.png")
        else if(contenido=="organizacion")
          clone.find(".imagen-tipo").attr("src", "icons/icon_CitYsens.organizacion.png")
        else if(contenido=="colectivo")
          clone.find(".imagen-tipo").attr("src", "icons/CitYsens.People.png")
      }
      else if(clase=="participante")
      {
        //Si no es participante escondemos el icono
        if(contenido==0)
          clone.find(".grupo-elemento-participante").hide();
      }
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
}

function cargarContenido(id)
{
  $.ajax({

     type: "GET",
     url: 'getDatos.php',
     data: "id=" + id, // appears as $_GET['id'] @ ur backend side
     success: function(data) {
           // data is ur summary
          $(".informacion-cabecera").html(data);
     }

   });

}

function newEvent()
{
  alert("Creando nuevo evento");
}

function subscribe()
{
  alert("Subscribiéndose");
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

function suggestBusqueda(texto)
{
  //TODO: Chapuza - Usa una variable global, cambiarlo
  if(window.previousSuggestText==texto)
  {
    return;
  }

  window.previousSuggestText=texto;
  if(texto=="")
  {
    $("#cabecera-suggest").empty();
    return;    
  }

  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestions.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    date: "any",
    format: "json"
  })
  .done(function(data) 
  {
    $("#cabecera-suggest").empty();
  
    //Que esto lo clone de una fila por defecto      
    var posTrasSeparador=0;
    $.each(data.suggestions, function(key, value)
    {
      if((value.tipo=="institucion")|(value.tipo=="organizacion")|(value.tipo=="colectivo"))
      {
        if(posTrasSeparador==0)
        {
          //Creamos la fila con la búsqueda actual
          $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+texto+"</div><div class='cabecera-suggest-texto2'></div></div>");
          $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-busqueda");
          posTrasSeparador=1;
          $("#cabecera-suggest").find(".cabecera-suggest-fila:last").addClass("cabecera-suggest-separador");
        }
      }

      //Creamos la sugerencia
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+value.texto1+"</div><div class='cabecera-suggest-texto2'>"+value.texto2+"</div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-"+value.tipo);
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });

    if(posTrasSeparador==0)
    {
      //Creamos la fila con la búsqueda actual en caso de que no haya separador
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+texto+"</div><div class='cabecera-suggest-texto2'></div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-busqueda");
    }

  
  });




}



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

$(".nuevoEvento").click(function()
{
  newEvent();
});

$(".correo").click(function()
{
  subscribe();
});

$("#input-busqueda").keyup(function()
{
  suggestBusqueda($(this).val());
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
        createGroup(grupo,filas.cabeceraIzq,filas.cabeceraCntr,filas.cabeceraDch,filas.totalFilas);
        $.each(filas.filas,function(i,item)
        {
          createLine(grupo,item,0);

        });
      });
    $(".grupo").fadeIn(1000);
    });  
}

cargarDatos("eventos");
