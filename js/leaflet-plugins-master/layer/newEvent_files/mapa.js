

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
        //history.pushState(null, null, "http://localhost:8888/?idLugar="+idLugar);
        window.location="/?idLugar="+idLugar;
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
  var map = L.map('map',{
            zoomControl: false,
            attributionControl: false,
        }).setView([40.47,-3.45], 11);

  /*map.on('click', function() 
  {
    alert('Has hecho click en el mapa');
  });
*/
  
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
  

  //L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png').addTo(map);
      

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
      iconUrl: '/icons/mira.png',
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

      console.log(xmin+"-"+xmax+"-"+ymin+"-"+ymax);
      console.log(paddingX+"-"+paddingY);


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
      



      $(".agenda-primera-linea").html("Mostrando EVENTOS en <strong>"+response.nombre+"</strong> las proximas semanas, que satisfacen los siguientes filtros de búsqueda:");

      //Aquí cargaríamos los distritos
      /*
      addPolygonToMap("Distrito I","shp/geoJSON/9/00501.geojson","Distrito I",'#ffaaaa',true);
      addPolygonToMap("Distrito II","shp/geoJSON/9/00502.geojson","Distrito II",'#ffaaaa',true);
      addPolygonToMap("Distrito III","shp/geoJSON/9/00503.geojson","Distrito III",'#ffaaaa',true);
      addPolygonToMap("Distrito IV","shp/geoJSON/9/00504.geojson","Distrito IV",'#ffaaaa',true);
      addPolygonToMap("Distrito V","shp/geoJSON/9/00505.geojson","Distrito V",'#ffaaaa',true);
      */

      var nivelHijos=parseInt(response.nivel,10)+1;


      $.getJSON("getChildAreas.php", 
      {
          dataType: 'json',
          nivel:nivelHijos,
          lugarOriginal:idLugar,
      })
      .done(function(data) 
      {
        $.each(data, function(i,datos)
        {
          addPolygonToMap(datos[0],"shp/geoJSON/"+nivelHijos+"/"+datos[0]+".geojson",datos[1],'#ffaaaa',true);
          if(response.nivel>7)
          {
            new L.Marker([datos[3],datos[2]], 
            {
              icon: new L.NumberedDivIcon({number: datos[5]})
            }).addTo(map).on('click',function()
            {
              //history.pushState(null, null, "http://localhost:8888/?idLugar="+idLugar);
              window.location="/?idLugar="+datos[0];
              //alert('Esto cargaría la página de '+texto);
            }).on('mouseover', function(e) 
            {
              $(".map-footer").html("Ir a "+datos[1]);
            }).on('mouseout', function(e) 
            {
              $(".map-footer").html("&nbsp;");
            });
          }

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
          marker.on('click',function()
          {
            //history.pushState(null, null, "http://localhost:8888/?idLugar="+idLugar);
            window.location="/?idLugar="+datos.idDistritoPadre;
            //alert('Esto cargaría la página de '+texto);
          }).on('mouseover', function(e) 
          {
            $(".map-footer").html("Ir a "+datos.idDistritoPadre);
          }).on('mouseout', function(e) 
          {
            $(".map-footer").html("&nbsp;");
          });


          markers[datos.idEvento]=marker;
          //.bindPopup("<b>"+datos.titulo+"</b><br />"+datos.texto);//.openPopup();
        });
      });

      //http://localhost:8888/getEventosCoordenadas.php?xmin=-3.64643&ymin=40.37454&xmax=-3.10192&ymax=40.60744

      
      //L.marker([40.470,-3.350]).addTo(map)
      //    .bindPopup("<b>Esto es un evento</b><br />Soy un evento");//.openPopup();
      

      //Aquí cargamos los colindantes
      $.getJSON("getLugaresColindantes.php", 
      {
          dataType: 'json',
          tipo:response.nivel,
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
          addPolygonToMap(datos[0],"shp/geoJSON/"+response.nivel+"/"+datos[0]+".geojson",datos[1],'#aaaaff',true);
          
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
