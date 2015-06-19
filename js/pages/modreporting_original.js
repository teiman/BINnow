/*
if (!window.console || !console.firebug){
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    window.console = {
            "_tttt_":1//dummy, para que el sistema de templates no rompa el codigo
    };

    for (var i = 0; i < names.length; ++i){
        window.console[names[i]] = function() {
            var t=1;//dummy, para que el sistema de templates no rompa el codigo
        }
    }
}

*/


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


//hace nombres de columnas arrastrables
function hacerArrastrable(){
    $( ".draggable" ).draggable({
        helper: "clone",
        cursor: "move",
        hoverClass: "sueltaaqui",
        start: function(event, ui){
            flag_dragging = true;
            console.log("hacerArrastrable.draggable.star")
        },
        stop: function(event, ui){
            flag_dragging = false;
            console.log("hacerArrastrable.draggable.stop")
        }
    });

    patch_chromeSeleccionTextos();
}


function hacerDropable(){
    $( ".droppable" ).droppable({
        drop: function( event, ui ) {
            var newid = $(ui.draggable).data("code");

            console.log("Tenemos columna en dropable");

            //reorganizacion columnas
            if($(ui.draggable).hasClass("cabeza_columna")){
                //console.log("Muy importante: tenemos columna!"+$(ui.draggable).attr("rel"));

                //var columnaMover = $(ui.draggable).parent().parent().find("*").index();

                //$(ui.draggable).parent().parent().find("*").addClass("tipoy")<--

                //$(ui.draggable).addClass("tipoX1");
                var $th_mueveme = $(ui.draggable).parent();
                var $tr_columnas = $($th_mueveme).parent();
                var $columnas = $(ui.draggable).parent().parent().find("*");

                var columnaMover = parseInt($columnas.index($th_mueveme)/2,10);

                //$th_mueveme.addClass("th_mueveme");
                $columnas.addClass("columnas_");
                //$tr_columnas.addClass("tr_columnas");

                //$th_mueveme.addClass("columna_origen");
                ///columnaMover2 = $tr_columnas.index($th_mueveme);
                //var columnaMover2 = $($th_mueveme).parent().parent().find("*").index();


                //console.log("cMov. Desde:"+columnaMover);

                var clickx = ui.position.left;

                var columnas = $("#lista_columnas th");
                var len = columnas.length;

                var tamahora=240;//columna izquierda
                var columnaMoverTo = -1;
                for(var t=0;t<len;t++){
                    try {
                        tamahora+= $(columnas[t]).width();
                        //console.log(tamahora + "-"+clickx);

                        if(clickx < tamahora){
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


                    if(nth_new > nth_old )
                        $("#lista_columnas th").eq(nth_old).insertAfter($("#lista_columnas th").eq(nth_new));
                    else
                        $("#lista_columnas th").eq(nth_old).insertBefore($("#lista_columnas th").eq(nth_new));


                    /*
                             * Ahora vamos a reordenar la caja de resultados.
                             *
                             */

                    var $rows = $("#cajaderesultados tr");

                    $rows.each(function(){
                        if(nth_new > nth_old )
                            $("td",this).eq(nth_old).insertAfter($("td",this).eq(nth_new));
                        else
                            $("td",this).eq(nth_old).insertBefore($("td",this).eq(nth_new));
                    });

                    $rows = $("#cajaderesultados_subtotales tr");

                    $rows.each(function(){
                        if(nth_new > nth_old )
                            $("td",this).eq(nth_old).insertAfter($("td",this).eq(nth_new));
                        else
                            $("td",this).eq(nth_old).insertBefore($("td",this).eq(nth_new));
                    });


                } else {

                //console.log("no encontrada columna");
                }

                return;
            }

            if(!newid) return;

            //console.log("[1] manejo de dropable, se añade columna");

            var modificado = filtros.agnadirColumna(newid);

            if(modificado) {
                //console.log("Mostrando listado con diferente numero de columnas")
                //console.log(filtros.getSubtotal());
                ventana.muestraListado();//fuerza el recargamiento con la columna modificada
            }
        }
    });

    patch_chromeSeleccionTextos();

    $( "#filtrosagrupados_caja" ).droppable({
        drop: function( event, ui ) {
            //var newid = $(ui.draggable).data("code");
            var newid = $(ui.draggable).attr("rel")+"";
            console.log("filtrosagrupados_caja("+newid+")");

            if(!newid) return;

            if( jQuery.inArray(newid, filtros.getAgrupar())<0){ //es nuevo filtro (sino, lo ignora)

                filtros.agnadirAgrupar(newid);

                ventana.muestraAgrupar(newid);

                $(".itemfiltro",$("#solucionesfiltros")).corner("4px");
                        
                hacerArrastrable();

                filtros.agnadirColumna(newid);//añadimos este filtro a las columnas que se visualizaran
                ventana.sugiereReenviar(true,po_recargar);
            }
        }
    });

    $( "#filtrosubtotales_caja" ).droppable({
        drop: function( event, ui ) {

            //var newid = $(ui.draggable).data("code");
            var newid = $(ui.draggable).attr("rel")+"";

            console.log("filtrosubtotalescaja="+newid);
            if(!newid) return;


            if(jQuery.inArray(newid, filtros.getSubtotal())<0){
                //console.log("actualizando enUso");
                //subtotalLista.push(newid);

                filtros.agnadirSubtotal(newid);
                ventana.muestraSubtotal(newid);
                ventana.sugiereReenviar(true,po_recargar);

                //if(!$.browser.webkit)
                $(".itemfiltro",$("#solucionesfiltros")).corner("4px");
                hacerArrastrable();
            }
        }
    });

    $("#paleta").droppable({
        drop:function(event,ui){
            //var newid = $(ui.draggable).data("code");
            var newid = $(ui.draggable).attr("rel")+"";

            console.log("paleta="+newid);


            if(!newid) return;

            if ($(ui.draggable).hasClass("itemdepaleta")){
                //No queremos que los items de la paleta interaccion consigo mismos
                return;
            }


            if( filtros.cuentaValidas( filtros.getenUso() )< 2 )
                return;

            filtros.eliminarColumna(newid);


            //Ocultamos la columna ofensiva
            var $th_mueveme = $(ui.draggable).parent();
            var $columnas = $(ui.draggable).parent().parent().find("*");

            var columnaMover = parseInt($columnas.index($th_mueveme)/2,10);


            $("#lista_columnas th").eq(columnaMover).toggle();
            var $rows = $("#cajaderesultados tr");

            $rows.each(function(){
                $("td",this).eq(columnaMover).toggle();
            });

            $rows = $("#cajaderesultados_subtotales tr");

            $rows.each(function(){
                $("td",this).eq(columnaMover).toggle();
            });

            ventana.sugiereReenviar(true,po_recargar);
            filtros.necesitaRecargar(true);//necesita recargar toda la pagina
            return;
        }
    });

    $("#sinfiltros_caja, .auxiliarsincolumnas").droppable({
        drop:function(event,ui){
            //var newid = $(ui.draggable).data("code");
            var newid = $(ui.draggable).attr("rel")+"";

            console.log("probando body")
            console.log(newid);

            if(!newid) return;

            if ($(ui.draggable).hasClass("itemdepaleta")){
                //Los items de paleta aqui no deberian hacer nada.
                return;
            }

            if( filtros.cuentaValidas( filtros.getenUso() )< 2 )
                return;

            filtros.eliminarColumna(newid);

            ventana.necesitaReenviar();// se ha eliminado una columna, es necesario feedback inmediato
            return;
        }
    });

    $( "#filtrosfiltros_caja" ).droppable({
        drop: function( event, ui ) {
            //var newid = $(ui.draggable).data("code");
            var newid = $(ui.draggable).attr("rel")+"";

            console.log("Tenemos sugerencia de filtro"+newid+",rel:"+rel);


            if(!newid) return;

            if(jQuery.inArray( newid,filtros.getFiltros() )<0){
                filtros.agnadirFiltro(newid);
                ventana.muestraFiltro(newid);
                filtros.agnadirColumna(newid);
                ventana.sugiereReenviar(true,po_recargar);
                //if(!$.browser.webkit)
                $(".itemfiltro",$("#solucionesfiltros")).corner("4px");
            }
        }
    });


    $(".cabeza_columna").draggable({
        helper: "clone",
        cursor: "move",
        hoverClass: "sueltaaqui"
    });

    hacerArrastrable();
    $(".itemfiltro",$("#solucionesfiltros")).corner("4px");

    console.log("hacerDropable: termina")
}


$(function() {

    filtros.cuentaValidas = function (arreglo){
        var validas = 0;
        for(var t=0;t<arreglo.length;t++){
            if (arreglo[t] && arreglo[t].length>0){
                validas++;
            }
        }
        return validas;
    };

    /*------------*/

    filtros.eliminarSubtotal = function(newid){
        this._subtotalLista = this._subtotalLista.filterOutValue(newid);
    };

    filtros.getSubtotal = function(){
        return this._subtotalLista;
    };

    filtros.agnadirSubtotal = function(newid){
        if(jQuery.inArray(newid, filtros._subtotalLista)<0){
            filtros._subtotalLista.unshift(newid);
            ventana.sugiereReenviar(true,po_recargar);
            filtros.necesitaRecargar(true);//necesita recargar toda la pagina
        }
    };


    /*------------*/

    filtros.eliminarAgrupar = function(newid){
        this._agruparLista = this._agruparLista.filterOutValue(newid);
    };

    filtros.getAgrupar = function(){
        return this._agruparLista;
    };

    filtros.agnadirAgrupar = function(newid){
        if(jQuery.inArray(newid, filtros._agruparLista)<0){
            filtros._agruparLista.unshift(newid);
            ventana.sugiereReenviar(true,po_recargar);
        //filtros.necesitaRecargar(true);//necesita recargar toda la pagina
        }
    };

    /*------------*/

    filtros.eliminarFiltro = function(newid){
        filtros._filtrosLista = filtros._filtrosLista.filterOutValue(newid);
    };

    filtros.getFiltros = function(){
        return filtros._filtrosLista;
    };

    filtros.agnadirFiltro = function(newid){
        if(jQuery.inArray(newid, filtros._filtrosLista)<0){
            filtros._filtrosLista.unshift(newid);
            ventana.sugiereReenviar(true,po_recargar);
        //filtros.necesitaRecargar(true);//necesita recargar toda la pagina
        }
    };

    /*------------*/

    filtros.eliminarColumna = function(newid){
        this._enUso = this._enUso.filterOutValue(newid);
    };


    filtros.getenUso = function(){
        return this._enUso;
    };

    filtros.getColumnas = function(){
        return filtros.getenUso();
    }


    filtros.agnadirColumna = function(newid){ //devuelve si se han modificado las columnas
        if(jQuery.inArray(newid, filtros._enUso)<0){
            //la columna resulta ser nueva
            //console.log("nueva,columna, probablemente habra que recargar toda la pagina")
            filtros._enUso.unshift(newid);
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
    }

    filtros.eliminarSubtotal = function(idagrupamiento){
        filtros._subtotalLista = filtros._subtotalLista.filterOutValue(idagrupamiento);
    }

    filtros.eliminarFiltrosf =  function(idagrupamiento){
        filtros._filtrosLista = filtros._filtrosLista.filterOutValue(idagrupamiento);
    }


    //NOTA: las tablas de riesgo estaran en una base de datos separada

    /*------------*/ /*------------*/ /*------------*/

    ventana.reconstruirFiltros = function(){
        var agrupar1 = filtros.getAgrupar();
        var subtotal1 = filtros.getSubtotal();
        var filtros1 = filtros.getFiltros();
        //var subtotal = filtros.getSubtotal();

        for(var t=0;t<agrupar1.length;t++){
            var newid = agrupar1[t];
            ventana.muestraAgrupar(newid);
        }

        for(var t=0;t<subtotal1.length;t++){
            var newid = subtotal1[t];
            ventana.muestraSubtotal(newid);
        }

        for(var t=0;t<filtros1.length;t++){
            var newid = filtros1[t];
            ventana.muestraFiltro(newid);
        }
    };

    /* ---------------------- */

    ventana.muestraFiltro =function(newid){

        var t;

        if(!newid)return;
        if(newid.length<2) return;

        var cerrar = $("<a href='#' class='closex'>").click(function(){
            filtros.eliminarFiltro(newid);

            $("#filtrosf_"+newid).remove();

            ventana.sugiereReenviar(true,po_recargar);
        }).html("x");

        var param1 = "";
        var param2 = "";

        var key = false;
        for(t=0;t<filtros._filtrosListaParam.length;t++){
            key = filtros._filtrosListaParam[t].nombre;

            if (newid==key){

                if(filtros._filtrosListaParam[t].param1)
                    param1 = filtros._filtrosListaParam[t].param1;

                if(filtros._filtrosListaParam[t].param2)
                    param2 = filtros._filtrosListaParam[t].param2;
                break;
            }
        }

        var item = {
            "id":0,
            "tipo":"desconocido"
        };

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


        var campos,input;


        switch(item.tipo){

            case "json":
                var idfield1 = "param_filtro_"+newid;

                campos = $("<select id='"+idfield1+"' class='cambiosMuestraRecargar serializarpost'>");                
                campos.append(  $("<option>").html(param1+"") );

                $.ajax({
                    type: 'POST',
                    url: 'modreporting.php',
                    data: {
                        "modo":"consultacombo",
                        "combo":item.id
                        },
                    dataType: "json",
                    async: true,
                    success: function(data){
                        var $option;
                        $field = $("#"+idfield1);//
                        $field.html("");

                        $field.append(  $("<option>").html(" ") );

                        for(t=0;t<data.length;t++){                            
                            $option  = $("<option>").html(data[t]);
                            if(data[t]==param1){
                                $option.attr("selected","selected");
                            }
                            $field.append($option);
                        }
                        $field.val(param1);
                    }
                });
                break;
            case "fecha":
                idfield1 = "param_filtro_"+newid+"_d";
                idfield2 = "param_filtro_"+newid+"_h";

                campos = $("<span>")
                .append($("<span> - </span>"))
                .append(
                    $("<input type='text' value='"+param1+"' size='8' id='"+idfield1+"' class='datepickerme cambiosMuestraRecargar serializarpost'>")
                    )
                .append($("<span> hasta </span>"))
                .append(
                    $("<input type='text' value='"+param2+"' size='8' id='"+idfield2+"' class='datepickerme cambiosMuestraRecargar serializarpost'>")
                    )
                .append($("<span> </span>"))
                ;
                break;

            default:
            case "normal":
                input = $("<input type='text' size='8' id='param_filtro_"+newid+"' class='cambiosMuestraRecargar serializarpost'>")
                input.val(param1);
                campos = $("<span>").append( input );
                break;
        }


        $("#filtrosfiltros").append(
            $("<span>").attr("id","filtrosf_"+newid)
            .html(" "+trans[newid])
            .addClass("itemfiltro")
            .append(campos).append(cerrar)
            );

        //cambiosMuestraRecargar
        ventana.bind_cambiosMuestraRecargar();
    };

    ventana.bind_cambiosMuestraRecargar = function() {
        $(".datepickerme").datepicker();

        $(".cambiosMuestraRecargar").keyup(function(){
            ventana.sugiereReenviar(true,po_recargar);
        });

        $(".cambiosMuestraRecargar").change(function(){
            ventana.sugiereReenviar(true,po_recargar);
        });
    };

    ventana.muestraAgrupar =function(newid){
        if(!newid)return;
        if(newid.length<2) return;

        var cerrar = $("<a href='#' class='closex'>").click(function(){
            filtros.eliminarAgrupamiento(newid);
            $("#agrupar_"+newid).remove();
            ventana.sugiereReenviar(true,po_recargar);
        }).html("x");

        $("#filtrosagrupados").append($("<span>").attr("id","agrupar_"+newid).html(" "+trans[newid]).addClass("itemfiltro draggable").append(cerrar) );
    };


    ventana.muestraSubtotal=function(newid){
        if(!newid)return;
        if(newid.length<2) return;

        var cerrar = $("<a href='#' class='closex'>").click(function(){
            filtros.eliminarSubtotal(newid);
            $("#subtotal_"+newid).remove();
            ventana.sugiereReenviar(true,po_recargar);
        }).html("x");

        $("#filtrosubtotales").append($("<span>").attr("id","subtotal_"+newid).html(" "+trans[newid]).addClass("itemfiltro").append(cerrar) );
    };

    /* ---------------------- */

    ventana.muestraListado =    function(){

        $("#mensajecargando").html("Cargando...");

        if(ventana.necesitaRecargarPagina){
            ventana.necesitaReenviar();
            return;
        }

        //$("#mensajecargando").attr("disabled","disabled");

        //recarga el listado, suponiendo que no haya columnas nuevas ni nada que exija un refresco completo.
        $("#modoform").val("cogedatos");

        ventana.serializarParametros();
        ventana.sugiereReenviar(true,po_cargando);

        $.ajax({
            type: 'POST',
            url: 'modreporting.php',
            data: $('#reporting').serializeArray(),
            async: true,
            success: function(data){
                $("#contenedorcajaresultados").html(" ");
                $(tablaOriginal).appendTo($("#contenedorcajaresultados"));

                $("#modoform").val("capturasubtotal");

                $.ajax({
                    type: 'POST',
                    url: 'modreporting.php',
                    data: $('#reporting').serializeArray(),
                    async: true,
                    success: function(data){
                        $("#cajaderesultados_subtotales").html(" ");
                        $("#cajaderesultados_subtotales").html(data);


                        $("#tablaresultados").tablesorter({
                            widgets: ['zebra']
                            });
                        hacerDropable();
                        ventana.sugiereReenviar(false);
                    }
                });

                $("#cajaderesultados").html(" ");
                $("#cajaderesultados").html(data);

            }
        });



    //},10);


    };


    ventana.sugiereReenviar = function(mostrar,mensaje){
        $("#botonRecargarVoluntario").attr("value",mensaje);
        $("#botonRecargarVoluntario").removeClass("oculto");
        if(mostrar){
            $("#botonRecargarVoluntario").show();
        } else {
            $("#botonRecargarVoluntario").hide();
        }
    };

    ventana.serializarParametros = function(){
        $("#icolumnas").val( filtros.getenUso().join(","));
        $("#iagrupamientos").val( filtros.getAgrupar().join(","))
        $("#ifiltros").val( filtros.getFiltros().join(","))
        $("#isubtotales").val( filtros.getSubtotal().join(","))


        $(".serializarpost").each(function(){
            if( $("#data_" +$(this).attr("id")).length >0 ){
                //si ya existe,solo actualiza.
                $("#data_" +$(this).attr("id")).val($(this).val() );
                return;
            }

            $("#reporting").append(
                $("<input type='hidden'>")
                .   attr("name",$(this).attr("id"))
                .   attr("value",$(this).val() )
                .   attr("id","data_" +$(this).attr("id")  )
                );
        });


    //var data = $('#reporting').serializeArray()
    //console.log("------como lo deja serializar -------")
    //console.dir(data);

    };

    ventana.necesitaReenviar = function(){
        $("#modoform").val("autoenvio");

        ventana.sugiereReenviar(true,po_enviando);

        //console.log("info, isub, v.nR");
        //console.log(filtros.getSubtotal().join(","));

        ventana.serializarParametros();



        setTimeout(function(){
            //console.log("Ahora envia el submit")

            $("#reporting").submit();
        },10);
    }

});

//$(document).ready(function() {
$(function() {


    $(function() {
        ventana.reconstruirFiltros();
    });

    console.log("----------- Empieza pagina (deberia)----------------");
    //console.log(filtros.getSubtotal());

    tablaOriginal = $('#tablaresultados').clone();

    //se llena la paleta de posibles elementos

    /*
     * Primero ponemos las columnas en uso
     *
     */

    var columnasActual = filtros.getColumnas();
    for(var t=0;t<columnas.length;t++){
        var item = columnas[t];
        var libre = jQuery.inArray(item.id, columnasActual )<0;

        if(libre){
            continue;
        }

        //var $linea = $("<li class='itemdepaleta ocupado'>").html("<a rel='"+item.id+"'>"+ item.nombre + "</a>").addClass("draggable","ui-widget-content").attr("rel",item.id).data("code",item.id).addClass("linear");

        var $linea  = $(document.createElement("input"));

        $linea.attr("type","button");
        $linea.addClass("itemdepaleta ocupado linear draggable ui-widget-content");
        $linea.val(item.nombre);

        $linea.attr("rel",item.id)
        .   data("code",item.id)
        .   addClass("linear");

        $("#paleta").append($linea);
    }

    /*
     * Despues ponemos las columnas sin usar
     */
    for(var t=0;t<columnas.length;t++){
        var item = columnas[t];
        var libre = jQuery.inArray(item.id, columnasActual )<0;

        if(!libre){
            continue;
        }

        var $linea = $("<li class='itemdepaleta'>").html("<a rel='"+item.id+"'>"+ item.nombre + "</a>").addClass("draggable","ui-widget-content").attr("rel",item.id).data("code",item.id).addClass("linear");

        $("#paleta").append($linea);
    }


    patch_chromeSeleccionTextos();


    //hace nombres de columnas arrastrables
    $( ".draggable" ).draggable({
        helper: "clone",
        cursor: "move",
        hoverClass: "sueltaaqui",
        dragOverSelector : '.receptor',
        dragOverClass : 'profileOver',
        startDrag: function(event, ui){
            flag_dragging = true;
            console.log(".draggable.startDrag")
        },
        stopDrag: function(event, ui){
            flag_dragging = false;
            console.log(".draggable.stopDrag")
        },
        endDrag: function(event, ui){
            flag_dragging = false;
            console.log(".draggable.endDrag")
        //var newid = $(ui.draggable).attr("rel")+"";
        // ui=>objeto arrastrado
        //console.dir(event);


        },
        start: function(event, ui){
            flag_dragging = true;
            console.log(".draggable.start")
        },
        stop: function(event, ui){
            flag_dragging = false;
            console.log(".draggable.stop")
        }
    });


    /*
                helper: function() {
                    return $(this);
                },
                startDrag: function() {
                },
                endDrag: function() {
                },
                dragOverSelector : '.profile-drop',
                dragOverClass : 'profileOver',
                cursorAt: { top: -10,right:-30 },
                distance: 25*/


    var control_estado = false;

    $("#control").click(function(){
        control_estado = !control_estado;

        $('#colpaleta').slideToggle("fast");

        $(this).removeClass("control_colapsado");
        $(this).removeClass("control_ampliado");

        if(control_estado){
            //$("#control").css("backgroundImage","icons/ident1.gif");
            $(this).addClass("control_colapsado");
        } else {
            $(this).addClass("control_ampliado");
        //$("#control").css("backgroundImage","icons/ident2.gif");
        }
    });

});

var flag_dragging = false;//counter Chrome dragging/text selection issue

function patch_chromeSeleccionTextos(){ //soluciona un problema de chrome
    return;

    console.log("pre-inicia wrappaer");

    $(".draggable").mouseover(function(){
        document.onselectstart = function(){
            return false;
        };

        console.log("wrapper installed");

    }).mouseout(function(){
        if(!flag_dragging){
            document.onselectstart = null;
            console.log("wrapper UN-installed");
        }
    });
}




$("#salvareporte").live( "click", function(){
    var nombre_para_informe = prompt(po_entrenombreinforme);

    if(!nombre_para_informe) return;

    $("#nombre_para_informe").val(nombre_para_informe);

    $("#modoform").val("savereport");
    ventana.serializarParametros();

    $.ajax({
        type: 'POST',
        url: 'modreporting.php',
        data: $('#reporting').serializeArray(),
        dataType: "json",
        async: true,
        success: function(data){
            if(!data) return;

            if(data["ok"]){
                //$(function() {
                var alink = $("<a>");
                alink.attr("href","modreporting.php?modo=loadreport&id="+data.id);
                alink.text(nombre_para_informe);

                var li = $("<li>");
                li.append(alink);

                $("#listaReportesUsuario").append(li);
            //});
            }
        }
    });
    return false;
});


$("#buscacampo").live("keyup",function(){
    var comparaCon = $(this).val()+"";
    if(comparaCon.length<1){
        $("#paleta li").show();
        return;
    }

    $("#paleta li").each(function(){
        var contenido = $(this).html().toUpperCase();
        if(contenido.indexOf(comparaCon.toUpperCase())<0){
            $(this).hide();
        }
    });
});