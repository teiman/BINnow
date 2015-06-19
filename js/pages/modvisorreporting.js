

var gorbal=false;


if(typeof filtros == "undefined"){
   filtros = {};
}

if(typeof ventana == "undefined"){
   ventana = {};
}

//Ayuda, permite quitar un valor
Array.prototype.filterOutValue = function(v) {

    var x, _i, _len, _results;
    _results = [];
    for (_i = 0, _len = this.length; _i < _len; _i++) {
        x = this[_i];
        if (x !== v) {
            _results.push(x);
        }
    }
    return _results;

};


function xinspect(o,i){
    if(typeof i=='undefined')i='';
    if(i.length>50)return '[MAX ITERATIONS]';
    var r=[];
    for(var p in o){
        var t=typeof o[p];
        r.push(i+'"'+p+'" ('+t+') => '+(t=='object' ? 'object:'+xinspect(o[p],i+'  ') : o[p]+''));
    }
    return r.join(i+'\n');
}



//Calcula la pagina actual (las paginas tienen 100 registros), desde una altura en pixeles (cada registro usa 20 pixeles de altura).
function offset2pagina(offset){
    var tamagnoPaginaTransferencia = 1000;
    offset = offset*1;

    if(isNaN(offset))
        offset = 0;


    var offset_lines = parseInt(offset/20,10);// 20 pixels => 1 linea
    pagina = parseInt(offset_lines/tamagnoPaginaTransferencia,10);// 0 a 99 lineas, pagina 0;  99 a  199, pagina 1...

    // console.log("off:"+offset+",es:"+pagina);

    return pagina;
}

function paginaActual(){
    return offset2pagina( $("#contenedorcajaresultados").scrollTop());
}


function creatBotonBorrarInforme(){

    var img = document.createElement("img");
    $(img).attr("src","icons/basura1.gif")
    .attr("align","absmiddle")
    .css("padding-bottom","4px")
    .css("padding-left","8px");

    return img;
}


function AvisarUsuario(mensaje){

                   if($(".notificador").length){
                        $(".notificador").remove();
                    }

                    var notice = '<div class="notice notificador" >'
                        + '<div class="notice-body">'
                        + '<img src="images/info.png" alt="" />'
                        + '<h3>Aviso</h3>'
                        + '<p>'+mensaje+'</p>'
                        + '</div>'
                        + '<div class="notice-bottom">'
                        + '</div>'
                        + '</div>';

                    $( notice ).purr({
                        usingTransparentPNG: true
                    });
}


function insertarEnPosicion(datos,ponEn,nuevoElemento){
    var finaldato = [];
    var posicion = 0;

    _.each(datos,function(dato){
        if(posicion==ponEn){
            finaldato.push(nuevoElemento);
        }
        finaldato.push(dato);
        posicion++;
    });

    return finaldato;
}


/*
 * Copia la anchura de las columnas del listado, a las cabeceras (que es una tabla aparte) de modo que coincidan
 */
function ArreglarColumnas(){
    var imp,index,tams = [],tams2=[],n1=0,n2=0;
    var w,min1=99999,min2=99999,min=99999;

    //console.log("Inicia ArreglarColumnas");

    imp = "!important";

    $("#cajaderesultados tr:eq(1) td",$("#tablaresultados")).each(function(){
        w = $(this).width();
        min1 = (w<min1)?w:min1;
        tams.push(w);
        n1++;
    });
    $("#cajaderesultados tr:eq(0) td",$("#tablaresultados")).each(function(){
        w = $(this).width();
        min2 = (w<min2)?w:min1;
        tams2.push(w);
        n2++;
    });

    /* Cuando esto ocurre, elegir la primera fila para calcular los tamaños era una mala idea */
    if(n2>n1){
        tams=tams2;

        min = min2;//usamos como minimo, el del registro 2

        if (n2==1){
            return;//si hay solo una columna, no tenemos interes en usarla
        }
    } else {
        min = min1;//usamos como minimo, el del registro 1
    }

    var usaPequegno = min<150;


    index = 0;
    $("tr#lista_columnas th",$("#tablaresultados1")).each(function(){
        var size = tams[index]+12;
        //var size = tams[index];

        if(size && !isNaN(size)){
            $(this).attr("width",size)
            .attr("align","center")
            .css("width",size+"px");

            if (usaPequegno){
                $(this).addClass("columna_oprimida")
            } else {
                $(this).removeClass("columna_oprimida")
            }

        }

        index++;
    });

    index = 0;
    $("#cajaderesultados_subtotales tr:eq(0) td",$("#tablaresultados3")).each(function(){
        var size = tams[index]+1;

        if(size && !isNaN(size)){
            $(this).attr("width",size)
            .css("width",size+"px")
            .css("overflow","hidden")
            .css("max-width",size+"px")
            .css("min-width",size+"px");
        }

        index++;
    });

}


function ArreglarColumnas_limpia(){

    $("tr#lista_columnas th",$("#tablaresultados1")).each(function(){
        $(this).removeAttr("width")
            .css("width","")
            .removeClass("columna_oprimida");
    });

    $("#cajaderesultados_subtotales tr:eq(0) td",$("#tablaresultados3")).each(function(){
            $(this).removeAttr("width")
            .css("width","")
            .css("max-width","")
            .css("min-width","");
    });

}


/*
 * Mantiene un cache de "paginas" de una consulta.
 */
var cachePaginas = (function(){
    var borrarCache = false;//no funciona
    var localcache = [];
    var lotenemos = [];
    var pedidas = [];
    var paginaMostrando = -1;
    var modo = "cogedatos";

    return {
        $barra:$("#ajaxbar"),
        setmodo:function(nuevomodo){
            modo  = nuevomodo;

            //console.log("cachePaginas:SETmodo:"+nuevomodo);
        },
        getmodo:function(){
            //console.log("cachePaginas:GETmodo:"+modo);
            return modo;
        },
        barra:function(estado){
            if(!estado){
                $("#ajaxbar").hide();
            } else {
                $("#ajaxbar").removeClass("oculto");
                $("#ajaxbar").show();
            }
        },
        vaciar:function(){
            localcache =[];
            lotenemos = [];
            paginaMostrando= -1;
            pedidas =[];
            //modo = "cogedatos";
            console.log("RESETADO cache");
        },
        del:function(pagina){
            delete localcache[pagina];
            lotenemos[pagina]= false;
        },
        add:function(pagina,texto){
            console.log("agnadimos pagina a cache:"+pagina);
            localcache[pagina] = texto;
            lotenemos[pagina]= true;
        },
        get:function(pagina){
            return localcache[pagina];
        },
        test:function(pagina){
            return lotenemos[pagina]!=undefined;
        },
        addPedida:function(pagina){
            pedidas[pagina] = true;
        },
        esPedida:function(pagina){
            return pedidas[pagina]!=undefined;
        },
        load:function(pagina,pedir){
            if(cachePaginas.test(pagina)){
                console.log("Visualizando pagina:"+pagina)
                cachePaginas.mostrar(pagina)
            } else {
                console.log("No tenemos pagina:"+pagina)
                if(!pedidas[pagina]) {
                    //$("#paginaCargando").val(pagina);
                    pedir();
                } else
                    console.log("Esperando... ya habia sido pedida");

                pedidas[pagina] = true;
            }
        },
        /*
         * Utilizando unas marcas html, se disparan pequeños eventos. Creado inicialmente para apoyar el debugeo.
         */
        autoJS:function(){

            function sql_mostrar(text){

                $(".autolog").removeClass("autobold");

                $("#sql_mostrar_normal").html("<pre id='sql_mostrar_normal_text'>"+text+"</pre>")
                .addClass("autobold");

                //$("#sql_mostrar_normal_text").snippet("sql");


            }
            function sql_mostrar_subtotales(text){
                $(".autolog").removeClass("autobold");
                $("#sql_mostrar_subtotales").html("<pre>"+text+"</pre>")
                .addClass("autobold");
            }
            function sql_mostrar_total(text){
                $(".autolog").removeClass("autobold");
                $("#sql_mostrar_total").html("<pre>"+text+"</pre>")
                .addClass("autobold");
            }
            function cuantas_lineas(text){
                $("#num_lineas_listado").html(text);
            }

            //console.log("autoJS, invocado")
            $(".autoeval").each(function(){
                var text = $(this).text();
                var tipo = $(this).attr("data-tipo");
                //console.log("encontrado autoeval:"+tipo+",text:"+text);

                /* "Eventos" */
                switch(tipo){
                    case "sql_mostrar":
                        sql_mostrar(text);
                        break;
                    case "sql_mostrar_subtotales":
                        sql_mostrar_subtotales(text);
                        break;
                    case "sql_mostrar_total":
                        sql_mostrar_total(text);
                        break;
                    case "cuantas_lineas":
                        cuantas_lineas(text);
                    default:
                        break;
                    }
                });

                //$(".autoeval").removeClass("autoeval");
                $(".autoeval").remove();//el mensaje ya ha sido usado, asi que lo eliminamos

            },
            mostrar:function(pagina){
                if(paginaMostrando==pagina){
                    console.log("mostrar: no hacemos nada, ya estas viendo:"+pagina);
                    return;//es la que estamos viendo
                }

                var htmlPagina = cachePaginas.get(pagina);

                if( $.browser.msie ){
                    $("#cajaderesultados").html(htmlPagina);
                } else {
                    document.getElementById("cajaderesultados").innerHTML = htmlPagina;
                }

                cachePaginas.autoJS();

                setTimeout("ArreglarColumnas()",0);

                paginaMostrando = pagina;
            },
            loadActual:function(pedir){
                var actual = paginaActual();
                if(paginaMostrando==actual){
                    console.log("loadActual: no hacemos nada, ya estas viendo:"+pagina);
                    return;//es la que estamos viendo
                }

                console.log("He!, estamos en la pagina:"+actual);

                cachePaginas.load(actual,pedir);
            }
        };
    })();


/*
 * Configurando jquery, para calendario español
 */

$(function(){

  $.datepicker.regional['es'] = {
      closeText: 'Cerrar',
      prevText: '<Ant',
      nextText: 'Sig>',
      currentText: 'Hoy',
      monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
      dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
      dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
      weekHeader: 'Sm',
      dateFormat: 'dd/mm/yy',
      firstDay: 1,
      isRTL: false,
      showMonthAfterYear: false,
        yearSuffix: ''
    };

   $.datepicker.setDefaults($.datepicker.regional['es']);
});




/*
 * Carga la pagina N'esima basandose en la pagina que estamos visualizando.
 */

$(function() {

    if ($.browser.webkit) { //patch a unwanted warning in chrome.
        $.event.props = $.event.props.join('|').replace('layerX|layerY|', '').split('|');
    }

    function CargarPaginaActual(){
        function CargarYMostrar(){
            //Solicitamos una pagina que no conocemos
            var modo = cachePaginas.getmodo();

            $("#modoform").val(modo);

            if(modo=="cogedatos")
                $("a.eligeSuma").removeClass("eligeSumaSeleccionado");

            var paginaPeticionMostrar = paginaActual();
            $("#offsetvalue").val(paginaPeticionMostrar);

            console.log("Pedimos pagina:"+paginaPeticionMostrar);
            cachePaginas.barra(true);

            cachePaginas.addPedida(paginaPeticionMostrar);
            $.ajax({
                type: 'POST',
                url: 'modvisorreporting.php',
                data: $('#reporting').serializeArray(),
                success: function(data){
                    cachePaginas.barra(false);
                    cachePaginas.add(paginaPeticionMostrar,data);//conocemos una nueva pagina

                    console.log("Obtenemos pagina:"+paginaPeticionMostrar);

                    if(1)
                        cachePaginas.loadActual(CargarYMostrar);//se rellamara, si la pagina que queremos ver no es la que actualmente esta seleccionada
                    else
                        cachePaginas.mostrar(paginaPeticionMostrar);

                    delete data;
                }
            });
        }
        console.log("Podria visualizarse la pagina actual, por favor?")
        cachePaginas.loadActual(CargarYMostrar);// cachePaginas.loadActual
    }

    $("#contenedorcajaresultados").scroll( $.debounce( 250,false, CargarPaginaActual) );
});




/*
 *
 * class filtro
 *
 */
$(function() {





    filtros.trasponerColumnas = function(desde,hacia){
        //var usandose = this._enUso;

        if (desde == hacia) return;

        var elementoDesde = this._enUso[desde];
        var elementoHasta = this._enUso[hacia];

        this._enUso[hacia] = elementoDesde;
        this._enUso[desde] = elementoHasta;

        $("#icolumnas").val(this._enUso.join(","));//hack

        //console.log("desde:"+desde+",hasta:"+hacia);
    }


    filtros.cambiaAgrupado = function(){

        //console.log("filtros.cambiaAgrupado inicia");
        $("#subtotales_menu").html("");

        var boton;
        var text;

        esPrimero = true;

        var lista = filtros.getAgrupar();


        /*
         * Se eliminan elementos vacios que hayan podido quedar, y que no queremos.
         */

        var newlista = [];

        $.each(lista,function(key,valor){
            if(valor && valor.length>0){
                newlista.push(valor);
            }
        });

        filtros._agruparLista  = newlista;


        /*
         * Se construyen los filtros dinamicos en funcion de esto
         */

        var msgtotal = "";
        var idtotal = "";
        var msgpad = "";
        var boton;

        $.each(newlista,function(key,valor){
            boton = $("<a>");

            text = trans[valor];

            msgtotal += msgpad + text;
            idtotal += "," + valor;
            msgpad = " y ";


            mensaje = "Mostrar subtotales por: "+ msgtotal;

            boton
            .attr("href","#")
            .addClass("eligeSuma")
            .attr("data-grupo",idtotal)
            .html(msgtotal)
            .attr("title",mensaje)
            .attr("alt",mensaje)
            ;

            //console.log("idtotal aqui:"+idtotal);
            boton.click($.debounce(500,function(){
                var localidtotal = $(this).attr("data-grupo");
                $("#eligidoagrupar").val(localidtotal);
                $("a.eligeSuma").removeClass("eligeSumaSeleccionado");
                $(this).addClass("eligeSumaSeleccionado");
                //cachePaginas.setmodo("cogegrupo");
                ventana.muestraListado("cogegrupo",idtotal);
            }))

            if(!esPrimero)
                $("#subtotales_menu").append(document.createTextNode(" · "));

            $("#subtotales_menu").append(boton);

            esPrimero = false;
        })

        if(boton && boton.length){
            boton.html("TODO");
        }

    }


    /*
     * Los filtros han sido modificados, y hay que actualizar cosas que dependen de el, y hacer visible el nuevo estado.
     */
    filtros.modificado = function(){

        filtros.cambiaAgrupado();

        var nombreOriginal = $("#nombreListado").attr("data-original");

        if(nombreOriginal) {
            $("#nombreListado").html(nombreOriginal + " ( modificado )");

             //<img src="icons/basura1.gif" onclick="alert('foo')" align="absmiddle" style="padding-bottom:4px;">

             //var img = creatBotonBorrarInforme();
             //$("#nombreListado").append(img);
        }



    };


    filtros.cuentaValidas = function (arreglo){
        var validas = 0;
        var str = "";
        if(arreglo)
        for(var t=0;t<arreglo.length;t++){
            str = arreglo[t];
            if (str && str.length>0){
                validas++;
            }
        }
        return validas;
    };

    /*------------*/

    filtros.eliminarSubtotal = function(newid){
        this._subtotalLista = this._subtotalLista.filterOutValue(newid);
        filtros.modificado();
    };

    filtros.getSubtotal = function(){
        return this._subtotalLista;
    };

    filtros.agnadirSubtotal = function(newid){
        if(jQuery.inArray(newid, filtros._subtotalLista)<0){
            filtros._subtotalLista.shift(newid);
            filtros.modificado();

            ventana.sugiereReenviar(true,po_recargar);
            filtros.necesitaRecargar(true);//necesita recargar toda la pagina
        }
    };


    /*------------*/

    filtros.eliminarAgrupar = function(newid){
        this._agruparLista = _.without(filtros._agruparLista,newid);
        filtros.modificado();
    };

    filtros.existeAgrupar = function(newid){
        return _.include(filtros._agruparLista, newid);
    };


    filtros.getAgrupar = function(){
        return this._agruparLista;
    };

    /* Añade un campo a los filtros de agrupamiento */
    filtros.agnadirAgrupar = function(newid){

        if(!columnas_id2tipo.agrupable(newid)){
            AvisarUsuario("No se puede agrupar por este tipo de columna.");
            return;
        }


        if(!filtros.existeAgrupar(newid)){
            //console.log("filtros.agnadirAgrupar newid:"+newid)

            var antes = filtros._agruparLista.join(",");
            filtros._agruparLista.push(newid);

            var despues = filtros._agruparLista.join(",");

            //console.log("Antes:"+antes+",despues:"+despues);

            filtros.modificado();

            ventana.sugiereReenviar(true,po_recargar);
        }
    };

    /*------------*/

    filtros.eliminarFiltro = function(newid){
        filtros._filtrosLista = _.without( filtros._filtrosLista, newid );
        filtros.modificado();
    };

    filtros.getFiltros = function(){
        return filtros._filtrosLista;
    };

    filtros.agnadirFiltro = function(newid){
        if(jQuery.inArray(newid, filtros._filtrosLista)<0){
            filtros._filtrosLista.unshift(newid);
            filtros.modificado();
            ventana.sugiereReenviar(true,po_recargar);
            //filtros.necesitaRecargar(true);//necesita recargar toda la pagina
        }
    };

    /*------------*/

    filtros.eliminarColumna = function(newid){
        this._enUso = this._enUso.filterOutValue(newid);
        filtros.modificado();
    };


    filtros.getenUso = function(){
        return this._enUso;
    };

    filtros.getColumnas = function(){
        return filtros.getenUso();
    }


    filtros.agnadirColumna = function(newid,posicion){ //devuelve si se han modificado las columnas
        if(jQuery.inArray(newid, filtros._enUso)<0){
            //la columna resulta ser nueva
            //console.log("nueva,columna, probablemente habra que recargar toda la pagina")

            console.log("filtros.agnadirColumna:newid:"+newid+",posicion:"+posicion);



            if(posicion>=0) {
                ventana.construirColumna(posicion,newid);

                filtros._enUso = insertarEnPosicion(filtros._enUso,posicion,newid);
                $("#icolumnas").val( filtros._enUso.join(","));
            } else {
                ventana.construirColumna(0,newid);

                filtros._enUso.unshift(newid);
            }

            filtros.modificado();

            ventana.sugiereReenviar(true,po_recargar);
            filtros.necesitaRecargar(true);//necesita recargar toda la pagina
            return true;
        }
        return false;
    };

    /*------------*/


    filtros.necesitaRecargar = function(estado){

        //console.log(estado?"activando recargar":"desactivando recargar");

        if(estado){
            //console.log("algo ha activado la necesidad de recargar");
        }

        ventana.necesitaRecargarPagina = estado;

    };


    filtros.eliminarAgrupamiento = function(idagrupamiento){
        filtros._agruparLista = filtros._agruparLista.filterOutValue(idagrupamiento);
        filtros.modificado();
    }

    filtros.eliminarSubtotal = function(idagrupamiento){
        filtros._subtotalLista = filtros._subtotalLista.filterOutValue(idagrupamiento);
        filtros.modificado();
    }

    filtros.eliminarFiltrosf =  function(idagrupamiento){
        filtros._filtrosLista = filtros._filtrosLista.filterOutValue(idagrupamiento);
        filtros.modificado();
    }


    filtros.flasea = function (prefijo,newid){
        var id = "#"+prefijo+"_"+newid;

        if($(id).length>0){
            //$(id).addClass("ilumina").delay("200").removeClass("ilumina");
            $(id).addClass("ilumina");

            setTimeout(function(){
                $(id).removeClass("ilumina");
            },200)
        }

        //console.log("se ilumina/desilumina cosa")
    }

    filtros.numFiltrosSubtotal = function(){
        return filtros._subtotalLista.join("").length;
    }
});



/*
 * Class ventana
 *
 */

$(function() {
    //NOTA: las tablas de riesgo estaran en una base de datos separada


    /*------------*/ /*------------*/ /*------------*/



    ventana.build_json_combo = function(esReadOnly,newid,param1,params,item){

        var textIfDisabled = "";
        var idfield1 = "param_filtro_"+newid;


        var cssFlat = esReadOnly?"flatflat":"";
        
        if (esReadOnly) {
            textIfDisabled = " readonly ";
        }

        campos = $("<select id='"+idfield1+"' "+textIfDisabled+"  class='cambiosMuestraRecargar serializarpost serializaGrupos insidebox "+cssFlat+"'></select>");
        campos.append(  $("<option selected='selected'></option>").html(param1) );

        if(!esReadOnly) {

            ventana.set_cargaSucia(newid,true);

            var procesaRetorno = function(data){
                ventana.set_cargaSucia(newid,false);

                var textoIgnorar = "Selección multiple";

                var $field = $("#"+idfield1);//
                var $option;
                $field.html("");


                var $seleccionados = $(document.createElement("optgroup"));
                $seleccionados.attr("label","Filtra")
                .addClass("filtra");

                var $no_seleccionados = $(document.createElement("optgroup"));
                $no_seleccionados.attr("label","Disponibles")
                .addClass("disponibles");

                var ignorar = $(document.createElement("option")).val("__ignorar__");
                ignorar.html(textoIgnorar);

                $field.append(ignorar);

                var optionitem,valoption;
                var autodescribeIgnorar = "";

                if(data)
                    for(t=0;t<data.length;t++){
                        //if(data[t]==param1){
                        optionitem = data[t];

                        valoption = $.trim(optionitem["val"]);

                        if(_.include(params,valoption)){
                            $option  = $(document.createElement("option")).html(optionitem["text"]+" ");
                            //$option  = $(new Option(data[t] + " ","hola") );
                            //$option.attr("selected","selected")
                            $option.attr("value",optionitem["val"]);
                            $option.attr("data-modo","sel");
                            $seleccionados.append(  $option );

                            autodescribeIgnorar = autodescribeIgnorar + optionitem["text"] + ", ";
                        } 
                    }

                console.log("encontrado:"+autodescribeIgnorar);

                if(data)
                    for(t=0;t<data.length;t++){
                        optionitem = data[t];
                        valoption = $.trim(optionitem["val"]);

                        if(!_.include(params,valoption)){
                            $option  = $(document.createElement("option")).html(optionitem["text"]+" ");
                            //$option  = $(new Option(data[t] + " ") );
                            $option.attr("value",optionitem["val"]);
                            $option.attr("data-modo","nosel");
                            $no_seleccionados.append(  $option );
                        }
                    }

                $field.append($seleccionados);
                $field.append($no_seleccionados);

                $field.removeClass("insidebox");
                //$field.val(param1);


                if(data && autodescribeIgnorar){
                    if(1) ignorar.html(autodescribeIgnorar+"");
                }
                autodescribeIgnorar = "";


                var controlador = function(event,ui){
                    if($(this).val()!="__ignorar__"){
                        //$("option",$field).removeClass("mmarcado");
                        $("option:selected",$field).addClass("mmarcado");
                    }



                    setTimeout(function(){

                        $field = $("#"+idfield1);

                        //var htmlprevio = ($field.outerHTML());
                        //console.log("html-previo:"+htmlprevio);

                        var codigoSeguro = -987654321;
                        var agnadirSeleccionado = codigoSeguro;
                        var agnadirNoSeleccionado = codigoSeguro;

                        var autodescribeIgnorar = "";

                        $(".mmarcado",$field).each(function () {
                            $(this).removeClass("mmarcado");

                            var val = $(this).val();

                            console.log("moviendo val:"+val);

                            if(val=="__ignorar__") {
                                console.log("ignora esta columna")
                                return;
                            }

                            var estadoActual = $(this).attr("data-modo");

                            var newmodo = estadoActual=="sel"?"nosel":"sel";
                            $(this).attr("data-modo",newmodo);

                            if(newmodo=="sel"){
                                $seleccionados.append(this);

                                //$seleccionados.append( $("<option>"+val+"-z</option>") );
                                //console.log("val en seleccionado:"+val);
                                agnadirSeleccionado = val;
                            } else {
                                $no_seleccionados.append(this);
                                //$no_seleccionados.append( $("<option>"+val+"-no</option>") );
                                //console.log("val en NO seleccionado:"+val);
                                agnadirNoSeleccionado = val;
                            }


                        });


                        $seleccionados.find("option").each(function(){
                            var newtext = $(this).text();
                            autodescribeIgnorar = jQuery.trim(autodescribeIgnorar) + jQuery.trim(newtext)+",";
                        })

                        console.log("aI:"+autodescribeIgnorar);

                        if ( autodescribeIgnorar && autodescribeIgnorar!="")
                            $field.find("[value=__ignorar__]").html(autodescribeIgnorar);
                        else
                            $field.find("[value=__ignorar__]").html("Selección multiple");


                        if($.browser.msie){
                            console.log("recargando el html desde si mismo");

                            autodescribeIgnorar = "";

                            var html = ($field.outerHTML());
                            $field.outerHTML("");
                            $field.outerHTML(html);

                            //console.log("html-posterior:"+html);

                            $field = $("#"+idfield1);

                            /*
                                            if(!$field){
                                                console.log("field no se encuentra!");
                                            } else {
                                                console.log("field ES ALGO");
                                            }*/

                            $field.change(function(event,ui){
                                //console.log("controlador rebindeado-se llama");
                                controlador(event,ui);
                            });

                            if(agnadirSeleccionado!=codigoSeguro){
                                var encontrado = false;

                                $("option",$field).each(function(){
                                    var valor = $(this).val();

                                    if(valor==agnadirSeleccionado){
                                        encontrado = true;
                                    }
                                });

                                if(!encontrado){
                                    var $selcaja = $("optgroup.filtra",$field);

                                    $selcaja.append($("<option  data-modo='sel'>"+_.escape(agnadirSeleccionado)+"</option>"));
                                //console.log("se ha reagnadido val:"+agnadirSeleccionado);
                                    
                                }

                            } else
                            if(agnadirNoSeleccionado!=codigoSeguro){
                                var encontrado = false;

                                $("option",$field).each(function(){
                                    var valor = $(this).val();

                                    if(valor==agnadirSeleccionado){
                                        encontrado = true;
                                    }
                                });

                                if(!encontrado){
                                    var $selcaja = $("optgroup.disponibles",$field);

                                    $selcaja.append($("<option data-modo='nosel'>"+_.escape(agnadirNoSeleccionado)+"</option>"));
                                //console.log("se ha reagnadido val:"+agnadirNoSeleccionado);
                                }
                            }


                            $("optgroup.filtra option",$field).each(function(){
                                var newtext = $(this).text();
                                autodescribeIgnorar = jQuery.trim(autodescribeIgnorar) +  jQuery.trim(newtext)+",";
                            })
                            
                            if ( autodescribeIgnorar && autodescribeIgnorar!="")
                                $field.find("[value=__ignorar__]").html(autodescribeIgnorar);
                            else
                                $field.find("[value=__ignorar__]").html("Selección multiple");
                        }
                    },10);


                    $(':selected',$field).attr('selected', '');
                    $(':selected',$field).removeAttr('selected');
                    $field.val("__ignorar__");

                    event.stopPropagation();
                };

                $field.change(controlador);
            };

            /*
             * Pedimos los datos
             */
            $.ajax({
                type: 'POST',
                url: 'modreporting.php',
                data: {
                    "modo":"consultacombo",
                    "combo":item.id
                },
                dataType: "json",
                async: true,
                success: procesaRetorno
            });
        }

        return campos;
    };





    /*------------*/ /*------------*/ /*------------*/

    ventana.reconstruirFiltros = function(){
        var agrupar1 = filtros.getAgrupar();
        var subtotal1 = filtros.getSubtotal();
        var filtros1 = filtros.getFiltros();

        console.log("ventana.reconstruirFiltros: Reconstruyendo filtros");

        var t,newid;

        if(agrupar1)
        for(t=0;t<agrupar1.length;t++){
            newid = agrupar1[t];
            ventana.muestraAgrupar(newid);
        }

        if(subtotal1)
        for(t=0;t<subtotal1.length;t++){
            newid = subtotal1[t];
            ventana.muestraSubtotal(newid);
        }

        if(filtros1)
        for(t=0;t<filtros1.length;t++){
            newid = filtros1[t];
            ventana.muestraFiltro(newid);
        }

        //$(".itemfiltro",$("#solucionesfiltros")).corner("4px");

        filtros.cambiaAgrupado();
    };

    /* ---------------------- */

    ventana.muestraFiltro =function(newid){

        var item,textIfDisabled,t,cerrar,key,esReadOnly = false;
        var campos,idfield1,idfield2, cssFlat,especialcss;

        if(!newid)return;
        if(newid.length<2) return;

        esReadOnly = jQuery.inArray(newid, filtros._filtrosListaReadOnly)>-1;

        cssFlat = esReadOnly?"flatflat":"";

        if(esReadOnly)
            especialcss = "oculto";

        if(esReadOnly){
            cerrar = $("<span></span>");
        }else {
             
             if(!ventana.x_visible)
                cerrar = $("<span></span>");
             else
                cerrar = $("<a href='#' class='closex' alt='Quitar' title='Quitar'>x</a>").click(function(){
                filtros.eliminarFiltro(newid);

                $("#filtrosf_"+newid).remove();

                ventana.sugiereReenviar(true,po_recargar);
             });
        }

        var param1 = "", param2 = "",params= [];

        //busca parametros
        for(t=0;t<filtros._filtrosListaParam.length;t++){
            item = filtros._filtrosListaParam[t];

            if (newid==item.tipo){
                if(item["param1"]) param1 = $.trim(item["param1"]);
                if(item["param2"]) param2 = $.trim(item["param2"]);

                for(t=0;t<1000;t++){
                    if(item["param"+t])
                        params[t] = $.trim(item["param"+t]);
                }
                break;
            }
        }

        //console.log("filtrolista:param1:"+param1+",param2:"+param2);
        item = {
            "id":0,
            "tipo":"desconocido"
        };

        if(columnas)
        for(t=0;t<columnas.length;t++){
            item = columnas[t];

            if (item.id == newid){
                break;
            } else {
                item = {
                    "id":0
                };
            }
        }


        //console.log("Generando filtro para newid:"+newid+",tipo:"+item["tipo"]);


        switch(item.tipo){
            case "json":
                campos = ventana.build_json_combo(esReadOnly,newid,param1,params,item);
                break;

            case "fecha":
                if (esReadOnly) {
                    textIfDisabled = " readonly ";
                }

                idfield1 = "param_filtro_"+newid+"_d";
                idfield2 = "param_filtro_"+newid+"_h";

                campos = $("<span></span>")
                .append($("<span> - </span>"))
                .append(
                    $("<input type='text' value='"+param1+"' "+textIfDisabled+" size='9' id='"+idfield1+"' class='datepickerme cambiosMuestraRecargar serializarpost insidebox "+cssFlat+"'>")
                )
                .append($("<span> hasta </span>"))
                .append(
                    $("<input type='text' value='"+param2+"'  "+textIfDisabled+" size='9' id='"+idfield2+"' class='datepickerme cambiosMuestraRecargar serializarpost insidebox "+cssFlat+"'>")
                )
                .append($("<span> </span>"))
                ;

                break;

            case "cantidad":
                if (esReadOnly) {
                    textIfDisabled = " readonly ";
                }

                idfield1 = "param_filtro_"+newid+"_1";
                idfield2 = "param_filtro_"+newid+"_2";

                campos = $("<span>")
                .append($("<span> - Entre</span>"))
                .append(
                    $("<input type='text' value='"+param1+"' "+textIfDisabled+" size='8' id='"+idfield1+"' class='cambiosMuestraRecargar serializarpost insidebox "+cssFlat+"'>")
                )
                .append($("<span> y </span>"))
                .append(
                    $("<input type='text' value='"+param2+"'  "+textIfDisabled+" size='8' id='"+idfield2+"' class='cambiosMuestraRecargar serializarpost insidebox "+cssFlat+"'>")
                )
                .append($("<span> </span>"))
                ;

                break;

            default:
            case "normal":
                //console.log("e-1111-e");

                if (esReadOnly) {
                    textIfDisabled = " readonly ";
                }

                //console.log("e-1111-d");
                campos = $("<span></span>").append(
                $("<input type='text' value='"+param1+"'  "+textIfDisabled+" size='8' id='param_filtro_"+newid+"' class='cambiosMuestraRecargar insidebox serializarpost "+cssFlat+"'>")
            );
                //console.log("e-1111-c");
                break;
        }

        //console.log("e-a1");
        $("#filtrosfiltros").append(
        $("<span class='"+especialcss+"'></span>").attr("id","filtrosf_"+newid)
        .html(" &nbsp; "+trans[newid])
        .addClass("itemfiltro")
        .attr("rel",newid)
        .append(campos).append(cerrar)
        ).append(document.createTextNode(" "));

        //console.log("e-1111");
        //cambiosMuestraRecargar
        ventana.bind_cambiosMuestraRecargar();
};

    ventana.bind_cambiosMuestraRecargar = function() {
        $(".datepickerme",$("#filtrosfiltros")).datepicker();

        $(".cambiosMuestraRecargar",$("#filtrosfiltros")).keyup(function(){
            filtros.modificado();
            ventana.sugiereReenviar(true,po_recargar);
        });

        $(".cambiosMuestraRecargar",$("#filtrosfiltros")).change(function(){
            filtros.modificado();
            ventana.sugiereReenviar(true,po_recargar);
        });
    };

    ventana.muestraAgrupar =function(newid){
        if(!newid)return;
        if(newid.length<2) return;

        var cerrar;


        if(!ventana.x_visible)
            cerrar = $("<span></span>");
        else
            cerrar = $("<a href='#' class='closex'  alt='Quitar' title='Quitar'>x</a>").click(function(){
            filtros.eliminarAgrupamiento(newid);
            $("#agrupar_"+newid).remove();
            ventana.sugiereReenviar(true,po_recargar);
        });

        //$("#filtrosagrupados").append($("<span>").attr("id","agrupar_"+newid).html(" &nbsp; "+trans[newid]).addClass("itemfiltro draggable").append(cerrar) );
        $("#filtrosagrupados").append($("<span>").attr("id","agrupar_"+newid).html(" &nbsp; "+trans[newid]).addClass("itemfiltro").append(cerrar) );
    };


    ventana.muestraSubtotal=function(newid){
        if(!newid)return;
        if(newid.length<2) return;
        
        var cerrar;

        if(!ventana.x_visible)
            cerrar = $("<span></span>");
        else
            cerrar = $("<a href='#' class='closex'  alt='Quitar' title='Quitar'>x</a>").click(function(){
            filtros.eliminarSubtotal(newid);
            $("#subtotal_"+newid).remove();
            ventana.sugiereReenviar(true,po_recargar);
        });
        

        $("#filtrosubtotales").append($("<span>").attr("id","subtotal_"+newid).html(" &nbsp; "+trans[newid]).addClass("itemfiltro").append(cerrar) );
    };

    /* ---------------------- */

    /* Mantenimiento del grafico de barra de progreso que indica que se estan cargando datos */

    ventana.muestraBarraCarga = function(){
        $("#ajaxbar").removeClass("oculto");
        $("#ajaxbar").show();
    };

    ventana.ocultaBarraCarga = function(){
        $("#ajaxbar").hide();
    };
    /* ---------------------- */


    /*
     * Peticion para mostrar datos
     * @param: tipo,  indica que tipo de listado se quiere descargar
     */
    ventana.muestraListado =    function(tipo,parametrostipo){

        if(!tipo) {
            console.log(" ventana.muestraListado: no se especifico tipo, se asume 'cogedatos'");
            tipo = "cogedatos";
        }

        $("#mensajecargando").html("Cargando...");

        if(ventana.necesitaRecargarPagina){
            ventana.necesitaReenviar();
            return;
        }

        cachePaginas.vaciar();//Nuestro cache es invalido

        var paginaPeticion = paginaActual();
        $("#offsetvalue").val(paginaPeticion);//que "pagina" se solicita

        $("#modoform").val(tipo);
        ventana.serializarParametros();
        ventana.sugiereReenviar(true,po_cargando);

        setTimeout("ArreglarColumnas()",0);

        //Modifica el tipo de peticion, si es necesario.
        if(tipo=="cogedatos"){
            $("a.eligeSuma").removeClass("eligeSumaSeleccionado");
            cachePaginas.setmodo("cogedatos");
        } else {
            if (tipo=="cogegrupo"){
                cachePaginas.setmodo("cogegrupo");
            }
        }

        $("#paginaCargando").val(paginaPeticion);//que "pagina" se solicita

        cachePaginas.barra(true);//muestra la barra de "progreso"
        $.ajax({
            type: 'POST',
            url: 'modvisorreporting.php',
            data: $('#reporting').serializeArray(),
            async: true,
            success: function(data){
                cachePaginas.add(paginaPeticion,data);
                cachePaginas.barra(false);


                if( $.browser.msie ){
                    $("#cajaderesultados").html(data);
                } else {
                    document.getElementById("cajaderesultados").innerHTML = data;
                }

                cachePaginas.autoJS();

                data = "";
                delete data;

                hacerDropable();
                ventana.sugiereReenviar(false);
                setTimeout("ArreglarColumnas()",0);

                var cajaDondeMeter = "cajaderesultados_subtotales";
                if( $.browser.msie ){
                    $("#"+cajaDondeMeter).html("");
                } else {
                    document.getElementById(cajaDondeMeter).innerHTML = "";
                }


                $("#modoform").val("capturatotal");
                cachePaginas.barra(true);
                $.ajax({
                    type: 'POST',
                    url: 'modvisorreporting.php',
                    data: $('#reporting').serializeArray(),
                    async: true,
                    success: function(data){
                        cachePaginas.barra(false);

                        if( $.browser.msie ){
                            $("#"+cajaDondeMeter).html(data);
                        } else {
                            document.getElementById(cajaDondeMeter).innerHTML = data;
                        }



                        data = "";
                        delete data;

                        hacerDropable();
                        ventana.sugiereReenviar(false);

                        cachePaginas.autoJS();

                        setTimeout("ArreglarColumnas()",0);
                    }
                });
            }
        });

    };


    /*
     * Gestiona el boton que indica si se esta en proceso de carga/recargar es deseable
     */
    ventana.sugiereReenviar = function(mostrar,mensaje){
        $("#botonRecargarVoluntario").attr("value",mensaje);
        $("#botonRecargarVoluntario").removeClass("oculto");

        if(mostrar){
            $("#botonRecargarVoluntario").show();
        } else {
            $("#botonRecargarVoluntario").hide();
        }
    };

    /*
     * Prepara los datos para ser enviados al servidor
     */
    ventana.serializarParametros = function(){
        $("#icolumnas").val( filtros.getenUso().join(","));
        $("#iagrupamientos").val( filtros.getAgrupar().join(","))
        $("#ifiltros").val( filtros.getFiltros().join(","))
        $("#isubtotales").val( filtros.getSubtotal().join(","))

        filtros.serializa_modosuma();


        $(".temporalParaSerializar").remove();

        //console.log("Reciclamos viejos 'temporalParaSerializar' ");
        $(".serializarpost").each(function(){


            var atribid = $(this).attr("id");
            var valor = $(this).val();

            if( $("#data_" +atribid).length >0 ){
                //si ya existe,solo actualiza.
                $("#data_" +atribid).val(valor);
                return;
            }

            if($(this).hasClass("serializaGrupos")){
                //console.log("Requiere reagrupamiento:atribid"+atribid+",valor:"+valor);
                //$($this)optgroup

                var t = 0;
                $(".filtra option",this).each(function(){
                    valor = $(this).html();//probar con val
                    valor2 = $(this).val();

                    if(valor!="__ignorar__" && valor2!="__ignorar__"){
                        var newname = atribid+"_"+t;

                        //console.log("Creando multiple:"+newname+",valor:"+valor+",valor2:"+valor2);

                        if( $(newname).length>0 ){ //si ya existe, lo reusamos
                            $(newname).val( valor );
                        } else { //si no existe, creamos un nuevo contenedor
                            $("#reporting").append(

                            $("<input type='hidden'>")
                            .   attr("name",newname)
                            .   attr("value",valor )
                            .   addClass("temporalParaSerializar") //queda marcado para eliminar
                            .   attr("id","data_" +atribid+"_"+t  )
                        );
                        }
                        t++;
                    }
                });

                return;
            }

            $("#reporting").append(
            $("<input type='hidden'>")
            .   attr("name",atribid)
            .   attr("value",valor )
            .   attr("id","data_" +atribid  )
        );
        });

    };

    ventana.necesitaReenviar = function(){
        $("#modoform").val("autoenvio");

        ventana.sugiereReenviar(true,po_enviando);
        ventana.serializarParametros();

        setTimeout(function(){
            $("#reporting").submit();
        },10);
    }


    ventana.intentaEliminarColumna = function(newid){
        console.log("Eliminando columna:newid:"+newid);

        if (!_.include(filtros._enUso,newid)){
          //Ya se ha eliminado
          return;
        }

        if(filtros.existeAgrupar(newid)){
            //TODO: avisar usuario
            console.log("No se elimino la columna newid"+newid+", porque existe en agrupar");

            AvisarUsuario("No se puede eliminar columnas utilizadas en <b>agrupar</b>");


            return;
        }

        destruirColumna(newid);
        filtros.eliminarColumna(newid);

        //console.log("cc:"+newid);

        ventana.sugiereReenviar(true,po_recargar);
        //filtros.necesitaRecargar(true);//necesita recargar toda la pagina
    };



}); // class ventana y class filter

$(function(){

   ventana._cargaEnSucio = [];

   ventana.set_cargaSucia = function(newid,modo){
       var esta = _.include(ventana._cargaEnSucio,newid);
       var Sucio = modo;

       if(esta && Sucio) return;

       if(!esta && Sucio) {
           ventana._cargaEnSucio.push(newid);
           return;
       }

       if(esta && !Sucio) { //quitar de avg
           try {
            var index = $.inArray(newid,ventana._cargaEnSucio);
            //delete ventana._cargaEnSucio[index];
            ventana._cargaEnSucio.splice(index,1);
           }catch(e){
            //TODO: ie puede producir este error
           }


           return;
       }
   };

   ventana.esCargaSucia = function(){
        return  ventana._cargaEnSucio.length>0;
   };

});



$(function(){


    /*
     * Intenta añadir una nueva columna, con todos los bindings y demas
     */
    ventana.construirColumna  = function( columna, newid ) {
        console.log("construirColumna:"+columna+",newid:"+newid);

        if(columna<0) return;
        if(_.include(filtros._enUso,newid)) return;


        function decora_th(th,newid){
            var a = document.createElement("a");
            var nombre = trans[newid];

            $(th).addClass("cajacabeza_columna")
            .attr("valing","center");

            $(a).addClass("cabeza_columna","draggable")
            .attr("id","cabeza_"+newid)
            .attr("rel",newid)
            .html(nombre);

            $(th).append(a);
        }

        function decora_icons(th,newid){
            var id = "";

            $(th).attr("id","icons_"+newid);
            $(th).attr("valign","center");

            $(th).append(document.createTextNode(" "));


            id = " id='icondelete_"+newid+"' ";
            var img1 = $('<img src="icons/delete.png" '+id+' class="clickme iconito" data-tipo="delete" data-newid="'+newid+'" >');
            $(img1).attr("data-newid",newid);
            $(th).append(img1);

            $(th).append(document.createTextNode(" "));

            id = " id='iconnormal_"+newid+"' ";
            var img21 = $('<img src="icons/1downarrow2.gif"  '+id+'  class="clickme iconito  iconitosort" data-tipo="down"  data-newid="'+newid+'" data-normal="true" >');
            $(img21).attr("data-newid",newid);
            $(th).append(img21);

            $(th).append(document.createTextNode(" "));

            id = " id='iconup_"+newid+"' ";
            var img2 = $('<img src="icons/1downarrow.png" '+id+'  style="display:none" class="clickme iconito iconitosort" data-tipo="up"  data-newid="'+newid+'" >');
            $(img2).attr("data-newid",newid);
            $(th).append(img2);

            $(th).append(document.createTextNode(" "));

            id = " id='icondown_"+newid+"' ";
            var img3 = $('<img src="icons/1uparrow.png"  '+id+'  style="display:none" class="clickme iconito iconitosort" data-tipo="normal" data-newid="'+newid+'">');
            $(img3).attr("data-newid",newid);

            $(th).append(img3);

            $(th).append(document.createTextNode(" "));

            id = " id='iconsum_"+newid+"' ";
            var img31 = $('<img src="icons/sum.gif"  '+id+' class="clickme iconito" data-tipo="sum" data-newid="'+newid+'">');
            $(img31).attr("data-newid",newid);

            $(th).append(img31);

            $(th).append(document.createTextNode(" "));

            id = " id='iconave_"+newid+"' ";
            var img32 = $('<img src="icons/average2.gif" '+id+'  style="display:none" class="clickme iconito" data-tipo="ave" data-newid="'+newid+'">');
            $(img32).attr("data-newid",newid);

            $(th).append(img32);

            $(th).append(document.createTextNode(" "));


            id = " id='iconmas_"+newid+"' ";
            var img4 = $('<img src="icons/plusicon.gif" '+id+'  class="clickme iconito" data-tipo="mas" data-newid="'+newid+'">');
            $(img4).attr("data-newid",newid);

            $(th).append(img4);


        }

        var th = document.createElement("th");
        decora_th(th,newid);

        $(th).insertBefore($("#lista_columnas th").eq(columna));


        th = document.createElement("td");
        decora_icons(th,newid);
        $(th).insertBefore($("#lista_columnas_icons td").eq(columna));

        console.log("se añadio th(test)");

        var $rows = $("#cajaderesultados tr");
        var td;
        var cuantasColumnas = false;
        var insertarFinal = false;

        try {
            cuantasColumnas = $rows.eq(0).find("td").length;
        } catch(e){

        }

        if ( cuantasColumnas && cuantasColumnas==columna){
            //se quiere insertar al final.
            insertarFinal = true;
        }



        $rows.each(function(){
                td = document.createElement("td");

                 if (insertarFinal)
                    $(this).append(td);
                 else
                    $(td).insertBefore($("td",this).eq(columna));
        });

        $rows = $("#cajaderesultados_subtotales tr");

        $rows.each(function(){
                td = document.createElement("td");

                if (insertarFinal)
                    $(this).append(td);
                 else
                    $(td).insertBefore($("td",this).eq(columna));
        });


        $("#icolumnas").val(filtros._enUso.join(","))
        cachePaginas.vaciar();

        $("#colpaleta input[rel="+newid+"]").addClass("itemenuso");


        ventana.createBindingsIconitos();//hace iconitos clickeables
        hacerArrastrable();
        ArreglarColumnas();

        filtros.iconitosvisible();//oculta los iconitos inapropiados
    }

});


    function destruirColumna( newid ) {
        console.log("destruirColumna: Tenemos columna en dropable,nid:"+newid);

        var columnaMover = $(".cabeza_columna").index($("#cabeza_"+newid));

        if(columnaMover<0) return;

        $("#lista_columnas th").eq(columnaMover).remove();

        var $rows = $("#cajaderesultados tr");

        $rows.each(function(){
                $("td",this).eq(columnaMover).remove();
        });

        $rows = $("#cajaderesultados_subtotales tr");

        $rows.each(function(){
                $("td",this).eq(columnaMover).remove();
        });

        $("#icons_"+newid).remove();
        console.log("newid:"+newid);
        ArreglarColumnas();
    }



function hacerArrastrable(){

    var limites= {
        agrupar_y:114,
      //  subtotal_y:132,
        filtros_y:170
    };


    ventana.evento_agnadirfiltrosagrupados_caja = function(newid){


        if(!columnas_id2tipo.agrupable(newid)){
            AvisarUsuario("No se puede agrupar por este tipo de columna.");
            return;
        }


        if( jQuery.inArray(newid, filtros.getAgrupar())<0){ //es nuevo filtro (sino, lo ignora)
            filtros.agnadirAgrupar(newid);
            ventana.muestraAgrupar(newid);

            $(".itemfiltro",$("#solucionesfiltros")).corner("4px");

            filtros.agnadirColumna(newid,-1);//añadimos este filtro a las columnas que se visualizaran
            ventana.sugiereReenviar(true,po_recargar);

            
        }else {
            filtros.flasea("agrupar",newid);
        }
    };

    function filtrosagrupados_caja(evento,draggable ) {
        var newid = $(draggable).attr("rel")+"";

        if(!newid || newid == "undefined") return;

        ventana.evento_agnadirfiltrosagrupados_caja(newid);
    }


    function filtrosubtotales_caja( evento,draggable ) {
        var newid = $(draggable).attr("rel")+"";

        console.log("filtrosubtotalescaja="+newid);
        if(!newid) return;

        if(jQuery.inArray(newid, filtros.getSubtotal())<0){

            filtros.agnadirSubtotal(newid);
            ventana.muestraSubtotal(newid);
            ventana.sugiereReenviar(true,po_recargar);

            $(".itemfiltro",$("#solucionesfiltros")).corner("4px");
        }else {
            filtros.flasea("subtotal",newid);
        }
    }


    function filtrosfiltros_caja( evento, draggable ) {
        var newid = $(draggable).attr("rel")+"";

        console.log("Tenemos sugerencia de filtro"+newid);


        if(!newid) return;

        if(jQuery.inArray( newid,filtros.getFiltros() )<0){
            filtros.agnadirFiltro(newid);
            ventana.muestraFiltro(newid);
            filtros.agnadirColumna(newid,-1);
            ventana.sugiereReenviar(true,po_recargar);
            //if(!$.browser.webkit)
            $(".itemfiltro",$("#solucionesfiltros")).corner("4px");
        }else {
            filtros.flasea("filtrosf",newid);

            if(jQuery.inArray(newid, filtros._filtrosListaReadOnly)>-1){
                AvisarUsuario("El filtro ya existe, y es obligatorio. Los filtros obligatorios no se muestran.");
            }

            console.log("ya existe"+newid);
        }
    }


    function calculaColumna(clickx){
        var columnas = $("#lista_columnas th");
        if(!columnas) return;


        var len = columnas.length;
        var tamahora=0;//columna izquierda
        var columnaMoverTo = -1;
        for(var t=0;t<len;t++){
            try {
                tamahora+= $(columnas[t]).width();
                //console.log(tamahora + ", col:"+t);

                if(clickx < tamahora){
                    //console.log("encontrada:"+t+",tamhora"+tamahora+",preelige:"+t)
                    columnaMoverTo = t;
                    break;
                }
            }catch(e){
                console.log(e);
            }
        }

        return columnaMoverTo;
    }

    /*
     * Calcula la posicion mas cerca de insercion, utilizando el centro de una columna para decidir el punto de inserción anterior o posterior.
     */
    function calculaMediaColumna(clickx){
        var columnas = $("#lista_columnas th");
        var len = columnas.length;
        var tamahora=ventana.estadoToggle?240:0;//columna izquierda
        var columnaMoverTo = -1;
        var anchoColumnaActual = 0;

        for(var t=0;t<len;t++){
            try {
                anchoColumnaActual = $(columnas[t]).width();
                tamviejo = tamahora;
                tamahora += anchoColumnaActual;
                //console.log(tamahora + ", col:"+t);

                if(clickx < (tamviejo + anchoColumnaActual/2)){
                    columnaMoverTo = t;
                    break;
                }
            }catch(e){
                console.log(e);
            }
        }

        return columnaMoverTo;
    }

    function movercolumnas( event, draggable ) {
        var newid = $(draggable).data("code");

        console.log("movercolumnas: Tenemos columna en dropable, newid:"+newid);
        console.log("rel:"+ $(draggable).attr("rel"));

        //console.dir(draggable);
        // console.log(xinspect(draggable));

        gorbal = draggable;


        //reorganizacion columnas
        if($(draggable).hasClass("cabeza_columna")){
            //console.dir(draggable);

            var rel = $(draggable).attr("rel");
            var columnaMover = $(".cabeza_columna").index($("#cabeza_"+rel));
            var clickx = event.pageX;

            $("#labelTotal").remove();//mover esta etiqueta seria raro, asi que la eliminamos.

            var columnaMoverTo = calculaColumna(clickx);

            //alert("cMT:"+columnaMoverTo);

            if(columnaMoverTo>=0 && columnaMover!=columnaMoverTo){

                var nth_old = columnaMover;
                var nth_new = columnaMoverTo;


                if(nth_new > nth_old )
                    $("#lista_columnas th").eq(nth_old).insertAfter($("#lista_columnas th").eq(nth_new));
                else
                    $("#lista_columnas th").eq(nth_old).insertBefore($("#lista_columnas th").eq(nth_new));

                if(nth_new > nth_old )
                    $("#lista_columnas_icons td").eq(nth_old).insertAfter($("#lista_columnas_icons td").eq(nth_new));
                else
                    $("#lista_columnas_icons td").eq(nth_old).insertBefore($("#lista_columnas_icons td").eq(nth_new));


                /*
                 * Ahora vamos a reordenar la caja de resultados.
                 */
                var $rows = $("#cajaderesultados tr");

                $rows.each(function(){
                    if(nth_new > nth_old )
                        $("td",this).eq(nth_old).insertAfter($("td",this).eq(nth_new));
                    else
                        $("td",this).eq(nth_old).insertBefore($("td",this).eq(nth_new));
                });


                /*
                 * Ahora reordenamos subtotales
                 */
                $rows = $("#cajaderesultados_subtotales tr");

                $rows.each(function(){
                    if(nth_new > nth_old )
                        $("td",this).eq(nth_old).insertAfter($("td",this).eq(nth_new));
                    else
                        $("td",this).eq(nth_old).insertBefore($("td",this).eq(nth_new));
                });

                cachePaginas.vaciar();
                filtros.trasponerColumnas(nth_old,nth_new);

            } else {
                //console.log("no encontrada columna");
            }

            return;
        }



        if(!newid) return;

    }

    $(".cabeza_columna").addClass("draggable");

    var arrastrando = false;


    function drag_termina(event, ui){
            var newid;

            //event.stopPropagation();
            //alert(event.pageX);

            flag_dragging = false;
            console.log("hacerArrastrable.draggable.endDrag");

            var itemdepaleta = (ui &&  $(ui).hasClass("itemdepaleta"));
            var columnacabeza = (event && event.target && $(event.target).hasClass("cabeza_columna"));

            var pasar = ui;

            if($.browser.msie)
                pasar = event.target;

            if (columnacabeza && event.pageX<140){
                newid =  $(event.target).attr("rel");

                ventana.intentaEliminarColumna(newid);

                event.stopPropagation();
                //alert(1);
                return;
            }

            if(event.pageY<limites.agrupar_y){
                filtrosagrupados_caja(event,pasar);
                event.stopPropagation();
                //alert(2);
                return;
            } /* else if (event.pageY<limites.subtotal_y)  {
            filtrosubtotales_caja(event,ui);
            event.stopPropagation();

            console.log("c:1044");
            return;
        } */else if (event.pageY<limites.filtros_y)  {
                filtrosfiltros_caja(event,pasar);
                event.stopPropagation();

                //alert(2);
                //console.log("c:1050");
                return;
            } else if ( event.pageX>140 && !itemdepaleta){
                //console.log("eX"+event.pageX+",eY:"+event.pageY);
                //console.log("uXY"+ultimaXY);

                if((event.pageX+event.pageY)==ultimaXY) {
                    //console.log("ya conozco este evento"+ultimaXY);
                    return;
                }
                ultimaXY = event.pageX + event.pageY;

                event.stopPropagation();

                console.log("event,ui");
                //
                console.log(event);
                console.log(ui);
                //console.dir(ui);
                //target

                if($.browser.msie)
                    movercolumnas(event,event.target);
                else
                    movercolumnas(event,pasar);

                event.stopPropagation();

                //console.log("c:1067");
                return;
            }
            else if(itemdepaleta && event.pageX>220 ) {
                newid =  $(ui).attr("rel");

                var columnaMoverTo = calculaMediaColumna(event.pageX);

                var modificado = filtros.agnadirColumna(newid,columnaMoverTo);

                if(modificado) {
                    ///console.log("Se relanza con las nuevas columnas")
                    if(0)ventana.muestraListado();//fuerza el recargamiento con la columna modificada
                }

                event.stopPropagation();
                //console.log("nueva posicion de newid:"+newid+",cMT:"+columnaMoverTo);
                //console.log("Columnas:"+filtros._enUso);
                //console.log("c:1089");
                //alert(4);
                return;
            } else {
                //console.log("Evento de arrastrar no cae en ningún sitio logico:eX:"+event.pageX+",eY:"+event.pageY);
            }

            //alert(5);
            //console.log("c:1087");
        }

    $( ".draggable" ).draggable({
        helper: "clone",
        cursor: "move",
        cancel: false,//soluciona el problema con botones no dragables
        hoverClass: "sueltaaqui",
        endDrag: drag_termina,
        stop: drag_termina
    });

}

var ultimaXY=0;

function hacerDropable(){
    hacerArrastrable();
}




/*
 * Funciones de mantenimiento de arranque
 */

$(function() {
    console.log("----------- Empieza pagina ----------------");

    tablaOriginal = $('#tablaresultados').clone();
    ventana.reconstruirFiltros();


    function agnadirItemPaleta(item){

        if(!item){
            console.log("agnadirItemPaleta:Error: se quiso añadir item nulo");
            return;
        }

        if(!item.id){
            console.log("agnadirItemPaleta:Error: se quiso añadir item con id nulo:");
            console.dir(item);
            return;
        }


        var $linea  = $(document.createElement("input"));

        var extraclass = " "+ (item.libre?"libre":"itemenuso");

        $linea.attr("type","button");
        $linea.addClass("itemdepaleta ocupado linear draggable ui-widget-content "+extraclass);
        $linea.val(item.nombre);

        $linea.attr("rel",item.id)
        .   data("code",item.id)
        .   addClass("linear");

        $("#paleta").append($linea);
    }

    /*
     * Primero ponemos las columnas en uso
     *
     */

    var columnasActual = filtros.getColumnas();
    var t,item,libre;
    for(t=0;t<columnas.length;t++){
        item = columnas[t];
        libre = jQuery.inArray(item.id, columnasActual )<0;
        if(libre){
            continue;
        }
        if(!item.id) continue;

        item.libre = false;

        if(item.tipo!="oculto")
            agnadirItemPaleta(item);
    }

    /*
     * Despues ponemos las columnas sin usar
     */
    for(t=0;t<columnas.length;t++){
        item = columnas[t];
        libre = jQuery.inArray(item.id, columnasActual )<0;

        if(!libre){
            continue;
        }
        if(!item.id) continue;

        item.libre = true;

        if(item.tipo!="oculto")
            agnadirItemPaleta(item);
    }

    hacerArrastrable();

});


/*
 * Varios eventos para botones
 */

$(function() {


    /* Si hay un informe que borrar, mostrar boton de borrado (papelera) */
    if(ventana.id_informe>0  && !ventana.informe_sololectura){
        var img = creatBotonBorrarInforme();

        $("#nombreListado").append(img);
    }


    $("#salvareporte").click(function(){
        var sugerencia =  $("#nombreListado").text();

        var nombre_para_informe = prompt(po_entrenombreinforme,sugerencia);

        if(!nombre_para_informe) return;

        $("#nombre_para_informe").val(nombre_para_informe);
        $("#modoform").val("savereport");
        ventana.serializarParametros();

        $.ajax({
            type: 'POST',
            url: 'modvisorreporting.php',
            data: $('#reporting').serializeArray(),
            dataType: "json",
            async: true,
            success: function(data){
                if(!data) return;

                if(data["ok"]){
                    var li = $("<option>");
                    li.html(nombre_para_informe);
                    li.attr("value",data.id);

                    $("#listaReportesUsuario").append(li);
                }
            }
        });
        return false;
    });


    $("#accion_guardar").click(function(){
        console.log("guardando")
        $("#modoform").val("descargar");

        ventana.serializarParametros();


        $("#reporting").attr("target","descargando");
        $("#reporting").submit();
        $("#reporting").removeAttr("target");
    });

    $("#accion_imprimir").click(function(){
        console.log("imprimiendo")
        $("#modoform").val("imprimir");

        $("#imprimir").load(function(){
            console.log("Se ha imprimido algo!");
        });


        ventana.serializarParametros();

        $("#reporting").attr("target","imprimir");
        $("#reporting").submit();
        $("#reporting").removeAttr("target");
    });

    $("#accion_toexcel").click(function(){
        console.log("guardando")
        $("#modoform").val("toexcel");

        ventana.serializarParametros();

        $("#reporting").attr("target","toexcel");
        $("#reporting").submit();
        $("#reporting").removeAttr("target");
    });


    $("#accion_reinicio").click(function(){
        document.location = "modvisorreporting.php?modo=inicial-limpio&r="+Math.random();
    });

    $("#listaReportesUsuario").change(function(){
        var id = $(this).val();
        if(!id) return;
        document.location = "modvisorreporting.php?modo=loadreport&id="+id;
    });


});



/*
 * Filtro: ayuda a buscar paletas mediante subcadenas
 */

$(function() {
    var $paletas = $("#paleta input");

    var valorActual = decodeURIComponent($.cookie("buscacampo"));

    if (!valorActual || valorActual=="null")
        valorActual = "";

    $("#buscacampo").val( valorActual );

    var accentsTidy = function(s){
        var r=s.toLowerCase();
        r = r.replace(new RegExp("ñ", 'gi'),"n");
        r = r.replace(new RegExp("\\s", 'gi'),"");
        r = r.replace(new RegExp("[àáâãäå]", 'gi'),"a");
        r = r.replace(new RegExp("[èéêë]", 'gi'),"e");
        r = r.replace(new RegExp("[ìíîï]", 'gi'),"i");
        r = r.replace(new RegExp("í", 'gi'),"gi");
        r = r.replace(new RegExp("[òóôõö]", 'gi'),"o");
        r = r.replace(new RegExp("[ùúûü]", 'gi'),"u");
        r = r.replace(new RegExp("æ", 'gi'),"ae");
        r = r.replace(new RegExp("ç", 'gi'),"c");
        r = r.replace(new RegExp("œ", 'gi'),"oe");
        r = r.replace(new RegExp("[ýÿ]", 'gi'),"y");
        r = r.replace(new RegExp("\\W", 'gi'),"");
        return r;
    };

    $("#buscacampo").keyup(function(){
        $.cookie("buscacampo", encodeURIComponent($(this).val()));

        var comparaCon = $(this).val()+"";
        comparaCon = accentsTidy(comparaCon);

        if(comparaCon.length<1){
            $("#paleta input").show();
            return;
        }

        $paletas.each(function(){
            var contenido = accentsTidy($(this).val())+"";
            var compara = contenido.indexOf(comparaCon);

            if( compara<0){
                $(this).hide();
            } else {
                $(this).show();
            }

            //console.log("c:"+contenido+",comparaCon:"+comparaCon+",compara:"+compara);
        });

        //console.log(",comparaCon:"+comparaCon);
    });




    $("#buscacampo").keyup();

});


/*
 * El evento no le llega, porque el de arrastrar se lo come.
 *
$(function(){
//cabeza_columna
$("#cabeza_columna").dblclick(function(){
   console.log("#cabeza_columna:dblclick");
   alert("DBL click!");
});
});
 */



/*
 * Mantiene el listado de filas con una altura lo mas grande posible dado una altura de pantalla
 */

$(function() {
    function AjustarLayout(){
        var padarriba = 252;
        //var alturaventana = $(window).height() - 30;
        //var alturaventana = $(window).height() - 90;
        //var alturaventana = $(window).height() - 150;
        var alturaventana = $(window).height() - 165;

        var contamos = alturaventana-padarriba;
        contamos = contamos>0?contamos:0;

        if(contamos && contamos>100){
            $("#contenedorcajaresultados").css("height",contamos + "px");
        }

        ArreglarColumnas();
    }

    AjustarLayout();
    //$(window).resize($.debounce(100,AjustarLayout));
    $(window).resize($.debounce(250,AjustarLayout));
});


/*
 * Bindings de boton para ocultar columna
 */
$(function(){
   ventana.estadoToggle = true;

   $("#toggleColpaleta").click(function(){

      ventana.estadoToggle = (ventana.estadoToggle)?false:true;


      ArreglarColumnas_limpia();
      $('#colpaleta').toggle(ventana.estadoToggle);

      $("#fila2debug").toggle(ventana.estadoToggle);

      ArreglarColumnas();
   });

});



/*
 * Gestiona las columnas que se han solicitado que utilicen AVG en lugar de SUM.
 */

$(function(){

   filtros._modosSumas = []; //tiene los campos que requieren avg


   filtros.set_modosuma = function(newid,modo){ //hace el mantenimiento de "_modosSumas

       var esta = _.include(filtros._modosSumas,newid);
       var hacerAvg = (modo == "ave");

       if(esta && hacerAvg) return;

       if(!esta && hacerAvg) {
           filtros._modosSumas.push(newid);
           return;
       }

       if(esta && !hacerAvg) { //quitar de avg
           var index =  $.inArray(newid,filtros._modosSumas);
           //delete filtros._modosSumas[index];
           filtros._modosSumas.splice(index,1);
           return;
       }

       /* !noesta && !hacerAvg --- esta todo ok, no hacer nada*/
       return;
   };

   filtros.reset_modosuma = function(){
       filtros._modosSumas = [];
   };

   filtros.serializa_modosuma = function(){
        var data = filtros._modosSumas.join(",");

        //console.log("serializa_modosuma:data:"+data);
        $("#avgcolumnas").val(data);
   };



});




/*
 * Gestion de los bindings de los "iconitos".
 */

$(function(){
    ventana.createBindingsIconitos = function(){

        $(".iconito",$("#lista_columnas_icons")).each(function(){



            $(this).click(function(event){
                var tipo = $(this).attr("data-tipo");
                var newid = $(this).attr("data-newid");
                var modo = cachePaginas.getmodo()

                console.log("iconito:modo:"+modo+",tipo:"+tipo);

                function arreglaIconitos(){
                    var esEste = $(this).attr("data-newid")==newid;
                    var esNormal = $(this).attr("data-normal")=="true";

                    if(!esEste){
                        if(!esNormal) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    } else {
                        $(this).hide();
                    }
                }


                switch(tipo){
                    case "delete":
                        ventana.intentaEliminarColumna(newid);
                        break;
                    case "normal":
                        $("#ordenarpor").val("");
                        $("#ordenarpor_direccion").val("");

                        $(".iconitosort").each(arreglaIconitos);

                        $("#iconnormal_"+newid).show();
                        cachePaginas.vaciar();
                        ventana.muestraListado(cachePaginas.getmodo());

                        break;
                    case "sum":
                        $(this).hide();
                        $("#iconave_"+newid).show();

                        filtros.set_modosuma(newid,"ave");
                        ventana.sugiereReenviar(true,po_recargar);
                        break;
                    case "ave":
                        $(this).hide();
                        $("#iconsum_"+newid).show();

                        filtros.set_modosuma(newid,"sum");
                        ventana.sugiereReenviar(true,po_recargar);
                        break;
                    case "up":
                        $("#ordenarpor").val(newid);
                        $("#ordenarpor_direccion").val("up");

                        $(".iconitosort").each(arreglaIconitos);
                        $("#icondown_"+newid).show();

                        cachePaginas.vaciar();
                        ventana.muestraListado(cachePaginas.getmodo());
                        break;

                    case "acum":

                        var current = $("#campos_acumulador").val();

                        if(current == newid){
                           $(".acumtoggle").attr("src","icons/biger.png");
                           $("#campos_acumulador").val("");
                            cachePaginas.vaciar();
                            ventana.muestraListado(cachePaginas.getmodo());
                           return;
                        }



                        $("#campos_acumulador").val(newid);

                        $(".acumtoggle").attr("src","icons/biger.png");

                        $(this).attr("src","icons/biger_lux.png");


                        cachePaginas.vaciar();
                        ventana.muestraListado(cachePaginas.getmodo());
                        break;
                    case "down":
                        $("#ordenarpor").val(newid);
                        $("#ordenarpor_direccion").val("down");


                        $(".iconitosort").each(arreglaIconitos);

                        $("#iconup_"+newid).show();

                        cachePaginas.vaciar();
                        //ventana.sugiereReenviar(true,po_recargar);
                        ventana.muestraListado(cachePaginas.getmodo());
                        break;
                     case "mas":

                        ventana.evento_agnadirfiltrosagrupados_caja(newid);

                        break;
                    default:
                        console.log("createBindingsIconitos:tipo:"+tipo+",newid:"+newid);
                        break;
                }

                event.stopPropagation();
            });
        });
    };

    ventana.createBindingsIconitos();
});


/*
 * Caracteristica de compartir listados con otros usuarios
 */
$(function(){

   function resetea(){
        $("#compartirlistado").val(-1);
   }


   $("#compartirlistado").change(function(){
        var modosharereport="";
        var quien = $(this).val();
        var idgrupo = 0, idusuario="";

        if(quien==-1) return;//no es una opcion, es la "zona de aparcamiento"


        var sugerencia =  $("#nombreListado").text();

        var nombre_para_informe = prompt(po_entrenombreinforme,sugerencia);

        if(!nombre_para_informe){
            resetea();
            return;
        }

        switch(quien){
            case "otrousuario":
                modosharereport = quien;
                //TODO, que usuario
                idusuario = prompt("Escriba el nombre de inicio de sesion del usuario:","");

                if(!idusuario){
                    resetea();
                    return;
                }
                $("#idmodosharereport").val(idusuario);
                break;
            case "todos":
                modosharereport = quien;
                break;
            default:
                modosharereport = "grupo";
                idgrupo = parseInt(quien,10);
                if( !(idgrupo>0) ){
                    resetea();
                    return;
                }
                $("#idmodosharereport").val(idgrupo);
                break;
        }

        $("#modosharereport").val(modosharereport);

        $("#nombre_para_informe").val("- "+nombre_para_informe);
        $("#modoform").val("savereport-share");
        ventana.serializarParametros();

        $.ajax({
            type: 'POST',
            url: 'modvisorreporting.php',
            data: $('#reporting').serializeArray(),
            dataType: "json",
            async: true,
            success: function(data){
                if(!data) return;

                if(data["ok"]){

                    if(quien=="otrousuario")
                        alert("El listado se ha compartido correctamente con "+idusuario);
                    else
                        alert("El listado se ha compartido correctamente.");
                    /*
                    var li = $("<option>");
                    li.html(nombre_para_informe);
                    li.attr("value",data.id);

                    $("#listaReportesUsuario").append(li);*/
                } else {
                    alert("No se ha podido compartir el listado.");
                }
            }
        });
        return false;
   });
});

/*
 * Mantiene claves de CSS por navegador que permiten utilizar distinto CSS segun el navegador
 */
$(function(){

    if( $.browser.mozilla ){
        $("body").addClass("navegador_mozilla");
        $("body").removeClass("navegador_otro");
    } else if ($.browser.webkit) {
        $("body").addClass("navegador_webkit");
        $("body").removeClass("navegador_otro");
    }

    if($.browser.msie){
        $("body").addClass("navegador_ie");
        $("body").removeClass("navegador_otro");
    }

    $("html").removeClass("no-js");


    $("#descargando").hide();
    $("#imprimir").hide();
    $("#toexcel").hide();

});


/*
 * Debugging
 */
$(function(){

    var ultimo = "";

    if(0)
        setInterval(function(){

            if ($("#agrupar_txt").length){
                var texto = "";

                texto = filtros._agruparLista.join(",");


                if(ultimo != texto)
                    console.log("filtros._agruparLista(modificado):"+texto) ;

                $("#agrupar_txt").html(texto);

                ultimo = texto;
            }
    },200);

    var ultimo2 = "";

    if(0)
        setInterval(function(){

            if ($("#columnas_txt").length){
                var texto = "";

                texto = filtros.getenUso().join(",");

                if(ultimo2 != texto)
                    console.log("filtros.getenUso().join:"+texto) ;

                $("#columnas_txt").html(texto);

                ultimo2 = texto;
            }
    },200);

});


//var columnas_id2tipo = {};

$(function(){

   columnas_id2tipo.trans = {};

   columnas_id2tipo.inicia = function(){

        _.each(columnas,function(item){

            columnas_id2tipo.trans[item["id"]] = item["tipo"];
            //console.log(item)
        });
   };

   columnas_id2tipo.getTipo = function(id){
        return  columnas_id2tipo.trans[id];
   };

   columnas_id2tipo.agrupable = function(id){
        var tipo = columnas_id2tipo.trans[id];

        //if(tipo=="texto") return false;
        //if(tipo=="json") return false;
        //if(tipo=="json") return false;
        if(tipo=="numero") return false;
        if(tipo=="moneda") return false;
        if(tipo=="cantidad") return false;

        return true;
   };

    columnas_id2tipo.inicia();


    filtros.iconitosvisible = function(i){
         $(".iconito",$("#lista_columnas_icons")).each(function(){
            var newid = $(this).attr("data-newid");
            var tipo = columnas_id2tipo.getTipo(newid);

            var subtipe = $(this).attr("data-tipo");

            if( ( subtipe=="sum"  || subtipe=="acum" ) &&
                (tipo=="texto" || tipo=="codigo" || tipo=="cod" || tipo=="json" || tipo=="fecha"|| tipo=="mes"|| tipo=="agno")){
                $(this).hide();
            }

            if( ( subtipe=="mas" ) &&
                (tipo=="cantidad" || tipo=="moneda" || tipo=="numero" )){
                $(this).hide();
            }

        });
    };

    filtros.iconitosvisible();


});





/*
 * funcion de debug que marca la columna destino y fuente cuando se estan arrastrando
 */
function labelizar( pageX, draggable ) {
    //var newid = $(draggable).data("code");

    //console.log("LABELIZAR");

    $("#lista_columnas th").removeClass("columnaMover");
    $("#lista_columnas th").removeClass("columnaMoverTo");

    //reorganizacion columnas
    if($(draggable).hasClass("cabeza_columna")){
        //console.log("Muy importante: tenemos columna!"+$(ui.draggable).attr("rel"));

        var $th_mueveme = $(draggable).parent();
        var $tr_columnas = $($th_mueveme).parent();
        var $columnas = $(draggable).parent().parent().find("*");
        var rel = $(draggable).attr("rel");

        $("#vdebug").val(rel);

        var columnaMover = $(".cabeza_columna").index($("#cabeza_"+rel));
        var clickx = pageX;
        var columnas = $("#lista_columnas th");
        var len = columnas.length;

        var tamahora=ventana.estadoToggle?240:0;//columna izquierda
        var columnaMoverTo = -1;
        for(var t=0;t<len;t++){
            try {
                tamahora+= $(columnas[t]).width();
                //console.log(tamahora + ", col:"+t);

                if(clickx < tamahora){
                    //console.log("encontrada:"+t+",tamhora"+tamahora+",preelige:"+t)
                    columnaMoverTo = t;
                    break;
                }
            }catch(e){
                console.log(e);
            }
        }

        if(columnaMoverTo>=0 && columnaMover!=columnaMoverTo){

            var nth_old = columnaMover;
            var nth_new = columnaMoverTo;


            $("#lista_columnas th").eq(nth_old).addClass("columnaMover")
            $("#lista_columnas th").eq(nth_new).addClass("columnaMoverTo");
        }

    }

}

/*
 * Intenta paliar una mala interferencia entre ie y jquery
 */
    var eliminaPropietariosJQuery = function(s){
        var r = new String(s);
        r = r.replace("sizcache=","sizcache_jq=");
        r = r.replace("sizset=","sizset_jq=");
        return r;
    };


/*
 * define outerHTML, necesario en algun caso para solucionar bugs de IE
 */
jQuery.fn.outerHTML = function(s) {
    return (s)? this.before(s).remove(): jQuery("<p></p>").append(this.eq(0).clone()).html();
}

/*
 * funcion de ayuda a debugging
 */
function footest(){

    //alert($("#param_filtro_D_RESUMEN_DATOS_tipo_de_pedido").html());
    //$("#param_filtro_D_RESUMEN_DATOS_tipo_de_pedido").html("<option>hola</option>")

    var $me = $("#param_filtro_D_RESUMEN_DATOS_tipo_de_pedido");

    alert($me.outerHTML());
}



