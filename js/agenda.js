//Para sacar los parámetros de GET
$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}

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
  //$(".informacion-cabecera").html("Cargando contenido: "+id);
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
  console.log("Switch "+clase+" "+tipo);
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

function comprobarPlegadoFilas()
{
    if($("#switch-puntuales").hasClass("switch-filas-off"))
      plegarFilas("puntuales","convocatoria",0);
    if($("#switch-recurrentes").hasClass("switch-filas-off"))
      plegarFilas("recurrentes","recurrente",0);

    if($("#switch-instituciones").hasClass("switch-filas-off"))
      plegarFilas("instituciones","institucion",0);
    if($("#switch-organizaciones").hasClass("switch-filas-off"))
      plegarFilas("organizaciones","organizacion",0);
    if($("#switch-colectivos").hasClass("switch-filas-off"))
      plegarFilas("colectivos","colectivo",0);

}

function plegarFilas(clase,tipo,tiempo)
{
  $(".grupo-fila-"+tipo).slideUp(tiempo, function() {});
  $(".grupo-pie-"+tipo).slideDown(tiempo, function() {});
}

/* 
---------------------------------------------------------------------------------------------
-----------------------------------Cargado contenido agenda----------------------------------
---------------------------------------------------------------------------------------------
*/

function paddingZeros(number)
{
  return ("0" + number).slice(-2);
}

function cargarContenido(id)
{
  $(".informacion").slideUp("fast");
  $.getJSON('getDatos.php', 
  {
   id: id, // appears as $_GET['id'] @ ur backend side
  })
  .done(function(data) 
  {
    console.log(data);

    var date = new Date(data.fecha);
    var dateFin = new Date(data.fechaFin);
    var monthNames = [ "ENE", "FEB", "MAR", "ABR", "MAY", "JUN",
    "JUL", "AUG", "SEP", "OCT", "NOV", "DIC" ];


    $(".informacion-cabecera-izq-calendario-top").html(monthNames[date.getMonth()]);
    $(".informacion-cabecera-izq-calendario-bottom").html(date.getDate());
    $(".informacion-cabecera-izq-horas-top").html(paddingZeros(date.getHours())+":"+paddingZeros(date.getMinutes()));
    if(data.fechaFin!=null)
      $(".informacion-cabecera-izq-horas-bottom").html(paddingZeros(dateFin.getHours())+":"+paddingZeros(dateFin.getMinutes()));
    else
      $(".informacion-cabecera-izq-horas-bottom").html('');


    $(".informacion-cabecera-dch-titulo-top").html(data.titulo);
    $(".informacion-cabecera-dch-titulo-bottom").html(data.lugar);


    $(".informacion-cuerpo-tematicas-listado").html('');
    $.each(data.tematicas, function(i, object) 
    {
      if($(".informacion-cuerpo-tematicas-listado").html()!="")
        $(".informacion-cuerpo-tematicas-listado").append(', ');      
      $(".informacion-cuerpo-tematicas-listado").append(object);      
    });

    $(".informacion-cuerpo-etiquetas-listado").html(data.etiquetas);

    $(".informacion-cuerpo-contacto-url").attr("href", data.url);
    $(".informacion-cuerpo-contacto-url").html(data.url);
    $(".informacion-cuerpo-contacto-email").attr("href", "mailto:"+data.email);
    $(".informacion-cuerpo-contacto-email").html(data.email);

    $(".informacion-cuerpo-contacto-email").append(" ");

    $(".informacion-cuerpo-texto").html(data.texto);

    $(".informacion-cabecera").click(function()
    {
      window.location="/citysens/?idEvento="+data.idEvento+"&idOrigen="+window.idLugar;
    });

    url="http://www.citysens.org/?idEvento="+data.idEvento+"%26idOrigen="+window.idLugar;
    mensaje="Texto de ejemplo";

    $(".share-mail").attr("href", "mailto:?subject="+data.titulo+"&body="+mensaje+"%0D%0A%0D%0A"+url);
    $(".share-facebook").attr("href", "https://www.facebook.com/sharer/sharer.php?u="+url+"&t="+data.titulo+"&s="+mensaje+"");
    $(".share-twitter").attr("href", "https://twitter.com/share?url="+url+"&text="+data.titulo+" - ");
    $(".share-googleplus").attr("href", "https://plus.google.com/share?url="+url);
    $(".share-linkedin").attr("href", "http://www.linkedin.com/shareArticle?mini=true&url="+url+"&title="+data.titulo+"&summary="+mensaje+"&source=http://www.citysens.net");
    //$(".share-link").attr("href", "#");

    $(".informacion").slideDown("fast");
  });
}

function removeAllTags()
{
  $(".tagFiltro-busqueda").remove();
  $(".tagFiltro-tematica").remove();
  $(".tagFiltro-lugar").remove();
  $(".tagFiltro-entidad").remove();
  arrayTags=[];
}

function cargarDatos(clase, orden)
{

  $(".informacion").slideUp("fast");
  orden = typeof orden !== 'undefined' ? orden : 'fecha';

  console.log(clase+" "+orden);
  window.clase=clase;
  //[{"texto":"Alcal&aacute; De Henares","tipo":"lugar","id":"4284"}] 
  var hayUnLugar=false;
  var arrayTagsQuery=arrayTags.slice();

  $.each(arrayTagsQuery, function(i, object) 
  {
    if(object.tipo=="lugar")
    {
      hayUnLugar=true;
    }
  });

  if(!hayUnLugar)
  {
    var sugerencia = 
    {
      "texto": "", 
      "tipo": "lugar",
      "id": $.urlParam('idLugar')
    };
    arrayTagsQuery.push(sugerencia);
  }
  

  var query=JSON.stringify(arrayTagsQuery);
  //console.log(arrayTagsQuery);
  //console.log(query);

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
      console.log(arrayTagsQuery);
      conFiltros=":";
      if(arrayTagsQuery.length>1)
        conFiltros=" que satisfacen los siguientes filtros de búsqueda:";
      switch(clase)
      {
        case "eventos":
          if(jQuery.isEmptyObject(data.grupos))
          {
            $(".agenda-primera-linea").html("Ningun evento satisface los filtros de búsqueda:");      
          }
          else
          {
            $(".agenda-primera-linea").html("Mostrando EVENTOS en <strong>"+window.ciudad+"</strong> en las proximas semanas"+conFiltros);
          }
          $("#cabecera-suggest").empty();
          $(".input-busqueda").val('');
          $(".input-busqueda").attr('placeholder', 'Filtrar eventos...');
          break;
        case "organizaciones":
          if(jQuery.isEmptyObject(data.grupos))
          {
            $(".agenda-primera-linea").html("Ninguna entidad satisface los filtros de búsqueda:");      
          }
          else
          {
            $(".agenda-primera-linea").html("Mostrando ENTIDADES en <strong>"+window.ciudad+"</strong>"+conFiltros);
          }        
          $("#cabecera-suggest").empty();
          $(".input-busqueda").val('');
          $(".input-busqueda").attr('placeholder', 'Filtrar entidades...');
          break;
        case "procesos":
          if(jQuery.isEmptyObject(data.grupos))
          {
            $(".agenda-primera-linea").html("Ninguna iniciativa satisface los filtros de búsqueda:");      
          }
          else
          {
            $(".agenda-primera-linea").html("Mostrando INICIATIVAS en <strong>"+window.ciudad+"</strong> en las proximas semanas, que satisfacen los siguientes filtros de búsqueda:");
          }
          break;      
        case "noticias":
          break;
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
    comprobarPlegadoFilas();
    });  
}

/* 
---------------------------------------------------------------------------------------------
---------------------------------------Overlay Loading---------------------------------------
---------------------------------------------------------------------------------------------
*/

function loadOverlay(url,peque)
{
  if(peque)
    $("#overlay").addClass("overlayPeque");
  else
    $("#overlay").removeClass("overlayPeque");

  $(".darkOverlay").fadeIn("fast");
  $("#overlay").load(url);
}

function hideOverlay(url)
{
  $(".darkOverlay").fadeOut("fast",function()
  {
    $('#overlay').removeClass('overlayPeque');
    $('#overlay').removeClass('overlayPeque2');
    $("#overlay").html('');
  });
}

/* 
---------------------------------------------------------------------------------------------
-----------------------------------Nuevo evento, subscribir----------------------------------
---------------------------------------------------------------------------------------------
*/


function newEvent()
{
  loadOverlay("newEvent.html");
}

function isValidEmailAddress(emailAddress) 
{
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function subscribe()
{
  if(isValidEmailAddress($("#email-avisos").val()))
  {
    console.log($("#email-avisos").val());
    //nombreCiudad=$('#input-email-nombreCiudad').val();
    //$.post( "registerMailCityNotReady.php", { email: $(".input-email-ciudad").val(), idCiudad: $("#input-email-idLugar").val() } );
  }
  else
  {
    alert("Introduce un email correcto para suscribirte a los eventos");
  }
}

$(".cabecera-propon").click(function()
{
  newEvent();
});

$("#boton-avisos").click(function()
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
  removeAllTags();

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
  console.log("Mostrando Iniciativas");
  removeAllTags();
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
  console.log("Mostrando Entidades");
  removeAllTags();
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
  removeAllTags();

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

$('select').on('change', function() 
{
  cargarDatos('eventos', $(this).val());
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
        window.selectedSuggestion=0;
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

$('.cabecera-lupa').click(function() 
{
  if(window.selectedSuggestion==0)
        window.selectedSuggestion=1;
  var fila="#cabecera-suggest-fila-"+(window.selectedSuggestion-1);
  $(fila).trigger("click");  
});

/* 
---------------------------------------------------------------------------------------------
----------------------------------------Inicialización---------------------------------------
---------------------------------------------------------------------------------------------
*/


var arrayTags = new Array();


try
{
  var categoria=$.urlParam('category'); //Lanza un error si no hay tipo
  if(categoria==="ent")
  {
    $(".cabecera-pestania-dch").click();
  }
  else if(categoria==="eve")
  {
    $(".cabecera-pestania-izq").click();
  }
}
catch(err)
{
    $(".cabecera-pestania-izq").click();  
}



