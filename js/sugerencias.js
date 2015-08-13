//Para sacar los parámetros de GET
$.urlParam = function (name) {
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.search);
    return results ? results[1] : null;
}

// Creates the filter: html tag, filter variable and asociates function to remove it.
function clickSuggestion(filter)
{
    var existe = false;
    $.each(conf.arrayFilters, function (key, value) {
        if (!existe)
            existe = ((value.id == filter.id) & (value.tipo == filter.tipo));
    });
    if (!existe)
    {
        $("#cabecera-suggest").empty();


        var imagen = "";
        switch (filter.tipo) {
            case "tematica":
                imagen = "css/icons/etiqueta30x30.png";
                break;
            case "organizacion":
                imagen = "css/icons/icon_CitYsens.organizacion.png";
                break;
            case "institucion":
                imagen = "css/icons/icon_CitYsens.institucion.png";
                break;
            case "colectivo":
                imagen = "css/icons/CitYsens.people.png";
                break;
            case "busqueda":
                imagen = "css/icons/lupa.png";
                break;
            case "lugar":
                imagen = "css/icons/lugar.png";
                break;
            case "lupa":
                imagen = "css/icons/lupa.png";
                break;
            case "tiempo":
                imagen = "css/icons/clock.png";
                break;
        }

        //console.log("Clic Sugerencia: " + imagen + "/" + filter.texto1 + "/" + filter.tipo);

        var clone = $("#tagFiltroTemplate").clone();
        clone.hide();
        clone.attr("id", filter.texto1);
        if (typeof filter.abrev !== 'undefined')
            filter.texto1 = filter.abrev;
        clone.find('.tagFiltro-texto').html(filter.texto1);
        clone.find('.tagFiltro-imagen').css('background-image', "url(" + imagen + ")");

        clone.find('.tagFiltro-x').click(function ()
        {
            //Borrado  del filtro
            conf.arrayFilters = jQuery.grep(conf.arrayFilters, function (value)
            {
                var coincide = ((value.texto == filter.texto1) & (value.tipo == filter.tipo) & (value.id == filter.id));
                return !coincide;
            });

            if (!window.listado.tipo)
                window.listado.tipo = "eventos";

            window.listado.orden = $("#select_ordenar").val();
            if (!window.listado.orden)
                window.listado.orden = "puntuacion";
            $(this).fadeOut("fast", function () {
                $(this).parent().remove();
            });
            cargarDatos();
        });

        //clone.find('.grupo-cabecera-cntr').html(center);
        //clone.find('.grupo-cabecera-dch').html(right);

        var tipoEtiqueta = filter.tipo;
        if ((tipoEtiqueta == 'institucion') | (tipoEtiqueta == 'colectivo') | (tipoEtiqueta == 'organizacion'))
            tipoEtiqueta = 'entidad';

        clone.addClass("tagFiltro-" + tipoEtiqueta);

        clone.appendTo(".agenda-filtros-" + tipoEtiqueta);
        clone.fadeIn("fast");

        var sugerencia =
                {
                    "texto": filter.texto1,
                    "tipo": filter.tipo,
                    "id": filter.id
                };
        if (filter.tipo == 'tiempo') {
            sugerencia["start"] = filter.start;
            sugerencia["end"] = filter.end;
        }

        conf.arrayFilters.push(sugerencia);
        $("#input-busqueda").val('');
        //cargarDatos(window.conf.clase,$("#select_ordenar").val());
        window.listado.orden = $("#select_ordenar").val();
        cargarDatos();
    }

}

function prevSuggestion()
{
    if (window.conf.selectedSuggestion > 1)
    {
        $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion - 1).removeClass("cabecera-suggest-fila-selected");
        $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion - 2).addClass("cabecera-suggest-fila-selected");
        window.conf.selectedSuggestion--;
    }
}

function nextSuggestion()
{
    if ($("#cabecera-suggest").find(".cabecera-suggest-fila").length > window.conf.selectedSuggestion)
    {
        $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion - 1).removeClass("cabecera-suggest-fila-selected");
        $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion).addClass("cabecera-suggest-fila-selected");
        window.conf.selectedSuggestion++;
    }
}


function suggestBusqueda(texto)
{
    //TODO: Chapuza - Usa una variable global, cambiarlo
    if (window.previousSuggestText == texto)
    {
        return;
    }

    window.previousSuggestText = texto;
    if (texto == "")
    {
        $("#cabecera-suggest").empty();
        return;
    }

    // TODO: Later it uses the condition     if(window.clase=='eventos') for somthing simmilar.
    //       Probably it would be better to think of some alternative method to indicate what section we are in.
    //       Specially considering that there will be other categories in addition to events and entities.
    if ($(".cabecera-pestania-izq").hasClass("cabecera-pestania-seleccionada"))
        entidades = "";
    else
        entidades = "true";


    //Que cargue las sugerencias usando AJAX
    var getAgenda = "getSuggestions.php?";
    $.getJSON(getAgenda,
            {
                query: texto,
                idTerritorio: window.conf.idTerritorio,
                alrededores: window.conf.alrededores,
                date: "any",
                format: "json",
                entidades: entidades
            })
            .done(function (data)
            {
                $("#cabecera-suggest").empty();

                //ToDo: Que esto vaya ello solo sin necesidad de recolocarlo
                //Recolocamos la cabecera, el cuerpo y los botones según los filtros que haya
                //$("#cabecera-suggest").css('margin-top', $("#input-busqueda_tagsinput").position().top+$('#input-busqueda_tagsinput').outerHeight(true)+4);
                //$(".cuerpo").css('margin-top', 20+$('#input-busqueda_tagsinput').outerHeight(true));
                //$(".map").css('top', 20+$('#input-busqueda_tagsinput').outerHeight(true));
                //$(".informacion").css('top', 295+$('#input-busqueda_tagsinput').outerHeight(true));
                //$(".botonesSuperiores").css('top', 20+$('#input-busqueda_tagsinput').outerHeight(true));

                //$(".scroll-curtain-gradient").css('top', 22+$('.botonesSuperiores').position().top);
                //$(".scroll-curtain").css('height', $(".scroll-curtain-gradient").position().top-43);


                window.conf.selectedSuggestion = 0;

                //Añadimos el tooltip
                if (window.listado.tipo == 'eventos')
                    $("#cabecera-suggest").append("<div class='cabecera-suggest-tooltip'>Buscar eventos que tengan que ver con...</div>");
                else
                    $("#cabecera-suggest").append("<div class='cabecera-suggest-tooltip'>Buscar entidades que tengan que ver con...</div>");

                //Que esto lo clone de una fila por defecto      
                //Añadimos la búsqueda tal cual
                $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'><strong>" + texto + "</strong></div></div>");
                $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-busqueda");
                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id", "cabecera-suggest-fila-0");
                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function ()
                {
                    var filtroBusqueda =
                            clickSuggestion("css/icons/lupa.png", texto, 'busqueda', 0, "");
                });
                var num_fila = 1;
                $.each(data.suggestions, function (key, value)
                        //Order of suggestions is: time, thematic, territory, entities, goTo
                        {
                            if (value.tipo == "IrA")
                            {
                                if ($("#input-busqueda").val().length > 3)
                                    $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a " + value.texto1.replace(new RegExp("(" + value.textoBuscado + ")", 'gi'), "<b>$1</b>") + "</div></div>");
                                else
                                    $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a " + value.texto1 + "</div></div>");
                                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").addClass("cabecera-suggest-fila-IrA");
                                $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-IrA");
                                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id", "cabecera-suggest-fila-" + num_fila);
                                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function ()
                                {
                                    if (value.activo == 1)
                                    {
                                        window.conf.idTerritorio = value.id;
                                        //   cargarMapa(window.conf.idTerritorio);               
                                        // else          
                                        window.listado.orden = $("#select_ordenar").val();
                                        removeAllTags();
                                        cargarDatos();
                                        // clickSuggestion(icono,value.texto1,value.tipo,value.id);
                                    }
                                    else
                                    {
                                        $("#overlay").addClass("overlayPeque");
                                        $(".darkOverlay").fadeIn("fast");
                                        $("#overlay").load("cityNotReadyYet.html", function ()
                                        {
                                            $('#overlay').html($('#overlay').html().replace(/{CIUDAD}/g, value.texto1)); //need rev. sometimes load after show an change text
                                            $('#input-email-idLugar').val(value.id);
                                            $('#input-email-nombreCiudad').val(value.texto1);
                                        });
                                    }
                                    //clickSuggestion("css/icons/gps.png",texto,"IrA",0); //Añadir value.id, texto buscado
                                });
                            }
                            else
                            {
                                var div_texto1;
                                var div_texto2="";
                                
                                //Creamos la sugerencia
                                if ($("#input-busqueda").val().length > 2)
                                    div_texto1="<div class='cabecera-suggest-texto1'>" + value.texto1.replace(new RegExp("(" + value.textoBuscado + ")", 'gi'), "<b>$1</b>") + "</div>";
                                else
                                    div_texto1="<div class='cabecera-suggest-texto1'>" + value.texto1 + "</div>";
                                
                                if (typeof value.texto2 == 'undefined' | value.texto2 == "")
                                    $("#cabecera-suggest").find(".cabecera-suggest-texto1:last").addClass("cabecera-suggest-texto1-sinTexto2");
                                else 
                                    div_texto2="<div class='cabecera-suggest-texto2'>" + value.texto2 + "</div>";
                                
                                $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div>"+div_texto1+div_texto2+"</div>");
                                $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-" + value.tipo);
                                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id", "cabecera-suggest-fila-" + num_fila);
                                if (typeof value.texto2 !== 'undefined' | value.texto2 == "")
                                {
                                    $("#cabecera-suggest").find(".cabecera-suggest-texto1:last").addClass("cabecera-suggest-texto1-sinTexto2");
                                }

                                $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function ()
                                {
                                    clickSuggestion(value);
                                });
                            }
                            num_fila++;

                            //¿Animarlo?
                            //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
                        });
                // by default select cabecera-suggest-fila:first
                $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");
                window.conf.selectedSuggestion = 1;
                if ($(".input-busqueda").val() == '') {
                    $(".input-busqueda").val('');
                    $("#cabecera-suggest").empty();
                }
            });




}
