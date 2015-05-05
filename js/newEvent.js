function isValidEmailAddress(emailAddress) 
{
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function isValidURL(url)
{
  var pattern = new RegExp(/^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/ig);
  return pattern.test(url);
};

function suggestTematicas(texto)
{

  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestionsTematicas.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    format: "json",
  })
  .done(function(data) 
  {
    $("#tematicas-tooltip-select").empty();

    if(data.length<1)
    {
      $("#tematicas-tooltip-cabecera").html("Pulsa Enter para crear etiqueta");
    }
    else
    {
      $("#tematicas-tooltip-cabecera").html("Selecciona temática o escribe etiqueta para evento");
      var i=1;
      $.each(data, function(key, value)
      {
        $("#tematicas-tooltip-select").append("<div class='tematicas-tooltip-select-file'>"+value.tematica.replace(new RegExp("("+texto+")", 'gi'), "<b>$1</b>")+"</div>");
        $("#tematicas-tooltip-select").find(".tematicas-tooltip-select-file:last").click(function()
        {
          addTematica(value.idTematica,value.tematica);
        });
        i++;
      });  
    }

    $("#tematicas-tooltip").show();
  });
}

function addTematica(idTematica,tematica)
{
  $("#newEvent-tematica").val("");
  $("#tematicas-tooltip").hide();

  repetida=false;
  $.each(arrayTematicasNuevoEvento, function(key,value) 
  {
    if((value.idTematica==idTematica)&(value.tematica==tematica)) //Ya la teníamos
    {
      console.log("Repetida");
      repetida=true;
    }
  });

  if(repetida)
    return;


  var elemento= {};
  elemento["idTematica"]=idTematica;
  elemento["tematica"]=tematica;

  arrayTematicasNuevoEvento.push(elemento);
  
  var clone=$("#tagTematicaTemplate").clone();
  clone.hide();
  clone.attr("id",idTematica);

  clone.find('.tagTematica-texto').html(tematica);
  if(idTematica>0)
    clone.find('.tagTematica-imagen').addClass("tagTematica-imagen-tematica");
  else
    clone.find('.tagTematica-imagen').addClass("tagTematica-imagen-etiqueta");

  
  clone.find('.tagTematica-x').click(function()
  {
    //Borrado
    arrayTematicasNuevoEvento = jQuery.grep(arrayTematicasNuevoEvento, function(value) 
    {
      var coincide=((value.idTematica==idTematica)&(value.tematica==tematica));
      return !coincide;
    });

    $(this).fadeOut("fast",function()
    {
      $(this).parent().remove();
    });
  });
  

  clone.appendTo("#tematicas-grupo");
  clone.fadeIn("fast");
  $("#tematicas-grupo").animate({ scrollTop: $('#tematicas-grupo').height()}, 200);  
  
}

function suggestCiudades(texto)
{

  $("#newEvent-idCiudad").val("");

  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestionsCiudades.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    format: "json",
  })
  .done(function(data) 
  {
    $("#ciudad-tooltip-select").empty();

    if(data.length<1)
    {
      $("#ciudad-tooltip-cabecera").html("No hay ninguna ciudad con ese nombre");
    }
    else
    {
      $("#ciudad-tooltip-cabecera").html("Selecciona una ciudad para el evento");
      var i=1;
      $.each(data, function(key, value)
      {
        $("#ciudad-tooltip-select").append("<div class='ciudad-tooltip-select-file'>"+value.nombre.replace(new RegExp("("+texto+")", 'gi'), "<b>$1</b>")+"</div>");
        $("#ciudad-tooltip-select").find(".ciudad-tooltip-select-file:last").click(function()
        {
          if(value.activo==="1")
          {
            $("#newEvent-idCiudad").val(value.id);
            $("#newEvent-idLugar").val("");
            window.newEventCiudadID=value.id;
            window.newEventCiudad=value;
            $('#newEvent-ciudad').val(value.nombre);
            $("#ciudad-tooltip").hide();
            $("#newEvent-lugar").css('font-style','inherit').attr('placeholder','Lugar').prop('disabled', false);
            updateMapLocationCoordinatesCentroid(value.xmin,value.ymin,value.xmax,value.ymax,value.xcentroid,value.ycentroid);
            if(!(typeof window.marker==='undefined'))
              mapNewEvent.removeLayer(window.marker);
            $("#newEvent-lugar").val("");
            $("#newEvent-lugar").show();
          }
          else
          {
            $("#overlay").fadeOut("fast",function()
            {
              $("#overlay").addClass("overlayPeque");
              $("#overlay").load("cityNotReadyYet.html",function()
              {
                $('#overlay').html($('#overlay').html().replace(/{CIUDAD}/g,value.nombre));
                $('#input-email-idLugar').val(value.id);
                $('#input-email-nombreCiudad').val(value.nombre);   
                $("#overlay").fadeIn("fast");
              });       
            });  
          }
        });
        i++;
      });  
    }

    $("#ciudad-tooltip").show();
  });
}

function cargarMapaNewEvent()
{
  
  //Creamos el mapa
  var mapNewEvent = L.map('newEvent-map',{
            zoomControl: false,
            attributionControl: false,
        }).setView([40.47,-3.45], 9);

  /*map.on('click', function() 
  {
    alert('Has hecho click en el mapa');
  });
*/
  
  mapNewEvent.dragging.disable();
  mapNewEvent.touchZoom.disable();
  mapNewEvent.doubleClickZoom.disable();
  mapNewEvent.scrollWheelZoom.disable();
  mapNewEvent.boxZoom.disable();
  mapNewEvent.keyboard.disable();
  
  window.mapNewEvent=mapNewEvent;
  var ggl = new L.Google();
  L.Google('roadmap');
  mapNewEvent.addLayer(ggl);
  

  //L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(map);  
  
}

function updateMapLocationCoordinatesCentroid(xmin,ymin,xmax,ymax,xcentroid,ycentroid)
{
    southWest = L.latLng(ymin, xmin),
    northEast = L.latLng(ymax, xmax),
    bounds = L.latLngBounds(southWest, northEast);

    mapNewEvent.fitBounds(bounds);

    mapNewEvent.setView(L.latLng(ycentroid, xcentroid),mapNewEvent.getZoom()+1);


}

function prevLugarSuggestionNewEvent()
{
  if((typeof window.selectedLugarSuggestion==='undefined')|(window.selectedLugarSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:first").addClass("newEvent-lugarSuggest-fila-selected");    
    window.selectedLugarSuggestion=1;
  }
  else
  {
    if(window.selectedLugarSuggestion>1)
    {
      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila").eq(window.selectedLugarSuggestion-1).removeClass("newEvent-lugarSuggest-fila-selected");    
      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila").eq(window.selectedLugarSuggestion-2).addClass("newEvent-lugarSuggest-fila-selected");    
      window.selectedLugarSuggestion--;
    }
  }
}

function nextLugarSuggestionNewEvent()
{
  if((typeof window.selectedLugarSuggestion==='undefined')|(window.selectedLugarSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:first").addClass("newEvent-lugarSuggest-fila-selected");    
    window.selectedLugarSuggestion=1;
  }
  else
  {
    if($("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila").length>window.selectedLugarSuggestion)
    {
      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila").eq(window.selectedLugarSuggestion-1).removeClass("newEvent-lugarSuggest-fila-selected");    
      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila").eq(window.selectedLugarSuggestion).addClass("newEvent-lugarSuggest-fila-selected");    
      window.selectedLugarSuggestion++;
    }
  }
}

function clickSuggestionNewEvent(index)
{
  console.log(index);
  console.log(window.arraySuggestionsDirecciones[index]);
  $("#newEvent-lugarSuggest").empty();
  updateMapLocationDireccion(window.arraySuggestionsDirecciones[index]);
  $(".newEvent-lugarDiv-texto1").html(window.arraySuggestionsDirecciones[index].texto1);
  $(".newEvent-lugarDiv-texto2").html(window.arraySuggestionsDirecciones[index].texto2);
  $("#newEvent-lugar").hide();
  $("#newEvent-idLugar").val(window.arraySuggestionsDirecciones[index].id);

}

function updateMapLocationDireccion(direccion)
{
    var latlng = L.latLng(direccion.lat, direccion.lon);
    mapNewEvent.setView(latlng,direccion.zoom);
    if(!(typeof window.marker==='undefined'))
      mapNewEvent.removeLayer(window.marker);
    window.marker = L.marker(latlng).addTo(mapNewEvent);
}


function suggestLugar(texto)
{
  
  if((window.previousSuggestLugar==texto)&(texto!=""))
  {
    return;
  }

  $('#newEvent-idLugar').val("");
  window.previousSuggestLugar=texto;
 

  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestionsDirecciones.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    idTerritorio: window.newEventCiudadID,
  })
  .done(function(data) 
  {
    $("#newEvent-lugarSuggest").empty();    
    window.arraySuggestionsDirecciones=[];

    //window.conf.selectedSuggestion=0;

    //Que esto lo clone de una fila por defecto      
    var i=0;
    $.each(data.suggestions, function(key, value)
    {
      window.arraySuggestionsDirecciones[value.id]=value;
      //Creamos la sugerencia
      $("#newEvent-lugarSuggest").append("<div class='newEvent-lugarSuggest-fila'><div class='newEvent-lugarSuggest-texto1'>"+value.texto1.replace(texto,"<strong>"+texto+"</strong>")+"</div><div class='newEvent-lugarSuggest-texto2'>"+value.texto2+"</div></div>");
      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").attr("id","newEvent-lugarSuggest-fila-"+i);
      if(value.texto2=="")
      {
        $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-texto1:last").addClass("newEvent-lugarSuggest-texto1-sinTexto2");
      }

      $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").click(function()
      {
          clickSuggestionNewEvent(value.id);
      });
      i++;
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });

    $("#newEvent-lugarSuggest").append("<div class='newEvent-lugarSuggest-fila newEvent-lugarSuggest-crear'><div class='newEvent-lugarSuggest-texto1-sinTexto2'><strong>Crear un nuevo lugar</strong></div></div>");
    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").attr("id","newEvent-lugarSuggest-fila-"+i);
    if(data.suggestions.length<=0)
    {
      $("#newEvent-lugarSuggest").width($("#newEvent-lugar").width()+4);
      $("#newEvent-lugarSuggest").css('margin-left',1);
    }
    else
    {
      $("#newEvent-lugarSuggest").css('width','');
      $("#newEvent-lugarSuggest").css('margin-left','');
    }

    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").click(function()
    {

      createNewPlace($('#newEvent-lugar').val(),15,true,window.newEventCiudad);
    });

  });

}

function createNewPlace(lugar, zoom, withMarker,ciudad)
{  
  $.getJSON("http://maps.google.com/maps/api/geocode/json", 
  {
    address: lugar,
    sensor: 'false',
    components: 'locality:'+ciudad.nombre
  })
  .done(function (response) 
  {
    if(($.inArray('street_address', response.results[0].types) < 0)&($.inArray('route', response.results[0].types) < 0))
    {
      alert("Lo sentimos, no hemos encontrado esa dirección dentro de "+ciudad.nombre+". Por favor, inténtalo con otra dirección.");
      return;
    }

    southWest = L.latLng(response.results[0].geometry.viewport.southwest.lat, response.results[0].geometry.viewport.southwest.lng),
    northEast = L.latLng(response.results[0].geometry.viewport.northeast.lat, response.results[0].geometry.viewport.northeast.lng),
    bounds = L.latLngBounds(southWest, northEast);

    if(!typeof zoom ==='undefined')
      mapNewEvent.setZoom(zoom);
    else
      mapNewEvent.fitBounds(bounds);

    if(withMarker)
    {
      if(!(typeof window.marker==='undefined'))
        mapNewEvent.removeLayer(window.marker);
      window.marker = L.marker(mapNewEvent.getCenter()).addTo(mapNewEvent);
    }
    $("#newEvent-lugarSuggest").empty();

    var result = prompt("¿Cual es el nombre de este lugar?");
    $(".newEvent-lugarDiv-texto1").html(result);
    $(".newEvent-lugarDiv-texto2").html($("#newEvent-lugar").val());
    $("#newEvent-idLugar").val(0);
    $("#newEvent-lugar").hide();

  });
  
}  


var editor = new wysihtml5.Editor("newEvent-descripcion", 
{ // id of textarea element
  toolbar:      "newEvent-toolbar", // id of toolbar element
  parserRules:  wysihtml5ParserRules // defined in parser rules set 
});


$(".newEvent-lugarDiv").on("click",function()
{
  $("#newEvent-lugar").val("");
  $("#newEvent-lugar").show();
  suggestLugar("");
  $("#newEvent-lugar").focus();  
});

$('#newEvent-lugar').on("click",function()
{
  suggestLugar("");
});

$('#newEvent-lugar').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
      //updateMapLocationNewEvent($('#newEvent-direccion').val()+", "+$('#newEvent-ciudad').val());
      /*
      if(window.conf.selectedSuggestion==0)
        window.conf.selectedSuggestion=1;
      var fila="#cabecera-suggest-fila-"+(window.conf.selectedSuggestion-1);
      $(fila).trigger("click");
      */
      /*
      var icono=$(fila).find(".cabecera-suggest-icono").css('background-image');
      icono=icono.substring(4,icono.length-1);
      var texto=$(fila).find(".cabecera-suggest-texto1").text();
      clickSuggestionNewEvent(icono,texto,'busqueda',0);
      */
        return;
        break;
    case 27:  //Escape
        $("#newEvent-lugarSuggest").empty();
        $(this).val("");
        window.selectedLugarSuggestion=0;
        break;
    case 38:  //Up
        prevLugarSuggestionNewEvent();
        //return;
        break;
    case 40:  //Down
        nextLugarSuggestionNewEvent();
        //return;
        break;
    default:
        suggestLugar($(this).val());
  }
});

$('#newEvent-tematica').bind('mousedown',function(event)
{
  suggestTematicas($(this).val());
});

/*
$('#newEvent-tematica').focusout(function()
{
  //Al salir deberíamos de perder el foco, siempre que no sea en el tooltip
  //$("#tematicas-tooltip").hide();
  //$(this).val("");
});
*/

$('#newEvent-propose').bind('mousedown',function(event)
{
  var enviar=true;

  if($('#newEvent-titulo').val()==="")
  {
    enviar=false;
    $('#newEvent-titulo').css('border-color','red').focus(function(){$('#newEvent-titulo').css('border-color','')});
  }
  else
  {
    $('#newEvent-titulo').css('border-color','');    
  }

  if(!isValidEmailAddress($('#newEvent-email').val()))
  {
    enviar=false;
    $('#newEvent-email').css('border-color','red').focus(function(){$('#newEvent-email').css('border-color','')});
  }
  else
  {
    $('#newEvent-email').css('border-color','');    
  }

  if(!isValidURL($('#newEvent-webEvento').val()))
  {
    enviar=false;
    $('#newEvent-webEvento').css('border-color','red').focus(function(){$('#newEvent-webEvento').css('border-color','')});
  }
  else
  {
    $('#newEvent-webEvento').css('border-color','');    
  }


  alMenosUnaTematica=false;
  $.each(arrayTematicasNuevoEvento,function(key,tematica)
  {
    if(tematica.idTematica>0)
      alMenosUnaTematica=true;
  });

  if(!alMenosUnaTematica)
  {
    enviar=false;
    alert('Tienes que elegir al menos una temática de la lista desplegable, a parte de las etiquetas que crees.');
    $('#newEvent-tematica').css('border-color','red').focus(function(){$('#newEvent-tematica').css('border-color','')});
  }
  else
  {
    $('#newEvent-tematica').css('border-color','');    
  }

  if($('#newEvent-idCiudad').val()==="")
  {
    enviar=false;
    $('#newEvent-ciudad').css('border-color','red').focus(function(){$('#newEvent-ciudad').css('border-color','')});
  }
  else
  {
    $('#newEvent-ciudad').css('border-color','');    
  }

  if($('#newEvent-idLugar').val()==="")
  {
    enviar=false;
    $('#newEvent-lugar').css('border-color','red').focus(function(){$('#newEvent-lugar').css('border-color','')});
  }
  else
  {
    $('#newEvent-lugar').css('border-color','');    
  }

  if($('#newEvent-descripcion').val()==="")
  {
    enviar=false;
    $('.wysihtml5-sandbox').css('border-color','red');
    $('.wysihtml5-sandbox').css('border-width','2px');
  }
  else
  {
    $('.wysihtml5-editor').css('border-color','');    
  }
  
  if(!($('#newEvent-datepicker').val().match(/\b\d{2}\/\d{2}\/\d{4}\b/g)))
  {
    enviar=false;
    $('#newEvent-datepicker').css('border-color','red').focus(function(){$('#newEvent-datepicker').css('border-color','')});
  }
  else
  {
    $('#newEvent-datepicker').css('border-color','');    
  }
  
  if(!($('#newEvent-horaInicio').val().match(/\b\d{2}:\d{2}\b/g)))
  {
    enviar=false;
    $('#newEvent-horaInicio').css('border-color','red').focus(function(){$('#newEvent-horaInicio').css('border-color','')});
  }
  else
  {
    $('#newEvent-horaInicio').css('border-color','');    
  }
  
  if(($('#newEvent-horaFinal').val()!="")&(!($('#newEvent-horaFinal').val().match(/\b\d{2}:\d{2}\b/g))))
  {
    enviar=false;
    $('#newEvent-horaFinal').css('border-color','red').focus(function(){$('#newEvent-horaFinal').css('border-color','')});
  }
  else
  {
    $('#newEvent-horaFinal').css('border-color','');    
  }
  
  if(enviar)
  {

    var datosAEnviar= {};
    datosAEnviar["titulo"]=$("#newEvent-titulo").val();
    datosAEnviar["email"]=$("#newEvent-email").val();
    datosAEnviar["webEvento"]=$("#newEvent-webEvento").val();
    datosAEnviar["tematicas"]=arrayTematicasNuevoEvento;
    datosAEnviar["idCiudad"]=$("#newEvent-idCiudad").val();
    datosAEnviar["ciudad"]=$("#newEvent-ciudad").val();
    datosAEnviar["idTerritorio"]=$("#newEvent-idLugar").val();
    datosAEnviar["lugar"]=$("#newEvent-lugar").val();
    datosAEnviar["nombreLugar"]=$(".newEvent-lugarDiv-texto1").html();
    datosAEnviar["coordenadas"]=window.marker.getLatLng();
    datosAEnviar["descripcion"]=$("#newEvent-descripcion").val();
    datosAEnviar["fecha"]=$("#newEvent-datepicker").val();
    datosAEnviar["horaInicio"]=$("#newEvent-horaInicio").val();
    datosAEnviar["horaFinal"]=$("#newEvent-horaFinal").val();

    $.post( "newEvent.php", { data: escape(JSON.stringify(datosAEnviar))})
    .done(function(data) 
    {
      $("#overlay").fadeOut("fast",function()
      {
        $("#overlay").addClass("overlayPeque");
        $("#overlay").load("eventoEnviado.html",function()
        {
          $("#overlay").fadeIn("fast");
        });       
      });  
    });
  }

});



$('#newEvent-ciudad').bind('mousedown',function(event)
{
  suggestCiudades($(this).val());
});

$('#newEvent-tematica').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
        if($(this).val()!="")
          addTematica(0,$(this).val());
        break;
    case 27:  //Escape
        $("#tematicas-tooltip").hide();
        $(this).val("");
        break;
    default:
        suggestTematicas($(this).val());
  }
});


$('#newEvent-ciudad').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
        if($(this).val()!="")
        {
          //Preseleccionar una ciudad
        }
        break;
    case 27:  //Escape
        $("#newEvent-lugar").css('font-style','Italic').attr('placeholder','(Elige primero una ciudad)').prop('disabled','disabled');
        $("#ciudad-tooltip").hide();
        $(this).val("");
        break;
    default:
        suggestCiudades($(this).val());
  }
});

var arrayTematicasNuevoEvento = new Array();

jQuery('#newEvent-datepicker').datetimepicker({
 lang:'es',
  i18n:{
  es:{
   months:[
    'Enero','Febrero','Marzo','Abril',
    'Mayo','Junio','Julio','Agosto',
    'Septiembre','Octubre','Noviembre','Diciembre',
   ],
   dayOfWeek:[
    "Do", "Lu", "Ma", "Mi", 
    "Ju", "Vi", "Sa",
   ]
  }
 },
 dayOfWeekStart: 1,
 timepicker:false,
 format:'d/m/Y',
 validateOnBlur:false,
 closeOnDateSelect:true
});

jQuery('#newEvent-horaInicio').datetimepicker({
 lang:'es',
 datepicker:false,
 format:'H:i',
 validateOnBlur:false,
 closeOnDateSelect:true
});

jQuery('#newEvent-horaFinal').datetimepicker({
 lang:'es',
 datepicker:false,
 format:'H:i',
 validateOnBlur:false,
 closeOnDateSelect:true
});


/*
 
 */

cargarMapaNewEvent();