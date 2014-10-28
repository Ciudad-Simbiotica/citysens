$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}

function loadOverlayNoDisponible(url,idCiudad,ciudad)
{
  $("#overlay").addClass("overlayPeque");
  $(".darkOverlay").fadeIn("fast");
  $("#overlay").load(url,function(){
    $('#overlay').html($('#overlay').html().replace(/{CIUDAD}/g,ciudad));
    $('#input-email-idLugar').val(idCiudad);
    $('#input-email-nombreCiudad').val(ciudad);
  });


}

function addPolygonToMap(idLugar,url,texto,color,activo)
{

$.ajax({
    type: "POST",
    url: url,
    dataType: 'json',
    success: function (response) 
    {
      geojsonLayer = L.geoJson(response,{fillColor: color, weight: 1}).addTo(map);

      if(activo>=0) //Sólo hacen click y hover si es >0, si es <0 no es clicable
      {
        geojsonLayer.on('click',function()
        {
          if(activo>0)
          {
            //history.pushState(null, null, "http://localhost:8888/?idLugar="+idLugar);
            window.location="/citysens/?idLugar="+idLugar;
          }
          else
          {
            //No hay todavía para esta ciudad
            loadOverlayNoDisponible("cityNotReadyYet.html",idLugar,texto);
          }
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

      polygons[idLugar]=geojsonLayer;
      
    }
});  
}

function cargarMapa(idLugar)
{
  //Creamos el mapa
  window.polygons = [];
  $('.map-map').html('<div id="map"></div>');
  var map = L.map('map',
  {
    zoomControl: false,
    attributionControl: false,
  });
  
  map.dragging.disable();
  map.touchZoom.disable();
  map.doubleClickZoom.disable();
  map.scrollWheelZoom.disable();
  map.boxZoom.disable();
  map.keyboard.disable();
  
  var ggl = new L.Google();
  L.Google('roadmap');
  map.addLayer(ggl);
  //L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(map);
  window.map=map;
      
  //Iconos a medida
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
    //Area a mostrar con padding
    padding=0.025;  //2.5% por cada lado
    paddingX=padding*(parseFloat(response.xmax)-parseFloat(response.xmin));
    paddingY=padding*(parseFloat(response.ymax)-parseFloat(response.ymin));

    xmin=parseFloat(response.xmin)-paddingX;
    ymin=parseFloat(response.ymin)-paddingY;
    xmax=parseFloat(response.xmax)+paddingX;
    ymax=parseFloat(response.ymax)+paddingY;

    var southWest = L.latLng(ymin, xmin),
    northEast = L.latLng(ymax, xmax),
    originalBounds = L.latLngBounds(southWest, northEast);
    map.fitBounds(originalBounds);
    fittedBounds=map.getBounds();

    fittedXMin=fittedBounds.getWest();
    fittedXMax=fittedBounds.getEast();
    fittedYMin=fittedBounds.getSouth();
    fittedYMax=fittedBounds.getNorth();

    //Cálculo de Zoom para encajar el mapa
    originalLatSpan=ymax-ymin;
    originalLngSpan=xmax-xmin;
    fittedLatSpan=fittedYMax-fittedYMin;
    fittedLngSpan=fittedXMax-fittedXMin;

    zoomLat=fittedLatSpan/originalLatSpan;
    zoomLng=fittedLngSpan/originalLngSpan;

    if(zoomLat<zoomLng) //Hacemos zoom con el que menos necesite (para que el otro entre bien)
      zoom=zoomLat;
    else
      zoom=zoomLng;

    containerWidth=$("#map").width();
    containerHeight=$("#map").height();

    despLeft=-((containerWidth-(containerWidth/zoom))/2);
    despTop=-((containerHeight-(containerHeight/zoom))/2);

    $("#map").css("top",despTop);
    $("#map").css("left",despLeft);
    $("#map").css("zoom",zoom);

    //Breadcrumbs
    var breadcrumbs="";
    var first=true;
    $.each(response.breadcrumbs, function(i,lugar)
    {
      if(!first)
        breadcrumbs+=" > ";
      breadcrumbs+='<A HREF=\'?idLugar='+lugar[0]+'\'>'+lugar[1]+'</A>';
      first=false;
    });
    
    $(".map-breadcrumbs").html(breadcrumbs);
    window.ciudad=response.nombre;
    window.idLugar=idLugar;
    
    var nivelHijos=parseInt(response.nivel,10)+1;

    //Cargamos los polígonos hijos
    $.getJSON("getChildAreas.php", 
    {
        dataType: 'json',
        nivel:nivelHijos,
        lugarOriginal:idLugar,
    })
    .done(function(data) 
    {
      window.poligonos = [];
      $.each(data, function(i,datos)
      {
        window.poligonos[datos.id]=datos.nombre;

        addPolygonToMap(datos.id,"shp/geoJSON/"+nivelHijos+"/"+datos.id+".geojson",datos.nombre,'#ffaaaa',datos.activo);
        if(response.nivel>7)
        {
          if(typeof window.cantidadPorLugar[datos.id] === 'undefined')
            cantidad='0';
          else
            cantidad=window.cantidadPorLugar[datos.id];
          new L.Marker([datos.ycentroid,datos.xcentroid], 
          {
            icon: new L.NumberedDivIcon({number: cantidad})
          }).addTo(map);
        }
      });
      
      //Cargamos los eventos
      window.markers = [];
      $.each(window.listado.grupos, function(nombreSuperGrupo,datosSuperGrupo)
      {
        $.each(datosSuperGrupo, function(grupo,filas)
        {
          $.each(filas.filas,function(i,datos)
          {
            var marker=new L.Marker([datos.y,datos.x], 
            {
              icon: new L.TargetIcon()
            }).setOpacity(0).setZIndexOffset(100).addTo(map);
            marker.dragging.disable();
            

            markers[datos.id]=marker;
          });
        });
      });

    });
    
    
    //Aquí cargamos los colindantes
    var nivelColindantes=parseInt(response.nivel,10);
    if(nivelColindantes==9)
    {
      //Apaño para distritos
      addPolygonToMap(idLugar,"shp/geoJSON/9/"+idLugar+".geojson","ABCDE",'#ff33aa',-1);
      if(typeof window.cantidadPorLugar[idLugar] === 'undefined')
        cantidad='0';
      else
        cantidad=window.cantidadPorLugar[idLugar];
      new L.Marker([response.ycentroid,response.xcentroid], 
      {
        icon: new L.NumberedDivIcon({number: cantidad})
      }).addTo(map);

      nivelColindantes=9;
      $.getJSON("getLugaresColindantes.php", 
      {
          dataType: 'json',
          tipo:nivelColindantes,
          xmin:fittedXMin,
          xmax:fittedXMax,
          ymin:fittedYMin,
          ymax:fittedYMax,
          lugarOriginal:idLugar,
      })
      .done(function(data) 
      {
        $.each(data, function(i,datos)
        {
          if(datos.id!=response.idPadre)  //No mostramos el padre
            if(datos.idPadre==response.idPadre) //Sólo mostramos a los hijos de su padre (es decir, a los hermanos)
              addPolygonToMap(datos.id,"shp/geoJSON/9/"+datos.id+".geojson",datos.nombre,'#ffaaaa',datos.activo);
        });
      });
      nivelColindantes=8; //Limitamos a nivel 8
    }

    //Todos los colindantes
    $.getJSON("getLugaresColindantes.php", 
    {
        dataType: 'json',
        tipo:nivelColindantes,
        xmin:fittedXMin,
        xmax:fittedXMax,
        ymin:fittedYMin,
        ymax:fittedYMax,
        lugarOriginal:idLugar,
    })
    .done(function(data) 
    {
      $.each(data, function(i,datos)
      {
        if(datos.id!=response.idPadre)  //No mostramos el padre
          addPolygonToMap(datos.id,"shp/geoJSON/"+nivelColindantes+"/"+datos.id+".geojson",datos.nombre,'#aaaaff',datos.activo);
      });
    });
    
  });

  
}

function irACoordenadas(coordinates,zoom)
{
  window.map.panTo(coordinates);
}
