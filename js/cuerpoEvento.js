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
	$(".detalle-cabecera").text(data.titulo);
	$(".detalle-cuerpo-texto").html(data.texto);
	$(".detalle-izq").fadeIn(1000);
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
