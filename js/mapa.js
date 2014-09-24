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
      geojsonLayer.on('click',function()
      {
        if(activo==="1")
        {
          //history.pushState(null, null, "http://localhost:8888/citysens/?idLugar="+idLugar);
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
    //map.setZoom(11.5);
    //Cargamos las cosas relativas a la ciudad: Filtrado eventos, breadcrumbs, etc...
    //España > Madrid > <?=$datosLugar["nombre"];?>

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
    if(nivelColindantes>8)nivelColindantes=8; //Limitamos a nivel 8
    $.getJSON("getLugaresColindantes.php", 
    {
        dataType: 'json',
        tipo:nivelColindantes,
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
