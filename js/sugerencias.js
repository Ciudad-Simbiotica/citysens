//Para sacar los parámetros de GET
$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.href);
    return results[1] || 0;
}


function clickSuggestion(imagen,texto1,tipo,id) //Añadir id, texto buscado
{
  $("#cabecera-suggest").empty();
  console.log("Clic Sugerencia: "+imagen+"/"+texto1+"/"+tipo);

  var clone=$("#tagFiltroTemplate").clone();
  clone.hide();
  clone.attr("id",texto1);

  clone.find('.tagFiltro-texto').html(texto1);
  clone.find('.tagFiltro-imagen').css('background-image', "url("+imagen+")");

  console.log(window.clase);

  clone.find('.tagFiltro-x').click(function()
  {
    //Borrado
    arrayTags = jQuery.grep(arrayTags, function(value) 
    {
      var coincide=((value.texto==texto1)&(value.tipo==tipo)&(value.id==id));
      return !coincide;
    });

    cargarDatos(window.clase,$("#select_ordenar").val());

    $(this).fadeOut("fast",function(){
      $(this).parent().remove();
    });
  });

  //clone.find('.grupo-cabecera-cntr').html(center);
  //clone.find('.grupo-cabecera-dch').html(right);
  
  tipoFiltro=tipo;
  if((tipoFiltro=='institucion')|(tipoFiltro=='colectivo')|(tipoFiltro=='organizacion'))
    tipoFiltro='entidad';

  clone.addClass("tagFiltro-"+tipoFiltro);

  clone.appendTo(".agenda-filtros-"+tipoFiltro);
  clone.fadeIn("fast");


  /*
  var sugerencia=new Array();
  sugerencia["texto"]=texto1;
  sugerencia["tipo"]=tipo;
  sugerencia["id"]=id;
  */

  var sugerencia = 
  {
    "texto": texto1, 
    "tipo": tipo,
    "id": id
  };


  arrayTags.push(sugerencia);
  $("#input-busqueda").val('');
  cargarDatos(window.clase,$("#select_ordenar").val());


  /*if(!$('#input-busqueda').tagExist(texto1))
    $("#input-busqueda").addTag(texto1,{icon:tipo});
  */
  //window.location = "agenda.html?tipo="+tipo+"&texto1="+texto1;
  //alert("Has hecho click en una sugerencia de tipo "+tipo+" con el siguiente texto: "+texto1);
}


function prevSuggestion()
{
  if((typeof window.selectedSuggestion==='undefined')|(window.selectedSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");    
    window.selectedSuggestion=1;
  }
  else
  {
    if(window.selectedSuggestion>1)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-2).addClass("cabecera-suggest-fila-selected");    
      window.selectedSuggestion--;
    }
  }
}

function nextSuggestion()
{
  if((typeof window.selectedSuggestion==='undefined')|(window.selectedSuggestion==0)) //todavía no hay ninguna seleccionada
  {
    $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");    
    window.selectedSuggestion=1;
  }
  else
  {
    if($("#cabecera-suggest").find(".cabecera-suggest-fila").length>window.selectedSuggestion)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.selectedSuggestion).addClass("cabecera-suggest-fila-selected");    
      window.selectedSuggestion++;
    }
  }
}


function suggestBusqueda(texto)
{
  //TODO: Chapuza - Usa una variable global, cambiarlo
  if(window.previousSuggestText==texto)
  {
    return;
  }

  window.previousSuggestText=texto;
  if(texto=="")
  {
    $("#cabecera-suggest").empty();
    return;    
  }

  if($(".cabecera-pestania-izq").hasClass("cabecera-pestania-seleccionada"))
    entidades="";
  else
    entidades="true";


  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestions.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    idLugar: $.urlParam('idLugar'),
    date: "any",
    format: "json",
    entidades: entidades
  })
  .done(function(data) 
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
    

    window.selectedSuggestion=0;

    //Añadimos el tooltip
    if(window.clase=='eventos')
      $("#cabecera-suggest").append("<div class='cabecera-suggest-tooltip'>Buscar eventos que tengan que ver con...</div>");
    else
      $("#cabecera-suggest").append("<div class='cabecera-suggest-tooltip'>Buscar entidades que tengan que ver con...</div>");

    //Que esto lo clone de una fila por defecto      
    //Añadimos la búsqueda tal cual
    $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'><strong>"+texto+"</strong></div></div>");
    $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-busqueda");
    $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-0");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
          clickSuggestion("/citysens/icons/lupa.png",texto,'busqueda',0);
      });
    var i=1;
    $.each(data.suggestions, function(key, value)
    {
      //Creamos la sugerencia
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+value.texto1.replace(texto, '<b>'+texto+'</b>')+"</div><div class='cabecera-suggest-texto2'>"+value.texto2+"</div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-"+value.tipo);
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-"+i);
      if(value.texto2=="")
      {
        $("#cabecera-suggest").find(".cabecera-suggest-texto1:last").addClass("cabecera-suggest-texto1-sinTexto2");
      }

      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
          var icono="";
          if(value.tipo=="tematica")
            icono="/citysens/icons/etiqueta30x30.png";
          else if(value.tipo=="organizacion")
            icono="/citysens/icons/icon_CitYsens.organizacion.png";
          else if(value.tipo=="institucion")
            icono="/citysens/icons/icon_CitYsens.institucion.png";
          else if(value.tipo=="colectivo")
            icono="/citysens/icons/CitYsens.people.png";
          else if(value.tipo=="lugar")
            icono="/citysens/icons/lugar.png";
          else if(value.tipo=="lupa")
            icono="/citysens/icons/lupa.png";
          else if(value.tipo=="busqueda")
            icono="/citysens/icons/lupa.png";


          clickSuggestion(icono,value.texto1,value.tipo,value.id);
      });
      i++;
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });

    if(texto=="Villalbilla")//IrAMadrid
    {
      //Creamos la fila de ir a Madrid
      $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a "+texto.replace(texto,"<strong>"+texto+"</strong>")+"</div></div>");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").addClass("cabecera-suggest-fila-IrA");
      $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-IrA");
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-"+i);
      $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
      {
        window.location = "/citysens/?idLugar=888004444";
        //clickSuggestion("/citysens/icons/gps.png",texto,"IrA",0); //Añadir value.id, texto buscado
      });
    } 
    
  });




}
