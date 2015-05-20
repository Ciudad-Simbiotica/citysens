//Para sacar los parámetros de GET
$.urlParam = function(name){
    var results = new RegExp('[\?&amp;]' + name + '=([^&amp;#]*)').exec(window.location.search);
    return results?results[1]:null;
}


function clickSuggestion(imagen,texto1,tipo,id,abrev) //Añadir id, texto buscado
{
  $("#cabecera-suggest").empty();
  console.log("Clic Sugerencia: "+imagen+"/"+texto1+"/"+tipo);

  var clone=$("#tagFiltroTemplate").clone();
  clone.hide();
  clone.attr("id",texto1);
  if (texto1.length>27)
  texto1=abrev;
  clone.find('.tagFiltro-texto').html(texto1);
  clone.find('.tagFiltro-imagen').css('background-image', "url("+imagen+")");

  console.log(window.conf.clase);

  clone.find('.tagFiltro-x').click(function()
  {
    //Borrado  del filtro
    conf.arrayTags = jQuery.grep(conf.arrayTags, function(value) 
    {
      var coincide=((value.texto==texto1)&(value.tipo==tipo)&(value.id==id));
      return !coincide;
    });
    
    //cargarDatos(window.conf.clase,$("#select_ordenar").val());
   // if (window.listado.tipo)
   if (!window.listado.tipo)
    window.listado.tipo="eventos";
    if (!window.listado.orden)
        window.listado.orden="puntuacion";
    window.listado.orden=$("#select_ordenar").val();      
    $(this).fadeOut("fast",function(){
      $(this).parent().remove();
    });
    cargarDatos();
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


  conf.arrayTags.push(sugerencia);
  $("#input-busqueda").val('');
  //cargarDatos(window.conf.clase,$("#select_ordenar").val());
  window.listado.orden=$("#select_ordenar").val();
  cargarDatos();

  /*if(!$('#input-busqueda').tagExist(texto1))
    $("#input-busqueda").addTag(texto1,{icon:tipo});
  */
  //window.location = "agenda.html?tipo="+tipo+"&texto1="+texto1;
  //alert("Has hecho click en una sugerencia de tipo "+tipo+" con el siguiente texto: "+texto1);
}

function prevSuggestion()
{
    if(window.conf.selectedSuggestion>1)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion-2).addClass("cabecera-suggest-fila-selected");    
      window.conf.selectedSuggestion--;
    }
}

function nextSuggestion()
{
    if($("#cabecera-suggest").find(".cabecera-suggest-fila").length>window.conf.selectedSuggestion)
    {
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion-1).removeClass("cabecera-suggest-fila-selected");    
      $("#cabecera-suggest").find(".cabecera-suggest-fila").eq(window.conf.selectedSuggestion).addClass("cabecera-suggest-fila-selected");    
      window.conf.selectedSuggestion++;
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

  // TODO: Later it uses the condition     if(window.clase=='eventos') for somthing simmilar.
  //       Probably it would be better to think of some alternative method to indicate what section we are in.
  //       Specially considering that there will be other categories in addition to events and entities.
  if($(".cabecera-pestania-izq").hasClass("cabecera-pestania-seleccionada"))
    entidades="";
  else
    entidades="true";


  //Que cargue las sugerencias usando AJAX
  var getAgenda = "getSuggestions.php?";
  $.getJSON(getAgenda, 
  {
    query: texto,
    idTerritorio: window.conf.idTerritorio,
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
    

    window.conf.selectedSuggestion=0;

    //Añadimos el tooltip
    if(window.conf.clase=='eventos')
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
          clickSuggestion("css/icons/lupa.png",texto,'busqueda',0);
     $("#input-busqueda").val('');
      });
    var i=1;
    $.each(data.suggestions, function(key, value)
    {
      if(value.tipo=="IrA")
      {
        if ($("#input-busqueda").val().length>3)         
            $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a "+value.texto1.replace(new RegExp("("+value.textoBuscado+")", 'gi'), "<b>$1</b>")+"</div></div>");
        else
            $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1 cabecera-suggest-texto1-sinTexto2'>Ir a "+value.texto1+"</div></div>");
        $("#cabecera-suggest").find(".cabecera-suggest-fila:last").addClass("cabecera-suggest-fila-IrA");
        $("#cabecera-suggest").find(".cabecera-suggest-icono:last").addClass("cabecera-suggest-icono-IrA");
        $("#cabecera-suggest").find(".cabecera-suggest-fila:last").attr("id","cabecera-suggest-fila-"+i);
        $("#cabecera-suggest").find(".cabecera-suggest-fila:last").click(function()
        {
          if(value.activo==1)
          {           
        //   window.location = "?idTerritorio="+value.id;//Se podrian añadir los parametros
           window.conf.idTerritorio = value.id;
         //   cargarMapa(window.conf.idTerritorio);               
          // else          
           window.listado.orden=$("#select_ordenar").val(); 
           removeAllTags();
           cargarDatos();        
           // clickSuggestion(icono,value.texto1,value.tipo,value.id);
          }
          else
          {         
            $("#overlay").addClass("overlayPeque");
            $(".darkOverlay").fadeIn("fast");
            $("#overlay").load("cityNotReadyYet.html",function()
            {
              $('#overlay').html($('#overlay').html().replace(/{CIUDAD}/g,value.texto1)); //need rev. sometimes load after show an change text
              $('#input-email-idLugar').val(value.id);
              $('#input-email-nombreCiudad').val(value.texto1);
            });            
          }
          //clickSuggestion("css/icons/gps.png",texto,"IrA",0); //Añadir value.id, texto buscado
        });
      }
      else
      {
        //Creamos la sugerencia
        if ($("#input-busqueda").val().length>2)    
            $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+value.texto1.replace(new RegExp("("+value.textoBuscado+")", 'gi'), "<b>$1</b>")+"</div><div class='cabecera-suggest-texto2'>"+value.texto2+"</div></div>");
        else
            $("#cabecera-suggest").append("<div class='cabecera-suggest-fila'><div class='cabecera-suggest-icono'></div><div class='cabecera-suggest-texto1'>"+value.texto1+"</div><div class='cabecera-suggest-texto2'>"+value.texto2+"</div></div>");
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
              icono="css/icons/etiqueta30x30.png";
            else if(value.tipo=="organizacion")
              icono="css/icons/icon_CitYsens.organizacion.png";
            else if(value.tipo=="institucion")
              icono="css/icons/icon_CitYsens.institucion.png";
            else if(value.tipo=="colectivo")
              icono="css/icons/CitYsens.people.png";
            else if(value.tipo=="lugar")
              icono="css/icons/lugar.png";
            else if(value.tipo=="lupa")
              icono="css/icons/lupa.png";
            else if(value.tipo=="busqueda")
              icono="css/icons/lupa.png";

            clickSuggestion(icono,value.texto1,value.tipo,value.id,value.abrev);
        });
      }
      i++;
    
      //¿Animarlo?
      //$("#cabecera-suggest").find(".cabecera-suggest-fila").slideDown("fast");
    });
    //for default select cabecera-suggest-fila:first
            $("#cabecera-suggest").find(".cabecera-suggest-fila:first").addClass("cabecera-suggest-fila-selected");
            window.conf.selectedSuggestion=1;
            if ($(".input-busqueda").val()==''){
                $(".input-busqueda").val('');
                $("#cabecera-suggest").empty();
            }
  });




}
