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

function clickSuggestion(tipo,texto1) //Añadir id, texto buscado
{
  $("#cabecera-suggest").empty();
  console.log("Clic Sugerencia: "+tipo+"/"+texto1);
  if(!$('#input-busqueda').tagExist(texto1))
    $("#input-busqueda").addTag(texto1,{icon:tipo});

  //window.location = "agenda.html?tipo="+tipo+"&texto1="+texto1;
  //alert("Has hecho click en una sugerencia de tipo "+tipo+" con el siguiente texto: "+texto1);
}


function prevSuggestion()
{
  if((typeof window.selectedSuggestion==='undefined')|(window.selectedSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");    
    window.selectedSuggestion=1;
  }
  else
  {
    if(window.selectedSuggestion>1)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-2).addClass("cabecera-suggest-fila-selected");    
      window.selectedSuggestion--;
    }
  }
}

function nextSuggestion()
{
  if((typeof window.selectedSuggestion==='undefined')|(window.selectedSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");    
    window.selectedSuggestion=1;
  }
  else
  {
    if($("#cabecera-suggest").find(".cabecera-suggest-fila").length>window.selectedSuggestion)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion).addClass("cabecera-suggest-fila-selected");    
      window.selectedSuggestion++;
    }
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

    //ToDo: Que esto vaya ello solo sin necesidad de recolocarlo
    //Recolocamos la cabecera, el cuerpo y los botones según los filtros que haya
    $("#cabecera-suggest").css('margin-top', $("#input-busqueda_tagsinput").position().top+$('#input-busqueda_tagsinput').outerHeight(true)+4);
    $(".cuerpo").css('margin-top', 20+$('#input-busqueda_tagsinput').outerHeight(true));
    $(".map").css('top', 20+$('#input-busqueda_tagsinput').outerHeight(true));
    $(".informacion").css('top', 295+$('#input-busqueda_tagsinput').outerHeight(true));
    $(".botonesSuperiores").css('top', 20+$('#input-busqueda_tagsinput').outerHeight(true));
    
    $(".scroll-curtain-gradient").css('top', 22+$('.botonesSuperiores').position().top);
    $(".scroll-curtain").css('height', $(".scroll-curtain-gradient").position().top-43);
    

    window.selectedSuggestion=0;

    //Añadimos el tooltip
    $("#cabecera-suggest").append("<div class='cabecera-suggest-tooltip'>Buscar eventos que tengan que ver con...</div>");


    //Que esto lo clone de una fila por defecto      
    //Añadimos la búsqueda tal cual
    $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'><strong>"+texto+"</strong></div></div>");
    $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-busqueda");
    $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-0");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
          clickSuggestion("/citysens/icons/lupa.png",texto); //Añadir value.id, texto buscado
      });
    var i=1;
    $.each(data.suggestions, function(key, value)
    {
      //Creamos la sugerencia
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+value.texto1.replace(texto,"<strong>"+texto+"</strong>")+"</div><div class='cabecera-suggest-texto2'>"+value.texto2+"</div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-"+value.tipo);
    $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-"+i);
      if(value.texto2=="")
      {
        $("#cabecera-suggest").find(".cabecera-suggest-texto1:last").addClass("cabecera-suggest-texto1-sinTexto2");
      }

      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
          var icono="";
          if(value.tipo=="sanidad")
            icono="/citysens/icons/sanidad.png";
          else if(value.tipo=="organizacion")
            icono="/citysens/icons/icon_CitYsens.organizacion.png";
          else if(value.tipo=="institucion")
            icono="/citysens/icons/icon_CitYsens.institucion.png";
          else if(value.tipo=="colectivo")
            icono="/citysens/icons/CitYsens.people.png";
          else if(value.tipo=="lugar")
            icono="/citysens/icons/lugar.png";
          else if(value.tipo=="lupa")
            icono="/citysens/icons/lupa.png";
          else if(value.tipo=="busqueda")
            icono="/citysens/icons/lupa.png";


          clickSuggestion(icono,value.texto1); //Añadir value.id, texto buscado
      });
      i++;
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });

    if(texto=="san")//IrAMadrid
    {
      //Creamos la fila de ir a Madrid
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a "+texto.replace(texto,"<strong>"+texto+"</strong>")+"</div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").addClass("cabecera-suggest-fila-IrA");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-IrA");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-"+i);
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
        clickSuggestion("/citysens/icons/gps.png",texto); //Añadir value.id, texto buscado
      });
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

/*

*/

function cargarDatos(clase)
{

  var query=$("#input-busqueda").val();

/*
  if($("#input-busqueda").val()=="")
    $(".agenda-primera-linea").height("0px");
  else
    $(".agenda-primera-linea").height("auto");
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


$('#input-busqueda').tagsInput({
        'height':'16px',
        'width':'485px',
        'defaultText':'',
        onChange: function(elem, elem_tags)
        {
          //console.log($(this).val());
          /*
          var languages = ['php','ruby','javascript'];
          $('.tag', elem_tags).each(function()
          {
            if($(this).text().search(new RegExp('\\b(' + languages.join('|') + ')\\b')) >= 0)
              $(this).css('background-color', 'yellow');
          });
          */
        }
      });

function addPolygonToMap(idLugar,url,texto,color)
{
$.ajax({
    type: "POST",
    url: url,
    dataType: 'json',
    success: function (response) 
    {
      //console.log(response);
      geojsonLayer = L.geoJson(response,{fillColor: color, weight: 1}).addTo(map);
      geojsonLayer.on('click',function()
      {
        //history.pushState(null, null, "http://localhost:8888/citysens/?idLugar="+idLugar);
        window.location="http://localhost:8888/citysens/?idLugar="+idLugar;
        //alert('Esto cargaría la página de '+texto);
      });
      
      geojsonLayer.on('mouseover', function(e) 
      {
        $(".map-footer").html("Ir a "+texto);
      });
      
      geojsonLayer.on('mouseout', function(e) 
      {
        $(".map-footer").html("&nbsp;");
      });
      
    }
});  
}

function cargarMapa(idLugar)
{
  
  //Creamos el mapa
  var map = L.map('map',{zoomControl: false,attributionControl: false});
  map.on('click', function() 
  {
    alert('Has hecho click en el mapa');
  });
  
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



  L.NumberedDivIcon = L.Icon.extend(
  {
    options: {
      iconUrl: '',
      number: '',
      shadowUrl: null,
      iconSize: new L.Point(24, 24),
      iconAnchor: new L.Point(12, 12),
      popupAnchor: new L.Point(0, -12),
      /*
      iconAnchor: (Point)
      popupAnchor: (Point)
      */
      className: 'leaflet-div-icon'
    },
   
    createIcon: function () {
      var div = document.createElement('div');
      //var img = this._createImg(this.options['iconUrl']);
      var numdiv = document.createElement('div');
      numdiv.setAttribute ( "class", "number" );
      numdiv.innerHTML = this.options['number'] || '';
      //div.appendChild( img );
      div.appendChild( numdiv );
      this._setIconStyles(div, 'icon');
      return div;
    },
   
    //you could change this to add a shadow like in the normal marker if you really wanted
    createShadow: function () {
      return null;
    }
  });

  L.TargetIcon = L.Icon.extend(
  {
    options: {
      iconUrl: '/citysens/icons/mira.png',
      number: '',
      shadowUrl: null,
      iconSize: new L.Point(38, 37),
      iconAnchor: new L.Point(29, 29),
      popupAnchor: new L.Point(0, -29),
      /*
      iconAnchor: (Point)
      popupAnchor: (Point)
      */
      className: 'leaflet-target-icon'
    },
   
    createIcon: function () {
      var div = document.createElement('div');
      var img = this._createImg(this.options['iconUrl']);
      /*var numdiv = document.createElement('div');
      numdiv.setAttribute ( "class", "number" );
      numdiv.innerHTML = this.options['number'] || '';*/
      div.appendChild( img );
      //div.appendChild( numdiv );
      this._setIconStyles(div, 'icon');
      return div;
    },
   
    //you could change this to add a shadow like in the normal marker if you really wanted
    createShadow: function () {
      return null;
    }
  });


    $.getJSON("getMapData.php", 
    {
      dataType: 'json',
      idLugar: idLugar,
    })
    .done(function (response) 
    {
      padding=0.1;
      paddingX=padding*(response.xmax-response.xmin);
      paddingY=padding*(response.ymax-response.ymin);

      xmin=response.xmin;
      ymin=response.ymin;
      xmax=response.xmax;
      ymax=response.ymax;


      var southWest = L.latLng(ymin, xmin),
      northEast = L.latLng(ymax, xmax),
      bounds = L.latLngBounds(southWest, northEast);
      map.fitBounds(bounds);

      //Cargamos las cosas relativas a la ciudad: Filtrado eventos, breadcrumbs, etc...
      //España > Madrid > <?=$datosLugar["nombre"];?>
      $(".map-breadcrumbs").html("España > Madrid > "+response.nombre);
      $(".agenda-primera-linea").html("Mostrando EVENTOS en <strong>"+response.nombre+"</strong>");

      //Aquí cargaríamos los distritos
      /*
      addPolygonToMap("Distrito I","shp/geoJSON/9/00501.geojson","Distrito I",'#ffaaaa',true);
      addPolygonToMap("Distrito II","shp/geoJSON/9/00502.geojson","Distrito II",'#ffaaaa',true);
      addPolygonToMap("Distrito III","shp/geoJSON/9/00503.geojson","Distrito III",'#ffaaaa',true);
      addPolygonToMap("Distrito IV","shp/geoJSON/9/00504.geojson","Distrito IV",'#ffaaaa',true);
      addPolygonToMap("Distrito V","shp/geoJSON/9/00505.geojson","Distrito V",'#ffaaaa',true);
      */

      $.getJSON("getChildAreas.php", 
      {
          dataType: 'json',
          nivel:'9',
          lugarOriginal:idLugar,
      })
      .done(function(data) 
      {
        $.each(data, function(i,datos)
        {
          addPolygonToMap(datos[0],"shp/geoJSON/9/"+datos[4]+".geojson",datos[1],'#ffaaaa',true);
          new L.Marker([datos[3],datos[2]], 
          {
            icon: new L.NumberedDivIcon({number: datos[5]})
          }).addTo(map);

        });
      });
      

      //Aquí cargamos los eventos
      $.getJSON("getEventosCoordenadas.php", 
      {
          dataType: 'json',
          xmin:xmin,
          ymin:ymin,
          xmax:xmax,
          ymax:ymax,
      })
      .done(function(data) 
      {
        //console.log(data);
        window.markers = [];
        $.each(data, function(i,datos)
        {
          //var marker=L.marker([datos.y,datos.x],{opacity:0.0}).addTo(map);
          var marker=new L.Marker([datos.y,datos.x], 
          {
            icon: new L.TargetIcon()
          }).setOpacity(0).setZIndexOffset(1000).addTo(map);
          markers[datos.idEvento]=marker;
          //.bindPopup("<b>"+datos.titulo+"</b><br />"+datos.texto);//.openPopup();
        });
      });

      //http://localhost:8888/citysens/getEventosCoordenadas.php?xmin=-3.64643&ymin=40.37454&xmax=-3.10192&ymax=40.60744

      
      //L.marker([40.470,-3.350]).addTo(map)
      //    .bindPopup("<b>Esto es un evento</b><br />Soy un evento");//.openPopup();
      

      //Aquí cargamos los colindantes
      $.getJSON("getLugaresColindantes.php", 
      {
          dataType: 'json',
          tipo:'8',
          xmin:xmin,
          ymin:ymin,
          xmax:xmax,
          ymax:ymax,
          lugarOriginal:idLugar,
      })
      .done(function(data) 
      {
        $.each(data, function(i,datos)
        {
          addPolygonToMap(datos[0],"shp/geoJSON/8/"+datos[0]+".geojson",datos[1],'#aaaaff',true);
          
          /*
          var marker = new L.Marker([datos[3],datos[2]], 
          {
            icon: new L.NumberedDivIcon({number: datos[0]})
          }).addTo(map);
          */

        });
      });
    });

  
}

function irACoordenadas(coordinates,zoom)
{
  window.map.panTo(coordinates);
}

cargarDatos("eventos");

//irACoordenadas([30.48166,-3.364136], 13);





