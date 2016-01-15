$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.search);
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

function addPolygonToMap(idTerritorio,alrededores,url,nombre,color,activo)
{
    $.ajax({
        type: "POST",
        url: url,
        dataType: 'json',
        success: function (response) 
        {
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

            if (activo!=-1)
            {
                geojsonLayer.on('click',function (e)
                {              
                    irATerritorio(activo,idTerritorio,alrededores,nombre) // llamada a cargar el mapa 
                }); 
                geojsonLayer.on('mouseover', function(e) 
                {
                    $(".map-footer").html("Ir a "+nombre);
                    var layer = e.target;
                    layer.setStyle({
                        weight: 1,              
                        fillColor: '#98FB98',            
                    });      
                });

                geojsonLayer.on('mouseout', function(e) 
                {
                    $(".map-footer").html(window.conf.nombreAmbito);  
                    var layer = e.target;
                    layer.resetStyle(e.target);  //layer.resetStyle(); 
                });        
            }       
            polygons[idTerritorio]=geojsonLayer;
        }
    });
}  


function cargarMapa(idTerritorio,alrededores)//alrededores [0,1]
{
  //Creamos el mapa
  window.polygons = [];
  //$('.map-map').html('<div id="upbutton"></div><div id="map"></div>'); //Borramos mapa previo si existía
  $('.map-map').html('<div id="circle-button"></div><div id="upbutton"></div><div id="map"></div>'); //Borramos mapa previo si existía
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
      var img = this._createImg(this.options['iconUrl']);
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
  
// icono no usado, estamos usando el default
// L.TargetIcon = L.Icon.extend(
//  {
//    options: {
//      iconUrl: 'css/icons/leafletimages/marker-icon.png',
//      number: '',
//      shadowUrl: null,
//      iconSize:     (25, 41),
//      // iconSize: new L.Point(38, 37),
//      //iconAnchor: new L.Point(29, 29),
//      iconAnchor:   (12, 41),
//      //popupAnchor: new L.Point(0, -29),
//      popupAnchor:  (-3, -76),
//      
//      className: 'leaflet-target-icon'
//    },
//
//    createIcon: function () {
//      var div = document.createElement('div');
//      var img = this._createImg(this.options['iconUrl']);
//      /*var numdiv = document.createElement('div');
//      numdiv.setAttribute ( "class", "number" );
//      numdiv.innerHTML = this.options['number'] || '';*/
//      div.appendChild( img );
//      //div.appendChild( numdiv );
//      this._setIconStyles(div, 'icon');
//      return div;
//    },
//   
//    //you could change this to add a shadow like in the normal marker if you really wanted
//    createShadow: function () {
//      return null;
//    }
//  });

  $.getJSON("getMapData.php", 
  {
    dataType: 'json',
    idTerritorio: idTerritorio,
    alrededores: alrededores,
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
    
    // Define to use in footer and other places
    window.conf.idTerritorio=idTerritorio;
    
      //Breadcrumbs
    var breadcrumbs="";
    var lastAncestor="", lastAncestorName=""; //Debe guardar el último ancestro para el botón de ir a nivel superior
    var last= response.breadcrumbs.length-1;//TODO ver porque genera un error en jquery por undefined
    $.each(response.breadcrumbs, function(i,lugar)   
    {
       // $(".map-breadcrumbs > a:nth-child(2)")on.click(irATerritorio(activo,idTerritorio,texto,alrededores));
      if (i) // not first
          breadcrumbs+=" > ";

      if (i<last) { //not last
        //  breadcrumbs+='<A HREF=\'?idTerritorio='+lugar[0]+'\' > '+lugar[1]+'</A>';
          //breadcrumbs+='<A HREF=\'?idTerritorio='+lugar[0]+'\'><abbr title=\''+lugar[2]+'\'>'+lugar[1]+'</abbr></A>';
          breadcrumbs+='<span onclick="irATerritorio(1,'+lugar[0]+',0,\''+lugar[2]+'\')"><abbr title=\''+lugar[2]+'\'>'+lugar[1]+'</abbr></span>';
          lastAncestor=lugar[0];
          lastAncestorName=lugar[2];
      }
      else{
          breadcrumbs+='<div id=\'hijos\'><strong>'+lugar[1]+'</strong><ul id=\'listabreadcrumbs\'></ul></div>';
            $(".map-footer").html(window.conf.nombreAmbito);
         }      
    });

    $(".map-breadcrumbs").html(breadcrumbs);

 
    // in case it is not the top level, UP arrow displayed  
    if (lastAncestor!="")
    {
        var htmlUpButton ='<span onclick="irATerritorio(1,'+lastAncestor+',0)"> <span class="fa-stack fa-2x id="upButton"><i class="fa fa-arrow-up fa-stack-1x fa-inverse" id="flechaarriba"></i><i class="fa fa-circle-thin fa-stack-2x" id="aro"></i><i class="fa fa-circle fa-stack-2x" id="circulo"></i></span></span>';
        $("#upbutton").html(htmlUpButton);
        $("#upbutton").on( "mouseover", function(e) 
        {
          $(".map-footer").html("Subir a "+lastAncestorName);           
        });
        $("#upbutton").on('mouseout', function(e) 
        {
          $(".map-footer").html(window.conf.nombreAmbito);
        });
    };

    
    window.conf.idTerritorioMostrado=response.id;
    var nivelMostrado=parseInt(response.nivel,10);
    nivelHijos=nivelMostrado+1;
    nivelTios=nivelMostrado-1; // It could be adjusted if there is some level that is not considered significant (like districts, regions, etc.)
   
    // DISPLAY OF THE INNER OF TERRITORY SELECTED
    if (alrededores!=0) // We are on "and surroundings" navigation. We always allow to click on it to zoom into it.
    {
        addPolygonToMap(conf.idTerritorioMostrado,0,"shp/geoJSON/"+response.nivel+"/"+conf.idTerritorioMostrado+".geojson",response.nombre,'#ffaaaa',response.activo);

        if  (nivelMostrado>6) // If the territory is of level province or lower, counter is included and the switcher shown
        {
            if (typeof window.cantidadPorLugar[idTerritorio] === 'undefined')
                cantidad = '0';
            else
                cantidad = window.cantidadPorLugar[idTerritorio];
            new L.Marker([response.ycentroid, response.xcentroid],
                {
                icon: new L.NumberedDivIcon({number: cantidad})
                }).addTo(map);
        }
    }
    // Normal navigation: the territories within selected territory are displayed
    else if (response.idDescendiente==0) 
    {
        // If the territory has no child, the territory is shown
        if (response.activo==0)
            addPolygonToMap(conf.idTerritorioMostrado,0,"shp/geoJSON/"+response.nivel+"/"+conf.idTerritorioMostrado+".geojson",response.nombre,'#ffaaaa',0);
        else //está activo pero no tiene hijos, mandamos código especial -1
            addPolygonToMap(conf.idTerritorioMostrado,0,"shp/geoJSON/"+response.nivel+"/"+conf.idTerritorioMostrado+".geojson",response.nombre,'#ffaaaa',-1);
        
        if  (nivelMostrado>5) // If the territory is of level province or lower, counter is included and the switcher shown
        {
            if (typeof window.cantidadPorLugar[idTerritorio] === 'undefined')
                cantidad = '0';
            else
                cantidad = window.cantidadPorLugar[idTerritorio];
            new L.Marker([response.ycentroid, response.xcentroid],
                {
                icon: new L.NumberedDivIcon({number: cantidad})
                }).addTo(map);
            
            incluirPalanca();
        }
    }
    else { //Cargamos los polígonos hijos
        $.getJSON("getChildAreas.php", 
            {
            dataType: 'json',
            nivel:nivelHijos,
            lugarOriginal:conf.idTerritorioMostrado,
            })
        .done(function(data) 
            {
            window.poligonos = [];
            breadcrumbs_dropdown="";
            $.each(data, function(i,datos) {
                window.poligonos[datos.id]=datos.nombre;
                // For level city and neighborhood, the special navigation is activated by including list of neighbour territories in a conf parameter)
//                if(nivelHijos==10 || nivelHijos==8)
//                {
//                    addPolygonToMap(datos.id,datos.vecinos,"shp/geoJSON/"+nivelHijos+"/"+datos.id+".geojson",datos.nombre+" y alrededores",'#ffaaaa',datos.activo);
//                    breadcrumbs_dropdown+='<li onclick="irATerritorio('+datos.activo+','+datos.id+',1,\''+datos.nombre+'\')">'+datos.nombre+'</li>';
//                }
//                else
//                {    
                    addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelHijos+"/"+datos.id+".geojson",datos.nombre,'#ffaaaa',datos.activo);
                    breadcrumbs_dropdown+='<li onclick="irATerritorio('+datos.activo+','+datos.id+',0,\''+datos.nombre+'\')">'+datos.nombre+'</li>';
//                }
                
                if(nivelMostrado>5) { // If the territory is of level province or lower, there are counters, and a switcher is needed
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
            if (nivelMostrado>5)   // We show the switcher to display amount of items per territory, for levels under province
                incluirPalanca();
 
            $("#listabreadcrumbs").html(breadcrumbs_dropdown);
            $("#hijos").hover(function() {
                    $('#listabreadcrumbs').fadeIn(); //muestro mediante id	
             },
            function(){
                    $('#listabreadcrumbs').fadeOut(); //oculto mediante id		
            });

        });
    }

    // DISPLAY OF SURROUNDING OF SELECTED TERRITORY
    if (alrededores!=0)
    {        // For navigations with neighbours, neighboring territories are shown in a different colour.
        $.getJSON("getTerritorios.php",
            {
            dataType: 'json',
            territorios:response["vecinos"],
            })
            .done(function(data) 
                {
                $.each(data, function(i,datos)
                    {
                        addPolygonToMap(datos.id,1,"shp/geoJSON/"+nivelMostrado+"/"+datos.id+".geojson",datos.nombre+" y alrededores",'#FFE4C5',datos.activo);
                        
                        if  (nivelMostrado>6) // If the territory is of level province or lower, counter is included
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
                    if  (nivelMostrado>6) // If the territory is of level province or lower, switcher is shown
                    {
                    // We show the switcher to display amount of items per territory
                        incluirPalanca();
                    }
                });
                
        $.getJSON("getTerritoriosColindantes.php",
            {
            dataType: 'json',
            tipo:nivelMostrado,
            xmin:fittedXMin,
            xmax:fittedXMax,
            ymin:fittedYMin,
            ymax:fittedYMax,
            territoriosExcluidos:response["vecinos"]+','+conf.idTerritorioMostrado,
            })
            .done(function(data) 
                {
                $.each(data, function(i,datos)
                    {
                        addPolygonToMap(datos.id,1,"shp/geoJSON/"+nivelMostrado+"/"+datos.id+".geojson",datos.nombre+" y alrededores",'#aaaaff',datos.activo);
                    });
                });
    }
    else 
    //  20151222 - Dedided this is not needed by now. Better to have consisten behaviour everywhere.
    //  This creates confusion, specially with big metropolies. To see beyond limits, we have "and surroundings" navigation.
    //        if (nivelMostrado!=8 && nivelMostrado!=10) // Navegación normal, no municipio o barrio (that do not show "uncles")
      {
        // Show the brothers 
        $.getJSON("getTerritoriosColindantes.php",
            {
            dataType: 'json',
            tipo:nivelMostrado,
            xmin:fittedXMin,
            xmax:fittedXMax,
            ymin:fittedYMin,
            ymax:fittedYMax,
            territoriosExcluidos:conf.idTerritorioMostrado,
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
        $.getJSON("getTerritoriosColindantes.php", 
                {
                dataType: 'json',
                tipo:nivelTios,
                xmin:fittedXMin,
                xmax:fittedXMax,
                ymin:fittedYMin,
                ymax:fittedYMax,
                territoriosExcluidos:response.idPadre,
                })
                .done(function(data) 
                    {
                    $.each(data, function(i,datos)
                        {

                            addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelTios+"/"+datos.id+".geojson",datos.nombre,'#5353cf',datos.activo);
                        }); 
                    });
    }
//    else     // 20151222 - Dedided it is not needed by now. Better to have consisten behaviour.
//    {        // For level 10 (neighbourhood) and 8 (city) with no special behaviour, vecinos are shown.
//        $.getJSON("getTerritoriosColindantes.php",
//            {
//            dataType: 'json',
//            tipo:nivelMostrado,
//            xmin:fittedXMin,
//            xmax:fittedXMax,
//            ymin:fittedYMin,
//            ymax:fittedYMax,
//            territoriosExcluidos:conf.idTerritorioMostrado,
//            })
//            .done(function(data) 
//                {
//                $.each(data, function(i,datos)
//                    {
//                        addPolygonToMap(datos.id,0,"shp/geoJSON/"+nivelMostrado+"/"+datos.id+".geojson",datos.nombre,'#aaaaff',datos.activo);
//                    });
//                incluirPalanca();
//                });
//    }
    
                    
      //Cargamos los eventos
        window.markers = [];
        //TODO: esto genera excepción cuando listado es vacío. Convendría hacer chequeo de si es vacío antes de lanzarlo.
        $.each(window.listado.grupos, function(nombreSuperGrupo,datosSuperGrupo)
            {
            $.each(datosSuperGrupo, function(grupo,filas)
                {
                $.each(filas.filas,function(i,datos)
                    {
                    var marker=new L.Marker([datos.y,datos.x]).setOpacity(0).setZIndexOffset(100).addTo(map);
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


function irATerritorio(activo,idTerritorio,alrededores,nombre)
{
   if(activo>0)//Sólo hacen clic y hover si es >0, si es <0 no es clicable
   {             
        window.conf.idTerritorio = idTerritorio;                          
        window.conf.alrededores=alrededores;
        if (!alrededores)
          $(".tagFiltro-action").remove();    
        removeAllTags();
        cargarDatos();       
    }
    else
    {
        //No hay todavía para esta ciudad
         loadOverlayNoDisponible("cityNotReadyYet.html",idTerritorio,nombre);
    }    
}

function incluirPalanca() {
        if (window.cantidadPorLugar) 
        {
            if (window.conf.palanca==0||window.conf.palanca==='undefined')
            {
                var htmlshowpointers = '<button><i class="fa fa-toggle-off"></i></button>';     
            }
            else 
            {
                var htmlshowpointers = '<button><i class="fa fa-toggle-on"></i></button>';
                $(".leaflet-div-icon").fadeIn("fast", "linear");            
            }
            $("#circle-button").html(htmlshowpointers);
            $("#circle-button button").click(function () { 
                    if (window.conf.palanca==0||window.conf.palanca==='undefined')
                    {
                        $(this).find('i').toggleClass('fa-toggle-on fa-toggle-off');
                        $(".leaflet-div-icon").fadeToggle("fast", "linear");
                        window.conf.palanca=1;
                    }
                    else
                    {
                        $(this).find('i').toggleClass('fa-toggle-off fa-toggle-on');
                        $(".leaflet-div-icon").fadeToggle("fast", "linear");
                        window.conf.palanca=0;
                    }
                }
            );
            $("#circle-button").on("mouseover", function (e)
            {
                $(".map-footer").html("mostrar / ocultar eventos");
            });
            $("#circle-button").on('mouseout', function (e)
            {
                $(".map-footer").html(window.conf.nombreAmbito);
            });
        }
    }