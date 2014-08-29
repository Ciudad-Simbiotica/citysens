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

$.getJSON('getDatos.php', 
{
      id: $.urlParam('idEvento'),
})
.done(function(data) 
{
    console.log(data);
	$(".detalle-cabecera").text(data.titulo);
	$(".detalle-cuerpo-texto").html(data.texto);
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
        var clone=$("#tagFiltroTemplate").clone();
        clone.hide();
        clone.find('.tagFiltro-texto').text(etiqueta);
        clone.find('.tagFiltro-imagen').removeClass('tagFiltro-imagen').addClass('tagFiltro-etiqueta-imagen');
        clone.appendTo(".detalle-tags");
        clone.show();
    });

    contactoScrollPosition=Math.round($("#informacion-cuerpo-contacto").offset().top-177);

    cargarMapa(data.direccion.lat,data.direccion.long);
    $(".detalle-mapa-cabecera-lugar").text("Evento en "+data.lugar);
    $(".detalle-mapa-pie-nombre").text(data.direccion.nombre);
    $(".detalle-mapa-pie-direccion").text(data.direccion.direccion);
    $(".detalle-mapa-cabecera-volver").click(function(){
        window.location="/citysens/?idLugar="+$.urlParam('idOrigen');
    });

    var date = new Date(data.fecha);
    var dateFin = new Date(data.fechaFin);
    var monthNames = [ "ENE", "FEB", "MAR", "ABR", "MAY", "JUN",
    "JUL", "AUG", "SEP", "OCT", "NOV", "DIC" ];


    $(".detalle-mapa-pie-calendario-top").html(monthNames[date.getMonth()]);
    $(".detalle-mapa-pie-calendario-bottom").html(date.getDate());
    $(".detalle-mapa-pie-hora-inicio").html(paddingZeros(date.getHours())+":"+paddingZeros(date.getMinutes()));
    if(data.fechaFin!=null)
      $(".detalle-mapa-pie-hora-final").html(paddingZeros(dateFin.getHours())+":"+paddingZeros(dateFin.getMinutes()));
    else
      $(".detalle-mapa-pie-hora-final").html('');

    $(".detalle-mapa-pie-tipo").addClass('detalle-mapa-pie-tipo-'+data.tipo);

    $(".detalle-mapa-pie").show();


});


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
    window.location="/citysens/?idLugar="+$.urlParam('idOrigen');
});


$(".cabecera-pestania-dch").click(function()
{
    window.location="/citysens/?idLugar="+$.urlParam('idOrigen')+'&category=ent';
});

