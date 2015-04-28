$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results?results[1]:null;
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

function addPolygonToMap(idLugar,navType,url,texto,color,activo,style)
{
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        success: function (response) {
            function style(feature) {
                return {
                fillColor: color,
                weight: 1,       
                //color: 'white', //color de la linea
                dashArray: '',
                fillOpacity: 0.3,
                };
            }        
        geojsonLayer = L.geoJson(response,{fillColor: color, weight: 1, style: style}).addTo(map);

        if(activo>=0) //Sólo hacen clic y hover si es >0, si es <0 no es clicable
        {
            geojsonLayer.on('click',function()
            {
                if(activo>0)
                {
                    //history.pushState(null, null, "http://localhost:8888/?idLugar="+idLugar);
                    target="?idLugar="+idLugar;
                    if (navType==1)
                        target+="&navType=1"
                    window.location=target;
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
            var layer = e.target;
            layer.setStyle({
                weight: 1,              
                fillColor: '#98FB98',            
             });
            });
        
            geojsonLayer.on('mouseout', function(e) 
            {
              $(".map-footer").html(window.nombre);  
              var layer = e.target;
            layer.resetStyle(e.target);  //layer.resetStyle(); 
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
  $('.map-map').html('<div id="circle-button"></div><div id="upbutton"></div><div id="map"></div>');
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
      iconUrl: 'css/icons/mira.png',
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

  navType=$.urlParam('navType');
  $.getJSON("getMapData.php", 
  {
    dataType: 'json',
    idLugar: idLugar,
    navType: navType,
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
// Not needed after changing css properties zoom for transform:scale
//    containerWidth=$("#map").width();
//    containerHeight=$("#map").height();
//    despLeft=-((containerWidth-(containerWidth/zoom))/2);
//    despTop=-((containerHeight-(containerHeight/zoom))/2);
//
//
//  $("#map").css("top",despTop);
//  $("#map").css("left",despLeft);
//  $("#map").css("zoom",zoom);
    $("#map").css("transform","scale("+zoom+")");
    $("#map").css("-ms-transform","scale("+zoom+")");
    $("#map").css("-moz-transform","scale("+zoom+")");
    $("#map").css("-webkit-transform","scale("+zoom+")");
    //Breadcrumbs
    var breadcrumbs="";
    var lastAncestor="", lastAncestorName=""; //Debe guardar el último ancestro para el botón de ir a nivel superior
    var last= response.breadcrumbs.length-1;
    $.each(response.breadcrumbs, function(i,lugar)   
    {
      if (i) // not first
          breadcrumbs+=" > ";

      if (i<last) { //not last
        //  breadcrumbs+='<A HREF=\'?idLugar='+lugar[0]+'\' > '+lugar[1]+'</A>';
          breadcrumbs+='<A HREF=\'?idLugar='+lugar[0]+'\'><abbr title=\''+lugar[2]+'\'>'+lugar[1]+'</abbr></A>';
          lastAncestor=lugar[0];
          lastAncestorName=lugar[2];
      }
      else{
          breadcrumbs+='<div id=\'hijos\'><strong>'+lugar[1]+'</strong><ul id=\'listabreadcrumbs\'></ul></div>';         
          $(".map-footer").html(lugar[1]);
         }      
    });

    $(".map-breadcrumbs").html(breadcrumbs);

    // in case it is not the top level, UP arrow displayed  
    if (lastAncestor!="")
    {
        var htmlUpButton ='<A HREF=\'?idLugar='+lastAncestor+'\' > <span class="fa-stack fa-2x id="upButton"><i class="fa fa-arrow-up fa-stack-1x fa-inverse" id="flechaarriba"></i><i class="fa fa-circle-thin fa-stack-2x" id="aro"></i><i class="fa fa-circle fa-stack-2x" id="circulo"></i></span></A>';
        $("#upbutton").html(htmlUpButton);
        $("#upbutton").on( "mouseover", function(e) 
        {
          $(".map-footer").html("Subir a "+lastAncestorName);           
        });
        $("#upbutton").on('mouseout', function(e) 
        {
          $(".map-footer").html("&nbsp;");
        });
    };

    window.nombre=response.nombre;
    window.idTerritorio=idLugar;
    
    idTerritorioMostrado=response.id;
    var nivelMostrado=parseInt(response.nivel,10);
    nivelHijos=nivelMostrado+1;
    nivelTios=nivelMostrado-1; // It could be adjusted if there is some level that is not considered significant (like districts, regions, etc.)
   
    if (nivelMostrado > 7) // If the territory is of level city or lower, there are counters, and a switcher is needed
    {
        if (window.listado.grupos) 
        {
            var htmlshowpointers = '<button><i class="fa fa-toggle-off"></i></button>';
            $("#circle-button").html(htmlshowpointers);

            $("#circle-button button").click(function () {
                $(this).find('i').toggleClass('fa-toggle-on fa-toggle-off');
                $(".leaflet-div-icon").fadeToggle("slow", "linear");
            });
            $("#circle-button").on("mouseover", function (e)
            {
                $(".map-footer").html("mostrar / ocultar eventos");
            });
            $("#circle-button").on('mouseout', function (e)
            {
                $(".map-footer").html("&nbsp;");
            });
        }
    }
    
    // If the territory has no child, the territory is shown
    if (response.idDescendiente==0) 
    {
        addPolygonToMap(idTerritorioMostrado,0,"shp/geoJSON/"+response.nivel+"/"+idTerritorioMostrado+".geojson","ABCDE",'#ffaaaa',-1);

        if  (nivelMostrado>7) // If the territory is of level city or lower, counter is included
        {
            if (typeof window.cantidadPorLugar[idLugar] === 'undefined')
                cantidad = '0';
            else
                cantidad = window.cantidadPorLugar[idLugar];
            new L.Marker([response.ycentroid, response.xcentroid],
                {
                icon: new L.NumberedDivIcon({number: cantidad})
                }).addTo(map);
        }
    }
    else if (navType==1) // Territory has child, but we are on special navigation. We allow to clic on it to zoom into it.
    {
                addPolygonToMap(idTerritorioMostrado,0,"shp/geoJSON/"+response.nivel+"/"+idTerritorioMostrado+".geojson",response.nombre,'#ffaaaa',response.activo);

        if  (nivelMostrado>7) // If the territory is of level city or lower, counter is included
        {
            if (typeof window.cantidadPorLugar[idLugar] === 'undefined')
                cantidad = '0';
            else
                cantidad = window.cantidadPorLugar[idLugar];
            new L.Marker([response.ycentroid, response.xcentroid],
                {
                icon: new L.NumberedDivIcon({number: cantidad})
                }).addTo(map);
        }
    } 
    else { //Cargamos los polígonos hijos
        $.getJSON("getChildAreas.php", 
            {
            dataType: 'json',
            nivel:nivelHijos,
            lugarOriginal:idTerritorioMostrado,
            })
        .done(function(data) 
            {
            window.poligonos = [];
            breadcrumbs_dropdown="";
            $.each(data, function(i,datos)
                {
                window.poligonos[datos.id]=datos.nombre;
                // For level city and neighborhood, the special navigation (extra parameter in URL) is activated
                if(nivelHijos==10 || nivelHijos==8)
                    addPolygonToMap(datos.id,1,"shp/geoJSON/"+nivelHijos+"/"+datos.id+".geojson",datos.nombre,'#ffaaaa',datos.activo);
                else
                    addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelHijos+"/"+datos.id+".geojson",datos.nombre,'#ffaaaa',datos.activo);
                
                if(response.nivel>7) {
                    if(typeof window.cantidadPorLugar[datos.id] === 'undefined')
                        cantidad='0';
                    else
                        cantidad=window.cantidadPorLugar[datos.id];
                    new L.Marker([datos.ycentroid,datos.xcentroid], 
                        {
                        icon: new L.NumberedDivIcon({number: cantidad})
                        }).addTo(map);
                    }
                breadcrumbs_dropdown+='<li><A HREF=\'?idLugar='+datos.id+'\'>'+datos.nombre+'</A></li>';
                });
                $("#listabreadcrumbs").html(breadcrumbs_dropdown);
                $("#hijos").hover(function() {
			$('#listabreadcrumbs').fadeIn(); //muestro mediante id	
		 },
		function(){
			$('#listabreadcrumbs').fadeOut(); //oculto mediante id		
		});
                
            });
    }

    if (navType!=1)
      {
        // Show the brothers 
        $.getJSON("getLugaresColindantes.php",
            {
            dataType: 'json',
            tipo:nivelMostrado,
            xmin:fittedXMin,
            xmax:fittedXMax,
            ymin:fittedYMin,
            ymax:fittedYMax,
            lugarOriginal:idTerritorioMostrado,
            })
            .done(function(data) 
                {
                $.each(data, function(i,datos)
                    {
                    if (datos.idPadre==response.idPadre) //Sólo mostramos a los hijos de su padre (es decir, a los hermanos)
                        addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelMostrado+"/"+datos.id+".geojson",datos.nombre,'#aaaaff',datos.activo);
                    });
                });

        //Show the "uncles"
        $.getJSON("getLugaresColindantes.php", 
                {
                dataType: 'json',
                tipo:nivelTios,
                xmin:fittedXMin,
                xmax:fittedXMax,
                ymin:fittedYMin,
                ymax:fittedYMax,
                lugarOriginal:response.idPadre,
                })
                .done(function(data) 
                    {
                    $.each(data, function(i,datos)
                        {
                            addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelTios+"/"+datos.id+".geojson",datos.nombre,'#5353cf',datos.activo);
                        }); 
                    });
    }
    else 
    {
        // For level 10 (neighbourhood), and 8 (city) there is a special behaviour. We show all, brothers, cousins, etc.
        $.getJSON("getLugaresColindantes.php",
            {
            dataType: 'json',
            tipo:nivelMostrado,
            xmin:fittedXMin,
            xmax:fittedXMax,
            ymin:fittedYMin,
            ymax:fittedYMax,
            lugarOriginal:idTerritorioMostrado,
            })
            .done(function(data) 
                {
                $.each(data, function(i,datos)
                    {
                        addPolygonToMap(datos.id,1,"shp/geoJSON/"+nivelMostrado+"/"+datos.id+".geojson",datos.nombre,'#aaaaff',datos.activo);
                    });
                });
    }
        
        //Cargamos los eventos
        window.markers = [];
        //TODO: esto genera excepción cuando listado es vacío. Convendría hacer chequeo de si es vacío antes de lanzarlo.
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

    
}

function irACoordenadas(coordinates,zoom)
{
  window.map.panTo(coordinates);
}


