$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}


$.getJSON('getDatos.php', 
{
      id: $.urlParam('idEvento'),
})
.done(function(data) 
{
    //console.log(data);
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




});


$("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",250);
$("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",250);
$(".subcabecera-pestania-izq").hide();
$(".subcabecera-pestania-dch").hide();

$("#descripcion").click(function ()
{
    $('#detalle-cuerpo').animate({
        scrollTop: $("#detalle-cuerpo-texto").offset().top-177
    }, 1000, 'easeInOutQuint');
});

$("#contacto").click(function ()
{
    $('#detalle-cuerpo').animate({
        scrollTop: $("#informacion-cuerpo-contacto").offset().top-177
    }, 1000, 'easeInOutQuint');
});
