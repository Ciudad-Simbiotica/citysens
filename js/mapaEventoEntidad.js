$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}
function cargarMapa(lat,lon)
{
  
  //Creamos el mapa
  var map = L.map('detalle-mapa-mapa',
        {
            zoomControl: false,
            attributionControl: false,
        }).setView([lat,lon], 15);

  var marker = L.marker([lat,lon]).addTo(map);

  
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
  
}