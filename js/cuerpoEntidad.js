$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}

function paddingZeros(number)
{
  return ("0" + number).slice(-2);
}


$.fn.scrollTo = function( target, options, callback ){
  if(typeof options == 'function' && arguments.length == 2){ callback = options; options = target; }
  var settings = $.extend({
    scrollTarget  : target,
    offsetTop     : 50,
    duration      : 500,
    easing        : 'swing'
  }, options);
  return this.each(function(){
    var scrollPane = $(this);
    var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
    var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + scrollPane.scrollTop() - parseInt(settings.offsetTop);
    scrollPane.animate({scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function(){
      if (typeof callback == 'function') { callback.call(this); }
    });
  });
}

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

$.getJSON('getDatosEntidad.php', 
{
      id: $.urlParam('idEntidad'),
})
.done(function(data) 
{
  console.log(data);
  
	$(".detalle-cabecera").text(data.entidad);
	$(".detalle-cuerpo-texto").html(data.texto);

  if(data.url!=null)
    if((data.url.indexOf("http://") < 0) & (data.url.indexOf("https://") < 0))
      data.url="http://"+data.url;

  $(".informacion-cuerpo-contacto-url").attr("href", data.url);
  $(".informacion-cuerpo-contacto-url").html(data.url);
  $(".informacion-cuerpo-contacto-email").attr("href", "mailto:"+data.email);
  $(".informacion-cuerpo-contacto-email").html(data.email);

  if(data.tipo=="institucion") // checK: tipo or tipoEntidad?
    $(".detalle-tipo").css("background-image", "url(css/icons/icon_CitYsens.institucion.png)");
  else if(data.tipo=="organizacion")
    $(".detalle-tipo").css("background-image", "url(css/icons/icon_CitYsens.organizacion.png)");
  else if(data.tipo=="colectivo")
    $(".detalle-tipo").css("background-image", "url(css/icons/CitYsens.people.png)");

  $(".detalle-puntos-puntos").text(data.points);

  $(".detalle-tipo").show();  //Por ahora queda oculto


	$(".detalle-izq").fadeIn(1000);

    $.each(data.tematicas,function(id,tematica)
    {
        var clone=$("#tagFiltroTemplate").clone();
        clone.hide();
        clone.find('.tagFiltro-texto').text(tematica);
        clone.find('.tagFiltro-imagen').removeClass('tagFiltro-imagen').addClass('tagFiltro-tematica-imagen');
        clone.appendTo(".detalle-tags");
        clone.show();
    });

    $.each(data.etiquetas.split(','),function(id,etiqueta)
    {
      if(etiqueta!="")
      {
        var clone=$("#tagFiltroTemplate").clone();
        clone.hide();
        clone.find('.tagFiltro-texto').text(etiqueta);
        clone.find('.tagFiltro-imagen').removeClass('tagFiltro-imagen').addClass('tagFiltro-etiqueta-imagen');
        clone.appendTo(".detalle-tags");
        clone.show();
      }
    });

    contactoScrollPosition=Math.round($("#informacion-cuerpo-contacto").offset().top-177);
    
    cargarMapa(data.place.lat,data.place.lng,data.entidad+" @ "+data.place.nombre);
    $(".detalle-mapa-cabecera-lugar").text("Entidad en "+data.place.nombre);
    
    $(".detalle-mapa-pie-nombre").text(data.place.nombre);
    $(".detalle-mapa-pie-direccion").text(data.place.direccion);
    $(".detalle-mapa-cabecera-volver").click(function(){
        window.location="/?idTerritorio="+window.conf.idTerritorio;   //$.urlParam('idOrigen'); De donde es esto¿
    });
    
    /*

    $(".detalle-mapa-pie-calendario-top").html(monthNames[date.getMonth()]);
    $(".detalle-mapa-pie-calendario-bottom").html(date.getDate());
    $(".detalle-mapa-pie-hora-inicio").html(paddingZeros(date.getHours())+":"+paddingZeros(date.getMinutes()));
    if(data.fechaFin!=null)
      $(".detalle-mapa-pie-hora-final").html(paddingZeros(dateFin.getHours())+":"+paddingZeros(dateFin.getMinutes()));
    else
      $(".detalle-mapa-pie-hora-final").html('');

    $(".detalle-mapa-pie-tipo").addClass('detalle-mapa-pie-tipo-'+data.tipo);
    */
    $(".detalle-mapa-pie").show();


    //Sharing code
    url="http://www.citysens.net/?idEntidad="+data.idEntidad+"%26idOrigen="+window.conf.idTerritorio;
    mensaje="¡¡¡Esta entidad te puede interesar!!!";
    
    var tbx = document.getElementById("toolbox");

    tbx.innerHTML="";
    tbx.innerHTML += '<a class="addthis_button_email"></a>';
    tbx.innerHTML += '<a class="addthis_button_facebook"></a>';
    tbx.innerHTML += '<a class="addthis_button_twitter"></a>';
    tbx.innerHTML += '<a class="addthis_button_google_plusone" g:plusone:annotation="none" g:plusone:size="medium"></a>';
    tbx.innerHTML += '<a class="addthis_counter addthis_bubble_style"></a>';

    var addthis_config = {
          ui_language: "es" 
    } 
    var addthis_share = 
    { 
      url: url,
      title: data.entidad,
      description: mensaje,
      templates: 
      {
        twitter: data.entidad+" - "+url,
      },
      url_transforms : 
      {
        clean: true
      },
      
      // email_template: "citysens_evento",
      // email_vars: 
      // { 
      //   asunto: mensaje,
      //   fecha: data.fecha,
      //   lugar: data.lugar 
      // }   
      
    }

    addthis.toolbox("#toolbox",addthis_config,addthis_share);
    document.title = data.entidad;
});

$("head").append($("<link rel='stylesheet' type='text/css' href='css/cuerpoEntidad.css'>")); 

$("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",250);
$("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",250);
$(".subcabecera-pestania-izq").hide();
$(".subcabecera-pestania-dch").hide();

$("#descripcion").click(function ()
{
    $('#detalle-cuerpo').scrollTo(0, 1000, 'easeInOutQuint');
});

$("#contacto").click(function ()
{
    $('#detalle-cuerpo').scrollTo(contactoScrollPosition, 1000, 'easeInOutQuint');
});


$(".cabecera-pestania-izq").click(function()
{
    window.location="/?idTerritorio="+$.urlParam('idOrigen');
});


$(".cabecera-pestania-dch").click(function()
{
    window.location="/?idTerritorio="+$.urlParam('idOrigen')+'&category=ent';
});

$('#input-busqueda').attr('placeholder','Buscar en la entidad...');


$('#input-busqueda').keyup(function(event)
{
  if((event.which==13)||($('#input-busqueda').val()==""))
  {
   if($('#input-busqueda').val()!=window.conf.lastSearch)
   {
     window.conf.lastSearch=$('#input-busqueda').val();
     var page = $('#detalle-cuerpo-texto');
     var pageHtml = page.html().replace(/<span>/igm,"").replace(/<\/span>/igm,"");
     if($('#input-busqueda').val()!="")
     {
       var searchedText = $('#input-busqueda').val();
       var theRegEx = new RegExp("("+searchedText+")", "igm");    
       var newHtml = pageHtml.replace(theRegEx ,"<span>$1</span>");
     }
     else
       var newHtml = pageHtml;
     page.html(newHtml);

     if($('#input-busqueda').val()!="")
     {
       var firstOcurrencePosition=Math.round($("#detalle-cuerpo-texto").find("span")[0].offsetTop-90);
       $('#detalle-cuerpo').scrollTo(firstOcurrencePosition, 1000, 'easeInOutQuint');
     }
     else
       $('#detalle-cuerpo').scrollTo(0, 1000, 'easeInOutQuint');

     
   }
  }
});

$(".cabecera-propon").click(function()
{
  loadOverlay("newEvent.html");
});

