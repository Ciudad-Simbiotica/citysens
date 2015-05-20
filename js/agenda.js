//Para sacar los parámetros de GET
$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.search);
    return results?results[1]:null;
}
//-------------------------------------------------------------------
//Generación de información necesaria para navegación en página
//--------------------------------------------------------------------
//En estado de Pruebas
//window.miBusqueda ="";
//window.miBuqueda.idOrigen=idTerritorioorigen
/*
var miBusqueda = {
    
        idOrigen: $.urlParam('idTerritorio'),
        Organismo:'Evento',
    //    Filtros:typeof(window.arrayTags)!=="undefined"?window.arrayTags[0].texto:"sinfiltro"
        //idOrigen: window.idTerritorio// actualmente esta en window.idTerritorio   
        };
    
        
        */
/* 
---------------------------------------------------------------------------------------------
------------------------------------Inicializando variable global-----------------------------------
---------------------------------------------------------------------------------------------

*/
//TODO Revisar donde se crea window.con e introducirlo aqui
  window.conf={};
  window.conf.idTerritorio=$.urlParam("idTerritorio");
  window.conf.alrededores=0;
  
  window.listado={};
  if(!window.listado.tipo)
     window.listado.tipo="eventos";
  if(!window.listado.orden)
     window.listado.orden="fecha"; 


/* 
---------------------------------------------------------------------------------------------
------------------------------------Creado de grupos/filas-----------------------------------
---------------------------------------------------------------------------------------------
*/

function createSuperGroup(nombreSuperGrupo)
{
  var clone=$("#supergrupo-template").clone();
  clone.hide();
  clone.removeClass("supergrupo-template");
  clone.addClass("supergrupo");
  clone.attr('id',"sg_"+nombreSuperGrupo.replace(/\W/g,""));
  clone.find('.supergrupo-cuerpo').empty();
  clone.find('.supergrupo-cabecera').html(nombreSuperGrupo);
  
  clone.appendTo(".agenda");

  //clone.show();
}


function createGroup(grupo,left,center,right,totalFilas,nombreSuperGrupo)
{
	var clone=$("#grupo-template").clone();
  clone.hide();
  clone.removeClass("grupo-template");
  clone.addClass("grupo");
	clone.attr('id',nombreSuperGrupo.replace(/\W/g,"")+"-"+grupo.replace(/\W/g,""));
	clone.find('.grupo-filas').empty();

	clone.find('.grupo-cabecera-izq').html(left);
	clone.find('.grupo-cabecera-cntr').html(center);
	clone.find('.grupo-cabecera-dch').html(right);


  clone.find('.grupo-pie').html("");

  if(!(typeof totalFilas==='undefined'))
  {
    $.each(totalFilas,function(tipo,cantidad)
    {
      var cadenaTipo=tipo;
      if(cantidad>1)
      {
        if((tipo=="organizacion")||(tipo=="institucion"))
          cadenaTipo+="es";
        else
          cadenaTipo+="s";
      }

      clone.find('.grupo-pie').append("<div class='grupo-pie-"+tipo+"'><div class='grupo-pie-"+tipo+"-imagen'></div><div class='grupo-pie-"+tipo+"-texto'>+"+cantidad+" "+cadenaTipo+"</div></div>");
      clone.find(".grupo-pie-"+tipo).hide();
      clone.find(".grupo-pie-"+tipo).click(function()
      {
        $("#"+nombreSuperGrupo.replace(/\W/g,"")+"-"+grupo.replace(/\W/g,"")).find(".grupo-fila-"+tipo).slideDown("fast");
        $(this).slideUp("fast");
      });
    });
  }
  
  $("#sg_"+nombreSuperGrupo.replace(/\W/g,"")).find(".supergrupo-cuerpo").append(clone);

  clone.show();
}

function createLine(grupo,datos,animated,nombreSuperGrupo)
{
	var clone=$("#grupo-fila-template-"+datos.clase).clone();
	clone.hide();
    
  $.each(datos,function(clase,contenido)
  {
   		if(clase=="id")
      {
        clone.attr("id",contenido); //Ponemos ID a la fila
        clone.find(".grupo-elemento-handup").click(function() //Añadimos la función de click al botón
        {
          clickHandUp(contenido);
        });
        clone.click(function() //Añadimos la función de click a la fila
        {
          clickFila(contenido);
        });
        clone.mouseenter(function() //Añadimos la función de click a la fila
        {
          enterFila(contenido);
        })
        .mouseleave(function() //Añadimos la función de click a la fila
        {
          leaveFila(contenido);
        });
      }
      else if(clase=="tipo")
      {
        //Cambiamos las imágenes según el tipo
        //ToDo: Sustituir el IMG por DIV y cambiar el background por CSS
        if(contenido=="convocatoria")
          clone.find(".imagen-tipo").attr("src", "css/icons/Event-Unique.64.png");
        else if(contenido=="recurrente")
          clone.find(".imagen-tipo").attr("src", "css/icons/Event-Recurring.64.png");
        else if(contenido=="institucion")
          clone.find(".imagen-tipo").attr("src", "css/icons/icon_CitYsens.institucion.png");
        else if(contenido=="organizacion")
          clone.find(".imagen-tipo").attr("src", "css/icons/icon_CitYsens.organizacion.png");
        else if(contenido=="colectivo")
          clone.find(".imagen-tipo").attr("src", "css/icons/CitYsens.People.png");
      }
      /*
      else if(clase=="participante")
      {
        //Si no es participante escondemos el icono
        if(contenido==0)
          clone.find(".grupo-elemento-participante").hide();
      }
      */
      else if(clase=="temperatura")
      {
        //Cambiamos el termometro
        clone.find(".imagen-temp").attr("src", "css/icons/termometro_"+contenido+".png");
      }
      else
      {
        //El resto se sustituye el contenido por lo que se pasa
  			clone.find('.grupo-elemento-'+clase).html(contenido);
      }
	});

  clone.removeClass("grupo-fila-template");
  clone.addClass("grupo-fila-"+datos.tipo);

	clone.appendTo("#"+nombreSuperGrupo.replace(/\W/g,"")+"-"+grupo.replace(/\W/g,"")+">.grupo-filas");

	if(animated>0)
		clone.slideDown("fast", function() {});
	else
		clone.show();
};

/* 
---------------------------------------------------------------------------------------------
------------------------------------Clicks/Hovers en filas-----------------------------------
---------------------------------------------------------------------------------------------
*/

function clickHandUp(id)
{
  alert("Click 'Me gusta': "+id);
  event.stopPropagation();
}

function clickFila(id)
{
  //alert("Click Fila: "+id);
  $("[class^=grupo-fila-]").removeClass("grupo-fila-selected");
  $("#"+id).addClass("grupo-fila-selected");
  //$(".informacion-cabecera").html("Cargando contenido: "+id);

  if($(".cabecera-pestania-izq").hasClass("cabecera-pestania-seleccionada"))
    cargarContenido(id);
  else if($(".cabecera-pestania-dch").hasClass("cabecera-pestania-seleccionada"))
    cargarContenidoEntidad(id);

  $.each(markers, function( index, value ) 
  {
    if(markers[index]!=null)
      markers[index].setOpacity(0);
  });
  if(markers[id]!=null)
    markers[id].setOpacity(0.9);
}

function enterFila(id)
{
    if(!($("#"+id).hasClass("grupo-fila-selected")))
       if(markers[id]!=null)   
          markers[id].setOpacity(0.6);  
}

function leaveFila(id)
{
  if(!($("#"+id).hasClass("grupo-fila-selected")))
    if(markers[id]!=null)
      markers[id].setOpacity(0);  
}

function switchFilas(clase,tipo)
{
  console.log("Switch "+clase+" "+tipo);
  $("#switch-"+clase).toggleClass("switch-filas-off");
  if($("#switch-"+clase).hasClass("switch-filas-off"))
  {
    $(".grupo-fila-"+tipo).slideUp("fast", function() {});
    $(".grupo-pie-"+tipo).slideDown("fast", function() {});
  }
  else
  {
    $(".grupo-fila-"+tipo).slideDown("fast", function() {});
    $(".grupo-pie-"+tipo).slideUp("fast", function() {});    
  }  
}

function comprobarPlegadoFilas()
{
    if($("#switch-puntuales").hasClass("switch-filas-off"))
      plegarFilas("puntuales","convocatoria",0);
    if($("#switch-recurrentes").hasClass("switch-filas-off"))
      plegarFilas("recurrentes","recurrente",0);

    if($("#switch-instituciones").hasClass("switch-filas-off"))
      plegarFilas("instituciones","institucion",0);
    if($("#switch-organizaciones").hasClass("switch-filas-off"))
      plegarFilas("organizaciones","organizacion",0);
    if($("#switch-colectivos").hasClass("switch-filas-off"))
      plegarFilas("colectivos","colectivo",0);

}

function plegarFilas(clase,tipo,tiempo)
{
  $(".grupo-fila-"+tipo).slideUp(tiempo, function() {});
  $(".grupo-pie-"+tipo).slideDown(tiempo, function() {});
}

/* 
---------------------------------------------------------------------------------------------
-----------------------------------Cargado contenido agenda----------------------------------
---------------------------------------------------------------------------------------------
*/

function paddingZeros(number)
{
  return ("0" + number).slice(-2);
}

function cargarContenido(id)
{
  $(".informacion").slideUp("fast");
  $.getJSON('getDatos.php', 
  {
   id: id, // appears as $_GET['id'] @ ur backend side
  })
  .done(function(data) 
  {
    console.log(data);

    var date = new Date(data.fecha);
    var dateFin = new Date(data.fechaFin);
    var monthNames = [ "ENE", "FEB", "MAR", "ABR", "MAY", "JUN",
    "JUL", "AUG", "SEP", "OCT", "NOV", "DIC" ];


    $(".informacion-cabecera-izq-calendario-top").html(monthNames[date.getMonth()]);
    $(".informacion-cabecera-izq-calendario-bottom").html(date.getDate());
    $(".informacion-cabecera-izq-horas-top").html(paddingZeros(date.getHours())+":"+paddingZeros(date.getMinutes()));
    if(data.fechaFin!=null)
      $(".informacion-cabecera-izq-horas-bottom").html(paddingZeros(dateFin.getHours())+":"+paddingZeros(dateFin.getMinutes()));
    else
      $(".informacion-cabecera-izq-horas-bottom").html('');


    $(".informacion-cabecera-dch-titulo-top").html(data.titulo);
    $(".informacion-cabecera-dch-titulo-bottom").html(data.lugar);


    $(".informacion-cuerpo-tematicas-listado").html('');
    $.each(data.tematicas, function(i, object) 
    {
      if($(".informacion-cuerpo-tematicas-listado").html()!="")
        $(".informacion-cuerpo-tematicas-listado").append(', ');      
      $(".informacion-cuerpo-tematicas-listado").append(object);      
    });

    $(".informacion-cuerpo-etiquetas-listado").html(data.etiquetas);

    if(data.url!=null)
      if((data.url.indexOf("http://") < 0) & (data.url.indexOf("https://") < 0))
        data.url="http://"+data.url;

    $(".informacion-cuerpo-contacto-url").attr("href", data.url);
    $(".informacion-cuerpo-contacto-url").html(data.url);
    $(".informacion-cuerpo-contacto-email").attr("href", "mailto:"+data.email);
    $(".informacion-cuerpo-contacto-email").html(data.email);

    $(".informacion-cuerpo-contacto-email").append(" ");

    $(".informacion-cuerpo-texto").html(data.texto);

    $(".informacion-cabecera").click(function()
    {
      window.location="?idEvento="+data.idEvento+"&idOrigen="+window.conf.idTerritorio;
    });

    url="http://www.citysens.net/?idEvento="+data.idEvento+"%26idOrigen="+window.conf.idTerritorio;
    mensaje="¡¡¡Este evento te puede interesar!!!";
    
    var tbx = document.getElementById("toolbox");

    tbx.innerHTML="";
    tbx.innerHTML += '<a class="addthis_button_email"></a>';
    tbx.innerHTML += '<a class="addthis_button_facebook"></a>';
    tbx.innerHTML += '<a class="addthis_button_twitter"></a>';
    tbx.innerHTML += '<a class="addthis_button_google_plusone" g:plusone:annotation="none" g:plusone:size="medium"></a>';

    var addthis_config = {
          ui_language: "es" 
    } 
    var addthis_share = 
    { 
      url: url,
      title: data.titulo,
      description: mensaje,
      templates: 
      {
        twitter: data.titulo+" - "+url,
      }
    }

    addthis.toolbox("#toolbox",addthis_config,addthis_share);

    /*
    $(".share-mail").attr("href", "mailto:?subject="+data.titulo+"&body="+mensaje+"%0D%0A%0D%0A"+url);
    $(".share-facebook").attr("href", "https://www.facebook.com/sharer/sharer.php?u="+url+"&t="+data.titulo+"&s="+mensaje+"");
    $(".share-twitter").attr("href", "https://twitter.com/share?url="+url+"&text="+data.titulo+" - ");
    $(".share-googleplus").attr("href", "https://plus.google.com/share?url="+url);
    $(".share-linkedin").attr("href", "http://www.linkedin.com/shareArticle?mini=true&url="+url+"&title="+data.titulo+"&summary="+mensaje+"&source=http://www.citysens.net");
    //$(".share-link").attr("href", "#");
    */

    //Ocultamos lo que corresponde a entidades y mostramos los de eventos
    $(".informacion-cuerpo-contacto-elemento-evento").show();
    $(".informacion-cuerpo-contacto-elemento-entidad").hide();


    if(data.tipo=='recurrente')
    {
      switch(parseInt(data.repeatsAfter))
      {
        case 1:
          textoRepeticion='Se repite cada día';
          break;
        case 7:
          textoRepeticion='Se repite cada semana';
          break;
        case 14:
          textoRepeticion='Se repite cada dos semanas';
          break;
        case 21:
          textoRepeticion='Se repite cada tres semanas';
          break;
        default:
          textoRepeticion='Se repite cada '+data.repeatsAfter+' días';
          break;         
      }
      $(".informacion-cabecera-abajo").text(textoRepeticion);
      $(".informacion-cabecera").height(70);
      $(".informacion-cabecera-abajo").show();
    }
    else
    {
      $(".informacion-cabecera").height(50);
      $(".informacion-cabecera-abajo").hide();
    }

    $(".informacion").slideDown("fast");
  });
}

function cargarContenidoEntidad(id)
{
  $(".informacion").slideUp("fast");
  $.getJSON('getDatosEntidad.php', 
  {
   id: id, // appears as $_GET['id'] @ ur backend side
  })
  .done(function(data) 
  {
    console.log(data);

    $(".informacion-cabecera-dch-titulo-top").html(data.entidad);
    $(".informacion-cabecera-dch-titulo-bottom").html(data.direccion);


    $(".informacion-cuerpo-tematicas-listado").html('');
    $.each(data.tematicas, function(i, object) 
    {
      if($(".informacion-cuerpo-tematicas-listado").html()!="")
        $(".informacion-cuerpo-tematicas-listado").append(', ');      
      $(".informacion-cuerpo-tematicas-listado").append(object);      
    });

    $(".informacion-cuerpo-etiquetas-listado").html(data.etiquetas);
  
    $(".informacion-cuerpo-contacto-url")
      .attr("href", data.url)
      .html(data.url);
    $(".informacion-cuerpo-contacto-email")
      .attr("href", "mailto:"+data.email)
      .html(data.email);

    $(".informacion-cuerpo-contacto-twitter")
      .attr("href", "mailto:"+data.twitter)
      .html(data.twitter);

    $(".informacion-cuerpo-contacto-facebook")
      .attr("href", "mailto:"+data.facebook)
      .html(data.facebook);

    $(".informacion-cuerpo-contacto-facebook").append(" ");


    $(".informacion-cuerpo-descBreve").html(data.descBreve);
    $(".informacion-cuerpo-texto").html(data.texto);

    if(data.tipoEntidad=="institucion")
      $(".informacion-cabecera-izq-entidad-izq").css('background-image', "url(css/icons/icon_CitYsens.institucion.png)");
    else if(data.tipoEntidad=="organizacion")
      $(".informacion-cabecera-izq-entidad-izq").css('background-image', "url(css/icons/icon_CitYsens.organizacion.png)");
    else if(data.tipoEntidad=="colectivo")
      $(".informacion-cabecera-izq-entidad-izq").css('background-image', "url(css/icons/CitYsens.People.png)");


    $(".informacion-cabecera").click(function()
    {
      window.location="/?idEntidad="+data.idEntidad+"&idOrigen="+window.conf.idTerritorio;
    });

    url="http://www.citysens.net/?idEntidad="+data.idEntidad+"%26idOrigen="+window.conf.idTerritorio;
    mensaje="¡¡¡Esta asociación te puede interesar!!!";
    
    var tbx = document.getElementById("toolbox");

    tbx.innerHTML="";
    tbx.innerHTML += '<a class="addthis_button_email"></a>';
    tbx.innerHTML += '<a class="addthis_button_facebook"></a>';
    tbx.innerHTML += '<a class="addthis_button_twitter"></a>';
    tbx.innerHTML += '<a class="addthis_button_google_plusone" g:plusone:annotation="none" g:plusone:size="medium"></a>';

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
        twitter: data.titulo+" - "+url,
      }
    }

    addthis.toolbox("#toolbox",addthis_config,addthis_share);
    

    //Ocultamos lo que corresponde a entidades y mostramos los de eventos
    $(".informacion-cuerpo-contacto-elemento-evento").hide();
    $(".informacion-cuerpo-contacto-elemento-entidad").show();
    $(".informacion-cabecera").height(50);

    $(".informacion").slideDown("fast");
  });
}

function removeAllTags()
{
  $(".tagFiltro-busqueda").remove();
  $(".tagFiltro-tematica").remove();
  $(".tagFiltro-lugar").remove();
  $(".tagFiltro-entidad").remove();
  conf.arrayTags=[];

}

//function cargarDatos(clase, orden)
function cargarDatos()
{
    window.listado.orden = $("#select_ordenar").val();
    $(".agenda-segunda-linea").fadeOut("fast");
    $(".informacion").slideUp("fast");
    //orden = typeof orden !== 'undefined' ? orden : 'fecha';
    $(".cabecera-logo a").attr("href", "?idTerritorio=" + conf.idTerritorio);

    console.log(window.listado.tipo + " " + window.listado.orden);
    //window.conf.clase=clase;
    //[{"texto":"Alcal&aacute; De Henares","tipo":"lugar","id":"4284"}] 
    var hayUnLugar = false;
    var arrayTagsQuery = conf.arrayTags.slice();

    // If there is no territory filter, then the original territoryId is inserted as a filter.
    // TODO 2015.05.04: This is not optimal. It seems better that the function receives the territoryID which is applied in case there is none.
    // but for the moment being... we leave it as it is.
    $.each(arrayTagsQuery, function (i, object)
    {
        if (object.tipo == "lugar")
        {
            hayUnLugar = true;
        }
    });
    if (!hayUnLugar)
    {
        var sugerencia =
                {
                    "texto": "",
                    "tipo": "lugar",
                    "id": window.conf.idTerritorio
                };
        arrayTagsQuery.push(sugerencia);
    }


    var filtros = JSON.stringify(arrayTagsQuery);
    console.log(filtros);


    $(".supergrupo").attr('id', "").remove();  //Para que no se inserten en esta les quitamos el ID
    $(".agenda-segunda-linea").hide();
    /*$(".supergrupo").fadeOut("1000",function()
     {
     $(this).remove();
     });  */

    var getAgenda = "getAgendaXML.php?";
    $.getJSON(getAgenda,
            {
                clase: window.listado.tipo,
                date: "any",
                filtros: filtros,
                //idTerritorioOriginal: $.urlParam('idTerritorio'),
                idTerritorioOriginal: window.conf.idTerritorio,
                format: "json",
                orden: window.listado.orden
            })
            .done(function (data)
            {
                //Esperamos a que se hayan borrado los grupos (por si acaba antes) antes de clonar
                //console.log(arrayTagsQuery);

                //console.log(data);

                window.listado = data;

                primeraLinea = "";
                conFiltros = ":";
                if (window.conf.arrayTags.length > 0)
                    conFiltros = " que satisfacen los siguientes filtros de búsqueda:";
                $("#cabecera-suggest").empty();
                $(".input-busqueda").val('');

                switch (window.listado.tipo)
                {
                    case "eventos":
                        if (window.conf.alrededores == 1)
                            primeraLinea = "Mostrando EVENTOS en <strong>" + data.lugarOriginal.nombre + " y alrededores</strong> en las próximas semanas" + conFiltros;
                        else
                            primeraLinea = "Mostrando EVENTOS en <strong>" + data.lugarOriginal.nombre + "</strong> en las próximas semanas" + conFiltros;

                        if (jQuery.isEmptyObject(data.grupos)) {
                            primeraLinea += "<br><br><strong>Ningún evento.</strong>";
                        }
                        $(".input-busqueda").attr('placeholder', 'Filtrar eventos...');
                        break;
                    case "organizaciones":
                        if (window.conf.alrededores == 1)
                            primeraLinea = "Mostrando ENTIDADES en <strong>" + data.lugarOriginal.nombre + " y alrededores</strong> en las próximas semanas" + conFiltros;
                        else
                            primeraLinea = "Mostrando ENTIDADES en <strong>" + data.lugarOriginal.nombre + "</strong> en las próximas semanas" + conFiltros;
                        if (jQuery.isEmptyObject(data.grupos)) {
                            primeraLinea += "<br><br><strong>Ninguna entidad.</strong>";
                            $(".div-avisos").hide();
                        }
                        $(".input-busqueda").attr('placeholder', 'Filtrar entidades...');
                        break;
                    case "procesos":
                        if (window.conf.alrededores == 1)
                            primeraLinea = "Mostrando INICIATIVAS en <strong>" + data.lugarOriginal.nombre + " y alrededores</strong> en las próximas semanas" + conFiltros;
                        else
                            primeraLinea = "Mostrando INICIATIVAS en <strong>" + data.lugarOriginal.nombre + "</strong> en las próximas semanas" + conFiltros;
                        if (jQuery.isEmptyObject(data.grupos))
                        {
                            primeraLinea += "<br><br><strong>Ninguna iniciativa.</strong>";
                        }
                        $(".input-busqueda").attr('placeholder', 'Filtrar iniciativas...');
                        break;
                    case "noticias":
                        break;
                }
                $(".agenda-primera-linea").html(primeraLinea);


                //console.log(data.grupos);

                window.cantidadPorLugar = [];

                if (!(typeof data.grupos === 'undefined'))
                {
                    $.each(data.grupos, function (nombreSuperGrupo, datosSuperGrupo)
                    {
                        createSuperGroup(nombreSuperGrupo);
                        $.each(datosSuperGrupo, function (grupo, filas)
                        {
                            createGroup(grupo, filas.cabeceraIzq, filas.cabeceraCntr, filas.cabeceraDch, filas.totalFilas, nombreSuperGrupo);
                            $.each(filas.filas, function (i, item)
                            {
                                createLine(grupo, item, 0, nombreSuperGrupo);
                                //console.log(item);
                                if (typeof window.cantidadPorLugar[item.idDistritoPadre] === 'undefined')
                                    window.cantidadPorLugar[item.idDistritoPadre] = 0;
                                window.cantidadPorLugar[item.idDistritoPadre]++;
                            });
                        });
                    });
                }

                if (data.isFollowing)
                {
                    $("#boton-avisos").val("Dejar de recibir avisos");
                    window.conf.isFollowing = true;
                }
                else
                {
                    $("#boton-avisos").val("Recibir avisos");
                    window.conf.isFollowing = false;
                }
                $(".supergrupo").fadeIn(500);
                // Show sort-by and register only in case ther was results
                if (!jQuery.isEmptyObject(data.grupos))
                    $(".agenda-segunda-linea").fadeIn(500);
                comprobarPlegadoFilas();
                cargarMapa(window.conf.idTerritorio, window.conf.alrededores);
                scroll(0,0); //Nos posicionamos en la parte superior de la web.
            });
}

/* 
 ---------------------------------------------------------------------------------------------
 ---------------------------------------Overlay Loading---------------------------------------
 ---------------------------------------------------------------------------------------------
 */

function loadOverlay(url, peque)
{
    if (peque)
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

/* 
---------------------------------------------------------------------------------------------
-----------------------------------Nuevo evento, subscribir----------------------------------
---------------------------------------------------------------------------------------------
*/


function newEvent()
{
  loadOverlay("newEvent.html");
}

function isValidEmailAddress(emailAddress) 
{
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function subscribe()
{
  if(isLogged())
  {
    var hayUnLugar=false;
    var arrayTagsQuery=conf.arrayTags.slice();

    $.each(arrayTagsQuery, function(i, object) 
    {
      if(object.tipo=="lugar")
      {
        hayUnLugar=true;
      }
    });

    if(!hayUnLugar)
    {
      var sugerencia = 
      {
        "texto": "", 
        "tipo": "lugar",
        "id": window.conf.idTerritorio
      };
      arrayTagsQuery.push(sugerencia);
    }

    var params=JSON.stringify(arrayTagsQuery);
    if(window.isFollowing)
    {
      $.post( "changeSubscriptionStatus.php", { params: params, clase: window.conf.clase, action: 'unsubscribe' } )
      .done(function(data){
        console.log(data);
      });
      $("#boton-avisos").val("Recibir avisos");
      notificarError('Ya no recibirás avisos sobre esta búsqueda.');                
      window.conf.isFollowing=false;
    }
    else
    {

      $.post( "changeSubscriptionStatus.php", { params: params, clase: window.clase,  action: 'subscribe' } )
      .done(function(data){
        console.log(data);
      });
      $("#boton-avisos").val("Dejar de recibir avisos");
      window.conf.isFollowing=true;
      notificarExito('Acabas de apuntarte para recibir avisos sobre esta búsqueda.');
    }
  }
  else
  {
    if(isValidEmailAddress($("#email-avisos").val()))
    {
      $("#overlay").addClass("overlayPeque");
      $(".darkOverlay").fadeIn("fast");
      $("#overlay").load("register.html", function() 
      {
        $("#input-email").val($("#email-avisos").val());
      });
    }
    else
    {
      alert('Introduce un correo electrónico válido.');
    }
  }
}

$("#email-avisos").bind('keydown',function(event)
{
  if(event.which==13) 
  {
    subscribe();
  }
});


$(".cabecera-propon").click(function()
{
  newEvent();
});

$("#boton-avisos").click(function()
{
  subscribe();
});

function isLogged()
{
  return $('#logged').val()!="";
}

/* 
---------------------------------------------------------------------------------------------
---------------------------------BINDS switch filas/tipos------------------------------------
---------------------------------------------------------------------------------------------
*/


$("#switch-puntuales").click(function()
{
  switchFilas("puntuales","convocatoria");
});

$("#switch-recurrentes").click(function()
{
  switchFilas("recurrentes","recurrente");
});

$("#switch-instituciones").click(function()
{
  switchFilas("instituciones","institucion");
});

$("#switch-organizaciones").click(function()
{
  switchFilas("organizaciones","organizacion");
});

$("#switch-colectivos").click(function()
{
  switchFilas("colectivos","colectivo");
});


$(".cabecera-pestania-izq").click(function()
{
  console.log("Mostrando Eventos");
  removeAllTags();

  $("#cabecera-pestania-izq").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-puntuales").removeClass("switch-filas-off");
  $("#switch-recurrentes").removeClass("switch-filas-off");
  $(".subcabecera-pestania-dch").slideUp("fast",function() 
  {
    // Animation complete.
    $(".subcabecera-pestania-izq").slideDown("fast");
  });


  var newOptions = 
  { 
    "Fecha": "fecha",
    "Temática": "tematica",
    "Lugar": "lugar",
    "Popularidad": "popularidad"
  };

  var el = $("#select_ordenar");
  el.empty(); // remove old options
  $.each(newOptions, function(key, value) 
  {
    el.append($("<option></option>")
       .attr("value", value).text(key));
  });

  $("#select_ordenar").val('fecha');
  $('#select_ordenar').prop('onchange',null).attr('onchange','').unbind('change');
  
  $('#select_ordenar').on('change', function() 
  {
    //cargarDatos('eventos', $(this).val()); 
   window.listado.tipo="eventos";
  //if(!window.listado.orden)
    window.listado.orden=$(this).val();
    cargarDatos();
   
  });

  //cargarDatos("eventos",'fecha');
    window.listado.tipo="eventos";
    window.listado.orden="fecha";
  cargarDatos();

});

$(".cabecera-pestania-ctr").click(function()
{
  console.log("Mostrando Iniciativas");
  removeAllTags();
  $("#cabecera-pestania-ctr").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);

  $(".subcabecera-pestania-izq").slideUp("fast");
  $(".subcabecera-pestania-dch").slideUp("fast");
  //cargarDatos("procesos"); 
  window.listado.orden="procesos";
  cargarDatos();
 

});

$(".cabecera-pestania-dch").click(function()
{
  console.log("Mostrando Entidades");
  removeAllTags();
  $("#cabecera-pestania-dch").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-noticias").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-instituciones").removeClass("switch-filas-off");
  $("#switch-organizaciones").removeClass("switch-filas-off");
  $("#switch-colectivos").removeClass("switch-filas-off");

  $(".subcabecera-pestania-izq").slideUp("fast",function() 
  {
    // Animation complete.
    $(".subcabecera-pestania-dch").slideDown("fast");
  });


  var newOptions = 
  { 
    "Puntuación": "puntuacion",
    "Temática": "tematica",
    "Lugar": "lugar"
  };

  var el = $("#select_ordenar");
  el.empty(); // remove old options
  $.each(newOptions, function(key, value) 
  {
    el.append($("<option></option>")
       .attr("value", value).text(key));
  });

  $("#select_ordenar").val('puntuacion');
  $('#select_ordenar').prop('onchange',null).attr('onchange','').unbind('change');
  $('#select_ordenar').on('change', function() 
  {
    //cargarDatos('organizaciones', $(this).val());
    window.listado.tipo="organizaciones";
    window.listado.orden=$(this).val();
  });
//cargarDatos("organizaciones",'puntuacion');
window.listado.orden="puntuacion";
window.listado.tipo="organizaciones";
cargarDatos();



});

//No habilitado 
$(".cabecera-pestania-noticias").click(function()
{
  console.log("Mostrando Noticias");
  removeAllTags();

  $("#cabecera-pestania-noticias").addClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-izq").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-ctr").removeClass("cabecera-pestania-seleccionada",150);
  $("#cabecera-pestania-dch").removeClass("cabecera-pestania-seleccionada",150);
  $("#switch-puntuales").removeClass("switch-filas-off");
  $("#switch-recurrentes").removeClass("switch-filas-off");
  $(".subcabecera-pestania-dch").slideUp("fast");
  $(".subcabecera-pestania-izq").slideUp("fast");
  //cargarDatos("eventos");
  window.listado.tipo="eventos";
  cargarDatos();

});

$(".cabecera-derecha-inicia").click(function()
{
  console.log("Iniciando sesión");
  loadOverlay("login.html",true);
});

$(".cabecera-derecha-registrate").click(function()
{
  loadOverlay("register.html",true);
});

$(".cabecera-derecha-micitysens").click(function()
{
  console.log("MiCitysens");
});

$(".cabecera-derecha-logout").click(function()
{
  $("#logout_form").submit();
});




/* 
---------------------------------------------------------------------------------------------
------------------------------------BIND KEYUP SUGERENCIAS-----------------------------------
---------------------------------------------------------------------------------------------
*/


$('#input-busqueda').bind('keyup',function(event)
{
  switch (event.which) 
  {
    case 13:  //Intro
      if(window.conf.selectedSuggestion==0)
        window.conf.selectedSuggestion=1;
      var fila="#cabecera-suggest-fila-"+(window.conf.selectedSuggestion-1);
      $(fila).trigger("click");
      /*
      var icono=$(fila).find(".cabecera-suggest-icono").css('background-image');
      icono=icono.substring(4,icono.length-1);
      var texto=$(fila).find(".cabecera-suggest-texto1").text();
      clickSuggestion(icono,texto,'busqueda',0);
      */
        return;
        break;
    case 27:  //Escape
        $("#cabecera-suggest").empty();
        $(this).val("");
        window.conf.selectedSuggestion=0;
        break;
    case 38:  //Up
        prevSuggestion();
        //return;
        break;
    case 40:  //Down
        nextSuggestion();
        //return;
        break;
    default:
        suggestBusqueda($(this).val());
               
  }
});

$('.cabecera-lupa').click(function() 
{
  if(window.conf.selectedSuggestion==0)
        window.conf.selectedSuggestion=1;
  var fila="#cabecera-suggest-fila-"+(window.conf.selectedSuggestion-1);
  $(fila).trigger("click");  
});

$("body").mouseup(function (e)
{
  
  var container = new Array();
  container.push($('#input-busqueda'));
  container.push($('#cabecera-suggest'));
  container.push($('.cabecera-lupa'));

  var ocultar=true;

  $.each(container, function(key, value) 
  {
    if (!$(value).is(e.target) // if the target of the click isn't the container...
        && $(value).has(e.target).length === 0) // ... nor a descendant of the container
    {
    }
    else
    {
      ocultar=false;
    }
  });

  if(ocultar)
    suggestBusqueda('');
  
});


/* 
---------------------------------------------------------------------------------------------
----------------------------------------Notificaciones---------------------------------------
---------------------------------------------------------------------------------------------
*/

function notificarError(html)
{
  jError
  (
      html,
      {
        autoHide : true,
        clickOverlay : true,    
        MinWidth : 200,
        TimeShown : 5000,
        ShowTimeEffect : 200,
        HideTimeEffect : 200,
        LongTrip :0,
        HorizontalPosition : 'center',
        VerticalPosition : 'top',
        ShowOverlay : true,
        ColorOverlay : '#000',
        OpacityOverlay : 0.3,
        onClosed : function(){},
        onCompleted : function(){}
      }
  );
}

function notificarExito(html)
{
  jSuccess
  (
      html,
      {
        autoHide : true,
        clickOverlay : true,    
        MinWidth : 200,
        TimeShown : 5000,
        ShowTimeEffect : 200,
        HideTimeEffect : 200,
        LongTrip :0,
        HorizontalPosition : 'center',
        VerticalPosition : 'top',
        ShowOverlay : true,
        ColorOverlay : '#000',
        OpacityOverlay : 0.3,
        onClosed : function(){},
        onCompleted : function(){}
      }
  );
}

function notificarNormal(html)
{
  jNotify
  (
      html,
      {
        autoHide : true,
        clickOverlay : true,    
        MinWidth : 400,
        TimeShown : 5000,
        ShowTimeEffect : 200,
        HideTimeEffect : 200,
        LongTrip :0,
        HorizontalPosition : 'center',
        VerticalPosition : 'top',
        ShowOverlay : true,
        ColorOverlay : '#000',
        OpacityOverlay : 0.3,
        onClosed : function(){},
        onCompleted : function(){}
      }
  );
}

/* 
---------------------------------------------------------------------------------------------
----------------------------------------Inicialización---------------------------------------
---------------------------------------------------------------------------------------------
*/
 
//Mostrar notificaciones

var notificacion=$('#notificacion').val(); //Lanza un error si no hay tipo
if(notificacion==="exitoRegistro")
{
  notificarExito('Gracias por verificar tu dirección de correo electrónico. Ya puedes iniciar sesión.');
}
else if(notificacion==="errorRegistro")
{
  notificarError('El enlace de verificación que has seguido es erroneo o ha caducado. Por favor, regístrate de nuevo o recupera la contraseña.');    
}
else if(notificacion==="loginCorrecto")
{
  notificarExito('Has iniciado sesión correctamente.');
}
else if(notificacion==="loginError")
{
  notificarError('El email o contraseña son incorrectos. Intenta iniciar sesión de nuevo.');    
}
else if(notificacion==="resetCorrecto")
{
  notificarExito('Has cambiado la contraseña correctamente. Ya puedes iniciar sesión.');
}
else if(notificacion==="resetError")
{
  notificarError('El enlace para cambiar la contraseña que has seguido no es correcto o ha caducado.');    
}


//Clicar la categoría que corresponda
// Comment: this was an odd piece of code which seemed not to be used and additionally was based on catching an error. 
// urlParam was modified to return a null value in case the parameter does not exist (instead of exception)
// TODO: review when improving change of categories
  var categoria=$.urlParam('category'); 
  if(categoria==="ent")
  {
    $(".cabecera-pestania-dch").click();
  }
  else if(categoria==="eve")
  {
    $(".cabecera-pestania-izq").click();
  }
  else 
    $(".cabecera-pestania-izq").click();

if(isLogged())
{
  $(".logged-item").show();
}
else
{
  $(".public-item").show();
}