<div class='scroll-curtain'></div>
<div class='scroll-curtain-gradient'></div>

<article>
<div class='agenda'>

    <div class='agenda-primera-linea'>
        &nbsp;
    </div>
    <div class='agenda-filtros'>
        <div class='agenda-filtros-retop'>
            <div class='agenda-filtros-tiempo'>
                <div class='tagFiltro' id='tagFiltroTemplate' style="display:none">
                    <div class='tagFiltro-imagen'>
                    </div>
                    <div class='tagFiltro-texto'>
                        Filtro Ejemplo
                    </div>
                    <div class='tagFiltro-x'>
                        x
                    </div>
                </div>
            </div>
            <div class='agenda-filtros-tiempo2'>
            </div>
        </div>
        <div class='agenda-filtros-top'>
            <div class='agenda-filtros-busqueda'>
            </div>
            <div class='agenda-filtros-tematica'>
            </div>
        </div>
        <div class='agenda-filtros-bottom'>
            <div class='agenda-filtros-lugar'>
            </div>
            <div class='agenda-filtros-entidad'>
            </div>
        </div>
    </div>
    <div class='agenda-segunda-linea'>
        <div class='div-avisos'>
            <input type=text class="public-item" id="email-avisos" placeholder="escribe tu mail">
            <input type=button id="boton-avisos" value="Recibir avisos">
        </div>
        <div class='div-ordenar'>
            Ordenar por: 
            <select id="select_ordenar">
                <option value="fecha">Fecha</option>
                <option value="tematica">Temática</option>
                <option value="lugar">Lugar</option>
                <option value="popularidad">Popularidad</option>
            </select>
        </div>
    </div>

    <div class='supergrupo-template' id='supergrupo-template'>
        <div class='supergrupo-cabecera'>CABECERA</div>
        <div class='supergrupo-cuerpo'>
            <div class='grupo-template' id='grupo-template'>
                <div class='grupo-cabecera'>
                    <div class='grupo-cabecera-izq'>
                        Izq
                    </div>
                    <div class='grupo-cabecera-cntr'>
                        Centro
                    </div>
                    <div class='grupo-cabecera-dch'>
                        Dch
                    </div>
                </div>

                <div class='grupo-filas'>
                    <div class='grupo-fila-eventos' id='grupo-fila-template-eventos'>
                        <div class='grupo-filas-extizq'>
                            <span class='grupo-elemento-temp'>
                            <IMG class='imagen-temp' SRC='css/icons/termometro_3.png' height='32px'>
                        </span>
                        </div>
                        <div class='grupo-filas-horario'>
                            <span class='grupo-elemento-hora'>
                            11:00
                            </span>
                            <span class='grupo-elemento-handup'>
                            <IMG SRC='css/icons/flecha_arriba.png'>
                        </span>
                        </div>
                        <div class='grupo-filas-der'>
                            <span class='grupo-elemento-titulo'>
                            Convocatoria
                        </span>
                            <span class='grupo-elemento-lugar'>
                            Lugar
                        </span>
                            
                        
                        </div>                      
                        <div class='grupo-filas-extder flex-align-center flex-dir-column'>
                        <span class='grupo-elemento-tipo'>
                            <img class="imagen-tipo"  src='css/icons/Event-Unique.64.png' width="20px">
                        </span>
                        
                        </div>                        
                        
                    </div>

                    <div class='grupo-fila-procesos' id='grupo-fila-template-procesos'>
                        <div class='grupo-filas-extizq'>
                            <span class='grupo-elemento-temp'>
                            <IMG class='imagen-temp' SRC='css/icons/termometro_1.png' height='32px'>
                            </span>
                        </div>
                            
                        <div class='grupo-filas-izq'>
                            <span class='grupo-elemento-hora'>
                            11:00
                            </span>
                             <div class='informacion-cabecera-izq-horas-bottom'>
                              16:00
                            </div>
                            <span class='grupo-elemento-handup'>
                            <IMG SRC='css/icons/flecha_arriba.png'>
                            </span>
                        </div>
                        <div class='grupo-filas-der'>
                            <span class='grupo-elemento-titulo'>
                            Convocatoria
                            </span>
                            <span class='grupo-elemento-texto'>
                            Descripción
                            </span>
                        </div>
                        <div class='grupo-filas-extizq'>
                            <span class='grupo-elemento-tipo'>
                            <img class="imagen-tipo" src='css/icons/Event-Unique.64.png' width="20px">
                            </span>
                            <span class='grupo-elemento-lugar'>
                            Lugar
                            </span>
                        </div>                                                                
                    </div>

                    <div class='grupo-fila-organizaciones' id='grupo-fila-template-organizaciones'>
                        
                        <div class='grupo-filas-cen'>
                            <span class='grupo-elemento-puntos'>
                                9999
                            </span>
                            <div class='grupo-filas-cen'>
                                <span><img class=grupo-elemento-copa src='css/icons/copaBig.png' width="1rem">
                                </span> <!--Quitar css elemento-->
                            </div>                        
                        </div>
                        <div class='grupo-filas-der'>
                            <span class='grupo-elemento-tituloOrg'>
                            Convocatoria
                            </span>
                            <span class='grupo-elemento-textoOrg'>
                            Descripción
                            </span>
                        </div>
                        <div class='grupo-filas-extder flex-dir-column flex-align-der'>
                        <div class='grupo-elemento-tipo'>
                            <img class="imagen-tipo"  src='css/icons/UniqueEvent.64.png' width="20px">
                        </div>
                            <div class='grupo-elemento-lugarOrg'>
                            Lugar
                        </div>
                        </div>                                                                  
                    </div>

                </div>

                <div class='grupo-pie'>
                    Texto en el pie
                </div>

            </div>
        </div>
    </div>

</div>
</article>
<script src="js/mapa.js"></script>
<script src="js/sugerencias.js"></script>
<script src="js/agenda.js"></script>


<div class='map'>
    <div class='map-breadcrumbs'>
        &nbsp;
    </div>             
    <div class='map-map'>  
        <div id="circle-button"></div>
        <div id="upbutton"></div>
        <div id="map"></div>                                               
    </div>
    <div class='map-footer'>
        &nbsp;
    </div>
</div>

<div class='informacion'>
<!--    <div class='informacion-cabecera' title='Haz click para ver el evento completo'>
        <div class='informacion-cabecera-izq'>
            <div class='informacion-cabecera-izq-calendario informacion-cuerpo-contacto-elemento-evento'>
                <div class='informacion-cabecera-izq-calendario-top'>
                    MAR
                </div>
                <div class='informacion-cabecera-izq-calendario-bottom'>
                    20
                </div>
            </div>
            <div class='informacion-cabecera-izq-horas informacion-cuerpo-contacto-elemento-evento'>
                <div class='informacion-cabecera-izq-horas-top'>
                    11:00
                </div>-->
                <div class='informacion-cabecera-izq-horas-bottom'>
                    16:00
                </div>
            
            <div class='informacion-cabecera-izq-entidad informacion-cuerpo-contacto-elemento-entidad'>
                <div class='informacion-cabecera-izq-entidad-izq'>

                </div>
                <div class='informacion-cabecera-izq-entidad-dch'>
                    <div class='informacion-cabecera-izq-entidad-top'>

                    </div>
                    <div class='informacion-cabecera-izq-entidad-bottom'>

                    </div>
                </div>
            </div>		 	       
        <div class='informacion-cabecera-dch'>
            <div class='informacion-cabecera-dch-titulo'>
                <div class='informacion-cabecera-dch-titulo-top'>
                    Nombre evento no muy largo
                </div>
                <div class='informacion-cabecera-dch-titulo-bottom'>
                    Dirección del evento - Alcalá
                </div>
            </div>
        </div>
        <div class='informacion-cabecera-abajo informacion-cuerpo-contacto-elemento-evento'>Se repite cada semana</div>
    
    <div class='informacion-cuerpo'>
        <div class='informacion-cuerpo-descBreve informacion-cuerpo-contacto-elemento-entidad'>
            Descripción breve de la entidad, que se corresponde con la que se muestra en la celda de la entidad en la lista, aunque ampliado.
        </div>
        <div class='informacion-cuerpo-tematicas'>
            <div class='informacion-cuerpo-tematicas-elemento'><img src='css/icons/etiqueta30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Tematicas: </B></div> 
            <span class='informacion-cuerpo-tematicas-listado'>Temática 1, Temática 2, Temática 3</span>
        </div>
        <div class='informacion-cuerpo-etiquetas'>
            <div class='informacion-cuerpo-etiquetas-elemento'><img src='css/icons/etiquetas30x30.png' class="informacion-cuerpo-tematicas-img"> <B>Etiquetas: </B></div>
            <div class='informacion-cuerpo-etiquetas-listado'>Etiqueta 1, Etiqueta 2, Etiqueta 3</div>
        </div>
        <div class='informacion-cuerpo-contacto'>
            <div>
                <span class='informacion-cuerpo-contacto-elemento'>Web:</span> <a target="_blank" href='http://www.templateurl.es' class='informacion-cuerpo-contacto-url'>http://www.templateurl.es</a><br>
                <span class='informacion-cuerpo-contacto-elemento'>e-Mail:</span> <a target="_blank" href='mailto:correo@templateurl.es' class='informacion-cuerpo-contacto-email'>correo@templateurl.es</a><br>
            </div>
            <div class='informacion-cuerpo-contacto-elemento-entidad'>
                <span class='informacion-cuerpo-contacto-elemento'>Twitter:</span> <a target="_blank" href='https://twitter.com/templatetwitter' class='informacion-cuerpo-contacto-twitter'>@templatetwitter</a><br>
                <span class='informacion-cuerpo-contacto-elemento'>Facebook:</span> <a target="_blank" href='https://www.facebook.com/template-facebook' class='informacion-cuerpo-contacto-facebook'>template-facebook</a>
            </div>
        </div>
        <div class='informacion-cuerpo-texto'>
            <h3></h3>
            <p></p>
        </div>

    </div>
    <div class='informacion-pie'>
        <!-- Go to www.addthis.com/dashboard to customize your tools -->
<a href="" class="expandir-detalles flex-align-der"><i title="Haz click para ver el evento completo" class="fa fa-arrows-alt" style="color: #0000FF"></i></a>
        <div id="toolbox" class="addthis_default_style addthis_20x20_style"></div>      
        <!--<div class="follow_eye_listado">Seguir</div>

        <!--
        <div class="addthis_toolbox addthis_default_style">
                <a id="atbutton"></a>
        </div>
        -->

        <!--
        <a class='share-mail' href="mailto:?subject=Asunto&amp;body=Cuerpo"
           onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
           target="_blank" title="Share on Google+"><img width="16px" src='css/icons/mail.png'></a>

        <a class='share-facebook' href="https://www.facebook.com/sharer/sharer.php?u=http://www.google.com&t=Titulo&s=Texto"
           onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;"
           target="_blank" title="Share on Facebook"><img width="16px" src='css/icons/facebook.ico'></a>

        <a class='share-twitter' href="https://twitter.com/share?url=http://www.google.com&text=Titulo"
           onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;"
           target="_blank" title="Share on Twitter"><img width="16px" src='css/icons/twitter.ico'></a>

        <a class='share-googleplus' href="https://plus.google.com/share?url=http://www.google.com"
           onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
           target="_blank" title="Share on Google+"><img width="16px" src='css/icons/googleplus.ico'></a>

        <a class='share-linkedin' href="http://www.linkedin.com/shareArticle?mini=true&url=http://www.google.com&title=Titulo&summary=Texto&source=http://www.citysens.net"
           onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=350,width=480');return false;"
           target="_blank" title="Share on Google+"><img width="16px" src='css/icons/linkedin.ico'></a>
        -->
        <!--<a class='share-link' href="#"
           onclick=''
           target="_blank" title="Share on Google+"><img width="16px" src='css/icons/link.png'></a>-->

    </div>
</div>

