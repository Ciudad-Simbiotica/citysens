
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

function updateMapLocationNewEvent(lugar)
{
  console.log(lugar);
  
  $.getJSON("http://maps.google.com/maps/api/geocode/json", 
  {
    address: lugar,
    sensor: 'false',
  })
  .done(function (response) 
  {
    //console.log(response.results[0].geometry.bounds);   

    southWest = L.latLng(response.results[0].geometry.viewport.southwest.lat, response.results[0].geometry.viewport.southwest.lng),
    northEast = L.latLng(response.results[0].geometry.viewport.northeast.lat, response.results[0].geometry.viewport.northeast.lng),
    bounds = L.latLngBounds(southWest, northEast);


    mapNewEvent.fitBounds(bounds);
 
  });
  
}

function prevLugarSuggestion()
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

function nextLugarSuggestion()
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

function clickSuggestion(index)
{
  console.log(index);
  console.log(window.arraySuggestionsDirecciones[index]);
  $("#newEvent-lugarSuggest").empty();
  updateMapLocationDireccion(window.arraySuggestionsDirecciones[index]);
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
  
  if(window.previousSuggestLugar==texto)
  {
    return;
  }

  window.previousSuggestLugar=texto;
  if(texto=="")
  {
    $("#newEvent-lugarSuggest").empty();
    return;    
  }
  

  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestionsDirecciones.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    idLugar: $.urlParam('idLugar'),
    date: "any",
    format: "json"
  })
  .done(function(data) 
  {
    $("#newEvent-lugarSuggest").empty();    
    window.arraySuggestionsDirecciones=[];

    //window.selectedSuggestion=0;

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
          clickSuggestion(value.id);
      });
      i++;
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });

    $("#newEvent-lugarSuggest").append("<div class='newEvent-lugarSuggest-fila newEvent-lugarSuggest-crear'><div class='newEvent-lugarSuggest-texto1-sinTexto2'><strong>Crear un nuevo lugar</strong></div></div>");
    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").attr("id","newEvent-lugarSuggest-fila-"+i);
    $("#newEvent-lugarSuggest").find(".newEvent-lugarSuggest-fila:last").click(function()
    {
        //clickSuggestion(icono,value.texto1,value.tipo,value.id);
    });

  });

}




$(function() 
{
  $( "#newEvent-datepicker" ).datepicker($.datepicker.regional["es"]);
});

var editor = new wysihtml5.Editor("newEvent-descripcion", { // id of textarea element
  toolbar:      "newEvent-toolbar", // id of toolbar element
  parserRules:  wysihtml5ParserRules // defined in parser rules set 
});


$('#newEvent-ciudad').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
      updateMapLocationNewEvent($('#newEvent-direccion').val()+", "+$('#newEvent-ciudad').val());
      /*
      if(window.selectedSuggestion==0)
        window.selectedSuggestion=1;
      var fila="#cabecera-suggest-fila-"+(window.selectedSuggestion-1);
      $(fila).trigger("click");
      */
      /*
      var icono=$(fila).find(".cabecera-suggest-icono").css('background-image');
      icono=icono.substring(4,icono.length-1);
      var texto=$(fila).find(".cabecera-suggest-texto1").text();
      clickSuggestion(icono,texto,'busqueda',0);
      */
        return;
        break;
    case 27:  //Escape
        //$("#cabecera-suggest").empty();
        //$(this).val("");
        break;
    case 38:  //Up
        //prevSuggestion();
        //return;
        break;
    case 40:  //Down
        //nextSuggestion();
        //return;
        break;
    default:
        //suggestBusqueda($(this).val());
  }
});

$('#newEvent-lugar').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
      //updateMapLocationNewEvent($('#newEvent-direccion').val()+", "+$('#newEvent-ciudad').val());
      /*
      if(window.selectedSuggestion==0)
        window.selectedSuggestion=1;
      var fila="#cabecera-suggest-fila-"+(window.selectedSuggestion-1);
      $(fila).trigger("click");
      */
      /*
      var icono=$(fila).find(".cabecera-suggest-icono").css('background-image');
      icono=icono.substring(4,icono.length-1);
      var texto=$(fila).find(".cabecera-suggest-texto1").text();
      clickSuggestion(icono,texto,'busqueda',0);
      */
        return;
        break;
    case 27:  //Escape
        $("#newEvent-lugarSuggest").empty();
        $(this).val("");
        window.selectedLugarSuggestion=0;
        break;
    case 38:  //Up
        prevLugarSuggestion();
        //return;
        break;
    case 40:  //Down
        nextLugarSuggestion();
        //return;
        break;
    default:
        suggestLugar($(this).val());
  }
});


cargarMapaNewEvent();