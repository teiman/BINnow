

$(function(){

    $.event.props = $.event.props.join('|').replace('layerX|layerY|', '').split('|');

});



var Pagina = {};



/*
 * Ajusta el layout de forma automatica
 */
$(function() {
    function AjustarLayout(){
        var w= $(window).width();//fix for the evil² ie.
        $("#cabeza").width(w);        
    }

    AjustarLayout();

    //$(window).resize($.debounce(250,AjustarLayout));
    $(window).resize(AjustarLayout);
});


/*
 * Bindings basicos de la pagina
 */
$(function() {
    var $lineas_de_comm = $("#lineas_de_comm");
    var $cajafiltros = $("#cajafiltros");

    $("#link_central",$("#navcontainer")).addClass("pageSelected");
        
    $("#cajaaplicadores",$cajafiltros).hide();


    $("#tramitados_chk",$cajafiltros).click(function(){
        EnviarFiltrosChk.time = (new Date()).getTime();
        setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso);
    });

    $("#gestionada_chk",$cajafiltros).click(function(){
        EnviarFiltrosChk.time = (new Date()).getTime();
        setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso);
    });

    $("#eliminados_chk",$cajafiltros).click(function(){
        EnviarFiltrosChk.time = (new Date()).getTime();
        setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso);
    });

    $("#traspasados_chk",$cajafiltros).click(function(){
        EnviarFiltrosChk.time = (new Date()).getTime();
        setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso)
    });


    $("#soloasignados_chk",$cajafiltros).click(function(){
        EnviarFiltros2Chk.time = (new Date()).getTime();
        setTimeout(EnviarFiltros2Chk,EnviarFiltros2Chk.retraso);
    });
    
    $("#mostrarcerradas_chk",$cajafiltros).click(function(){
        EnviarFiltros3Chk.time = (new Date()).getTime();
        setTimeout(EnviarFiltros3Chk,EnviarFiltros3Chk.retraso);
    });

    $("#mostrarpendientegestor_chk",$cajafiltros).click(function(){
        EnviarFiltros4Chk.time = (new Date()).getTime();
        setTimeout(EnviarFiltros4Chk,EnviarFiltros4Chk.retraso);
    });
    
    $("#mostrarpendientelogistica_chk",$cajafiltros).click(function(){
        EnviarFiltros5Chk.time = (new Date()).getTime();
        setTimeout(EnviarFiltros5Chk,EnviarFiltros5Chk.retraso);
    });

    $("#mostrarenestudio_chk",$cajafiltros).click(function(){
        EnviarFiltros6Chk.time = (new Date()).getTime();
        setTimeout(EnviarFiltros6Chk,EnviarFiltros6Chk.retraso);
    });

    
    
    $("#lista_etiquetas_status",$cajafiltros).change( enviaMe ).data("master","#etiquetador");
    $("#lista_status",$cajafiltros).change( enviaMe ).data("master","#etiquetador3");
    $("#lista_etiquetas_locations",$cajafiltros).change( enviaMe ).data("master","#etiquetador4");
    $("#lista_etiquetas_incidencias",$cajafiltros).change( enviaMe ).data("master","#etsin");


    $("form.autoenviocambia select").change(function(){
        genLista();

        var newval = $(this).val;
        if(!newval || newval==-1) return;

        $(this).closest("form").submit();
    });

    $(".need_genlista",$cajafiltros).submit( genLista );

    $(".selcomm",$lineas_de_comm).change(function(event){
        if ( $('.selcomm:checked',$lineas_de_comm).length>0 ){
            $("#cajaaplicadores",$cajafiltros).show();
        } else {
            $("#cajaaplicadores",$cajafiltros).hide();
        }

        event.preventDefault()
        event.stopImmediatePropagation();

        return false;
    });


    $(".filaDatos",$lineas_de_comm).click( function(){
        var $this = $(this);
        var myid = new String( $this.attr("id") );
        var myid2 = myid.replace("datos_","");

        $("#a_"+myid2).click();
    });

    /*
    if ( $.browser.msie ){
        try{
        //$('.ik').ifixpng();
        }catch(e){};
    }
    */

    Global.altaComentario = $("#altaComentario").clone();
    $("#altaComentario").remove();

    $("#buscaid_contacto_txt",$cajafiltros).click(function(){
        buscaIdContacto()
    });

    setTimeout(function(){
        //$('.local-load',$("#lineas_de_comm")).cluetip({local:true,tracking: true});
                                        
        if($('.local-load',$lineas_de_comm).length>0)
            $('.local-load',$lineas_de_comm).cluetip({
                local:true,
                tracking: true
            });
    },0);

});


$(function(){
    if($("#combo_tipo_id_label").length>0){
        $("#combo_tipo_id_label").val(Global.autofiltra_tipo_idlabel);
    }
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
});



/*
 * Combo elección de color delegación
 */
$(function(){
    console.log("Creando bindings de colordelegacion")

    if($("#seleccionDelegacion").length>0){
        $("#seleccionDelegacion").change(function(){
            console.log("Se cambia color");

            $("#formcolordelegacion").submit();
        });
    }
});



/*
 * Api de objeto pagina
 *
 */
$(function(){

    Pagina.cuandoNuevaSolapa  = function() {

        /* Los select readonly solo contienen utilizable el dato elegido */
        $("select[readonly=readonly]").each(function(){
            $("option",this).each(function(){
                $(this).hide();
            });

            $("option:selected",this).each(function(){
                $(this).show();
            });            
            $(this).addClass("sololectura");
        });


        Pagina.bindings_solapa();
    /* ----------------------------- */
    };

    Pagina.enviarCambioEstado = function(formulario,id_comm){

        console.log("enviarCambioEstado");

        if(formulario=="#apply_status_com_datos"){
            //mostrar formulario de incidencias?
            if( Global.id_task == Global.id_task_incidencias ){

                var valor = $(formulario +" select").val();

                if(valor==Global.id_estado_abierto){
                    console.log("enviarCambioEstado: tienta abrir, porque estado es "+valor);

                    Pagina.tientaAbrirFormularioIncidencias(id_comm);
                    
                    return false;
                }
            }
        }

        console.log("enviarCambioEstado.enviando");

        $(formulario).submit();

        setTimeout(function(){
            
            //*Cuando cambia el estado el desplegable de los datos del pedido deben desaparecer, no solo cuando eliminamos y gestionamos
            document.location.href = "modcentral.php?r="+Math.random();
            //Pagina.recargarParaComm(id_comm);
        },300);
    };

    Pagina.estamosEnIncidencias = function(){
        return Global.id_task == Global.id_task_incidencias;
    };


    Pagina.tientaAbrirFormularioIncidencias = function(id_comm){
        console.log("Pagina.tientaAbrirFormularioIncidencias");

        if( Pagina.estamosEnIncidencias()  ){
            var existe_formulario = false;
                
            if( $("#id_formulario_incidencias").length){
                if($("#id_formulario_incidencias").val()==id_comm){
                    existe_formulario = true;
                }
            }

            console.log("fE:"+existe_formulario);

            if(!existe_formulario){
                console.log("No existe formulario, asi que intentamos abrir uno")
                Pagina.crearFormularioIncidencia(id_comm);
            }
        } else {
            console.log("No estamos en incidencias");
        }
    };

    Pagina.incidenciaRequiereDespligue = function(){
        var estadoIncidencia = $("#incidencia_actual_id_status").val();        

        //var estadosRW = estadoIncidencia == Global.incidencia_id_status_cerrada || estadoIncidencia == Global.incidencia_id_status_eliminada;
        var eliminada =  estadoIncidencia == Global.incidencia_id_status_eliminada;

        var estadoAbierta = estadoIncidencia==Global.id_estado_abierto;

        var estadoRecibida = estadoIncidencia == Global.id_estado_recibido;

        var cerrada = estadoIncidencia == Global.incidencia_id_status_cerrada;


        if(eliminada && Global.filtroAdmin){
            return true;
        }


        if(eliminada){
            console.log("Esta eliminada");
            return false;
        }

        if(estadoAbierta) {
            return true;
        }

        if(estadoRecibida){
            return false;
        }

        if(estadoIncidencia!=-1){
            return true;
        }


        if(cerrada && Global.filtroAdmin){
            return true;
        }




        console.log("no se abrira, porque no creemos que sea abrible")

        return false;
    };


    Pagina.abiertaSolapaDatos = function(id_comm){
        console.log("Pagina.abiertaSolapaDatos");

        if( Pagina.estamosEnIncidencias() ){



            if( Pagina.incidenciaRequiereDespligue() ){
                Pagina.tientaAbrirFormularioIncidencias(id_comm);
            } else {
                console.log("NOENTRA");
            }
            
        } else {
            console.log("ignorar:no estamos en incidencias");
        }
    };

    Pagina.crearFormularioIncidencia = function(id_comm){
        console.log("crearFormularioIncidencia");

        $.ajax({
            type: "POST",
            url: "modincidencias.php",
            dataType: 'json',
            data: "modo=formulario&id_comm=" + id_comm,
            success: function(data){
                console.log("crearFormularioIncidencia.success.idcomm:"+id_comm);

                if(data["ok"]){
                    var nombre = "#contenedor_"+id_comm;

                    if($(".formulario_incidencias", $(nombre)).lenth)
                        return;//ya existe el formulario

                    var div = $("<div class='formulario_incidencias'></div>");
                    div.html(data["html"]);
                    
                    $(nombre).append(div);
                    Pagina.bindings_nuevoFormulario();                    
                } else {

                    console.log("No se pudo crear el formulario");
                }
            }
        });
        
    };

    Pagina.bindings_nuevoFormulario = function(){
        console.log("Bindeando data  ");
                        
        /* tareas requeridas por lineas*/
        preparar();//prepara tareas anteriores
        crearBindings();//crea los bindings adecuados


        setTimeout(function(){

            // console.log("v1-mcjs-2");

            $(".sololectura[type=checkbox]").each(function(){
                var ck = ($(this).attr("checked")!== undefined)?1:0;
                $(this).attr("data-ck",ck);

                //console.log("mclive-ck:"+ck);
                $(this).change(function(){

                    var ck= $(this).attr("data-ck");

                    //console.log("ck:"+ck);
                    if(ck==1){
                        //console.log("re-enabling");
                        $(this).attr("checked","checked");
                    }else{
                        //console.log("re-disabling");
                        $(this).removeAttr("checked");
                    }
                    return false;
                });
            });

        //console.log("v1-mcjs-3");

        },0);
    /* otras tareas */

    //de momento nada

    };

    Pagina.mensajeFadeout = function(){
        //$("#fadeout").hide("slow");
        $("#fadeout").delay(600).fadeTo("slow", 0.01);
    };


    Pagina.navegaDocumento = function(idcom){
        
        document.location.href="moddocumento.php?id_comm="+idcom;

        return false;
    };


    var clonlinea = false;

    function preparar(){

        $("#lineas_incidencia .cloneame").removeClass("oculto");

        clonlinea = $("#lineas_incidencia .cloneame").clone();

        $("#lineas_incidencia .cloneame").remove();
    // $("#lineas_incidencia .borralinea").remove();
    }

    function crearBindings(){

        $("#lineas_incidencia .borralinea").click(function(){

            //alert("foo!");
            var datalinea = $(this).attr("data-linea")

            var dataid = "#datalinea_" + datalinea;
            var dataidextra = "#datalineaextra_"+ datalinea;

            $(dataid).remove();
            $(dataidextra).remove();

            return false;
        });


        $("#live_cod_cliente").keyup(function(){

            //console.log("live_cod_cliente.keyup");
            
            var codigoactual = $(this).val();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'modincidencias.php',
                data: 'modo=buscacliente&codigocliente='+ encodeURIComponent(codigoactual),
                success: function(data){
                    //console.log("------------------info encontrada------------------")
                    //console.dir(data);
                    if(data && data["ok"]){
                        $("#live_nombre_cliente").val(data["nombre"]);
                    }
                }
            });
        });


        $("#masboton").click(function(){

            var extra = $(clonlinea).clone();

            $(".borralinea",extra).click(function(){

                //alert("hi!");
                $(extra).remove();

                return false;
            });

            $("#lineas_incidencia").append(extra);
        });


        $(".sololectura optgroup").remove();

        $("#formularioincidencias").submit(function(){

            //$('#reporting').serializeArray(),
            /*
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'modincidencias.php',
                data: $('#formularioincidencias').serializeArray(),
                success: function(data){
                    console.log("------------------Datos Incidencias------------------")
                    console.dir(data);

                    if(!data) return;

                    if(data["ok"]){
                        $("#caja_formularioincidencias").html( data["html"] );
                        Pagina.bindings_nuevoFormulario();

                        //AvisarUsuario("Datos guardados");
                        Pagina.mensajeFadeout();
                    }
                }
            });


            return false;
            */

            });

    }


});



//apply_status_com.submit
function apply_status_com_submit(formulario){
//apply_status_com

}




function CargarExtraFlotante(id_comm,tooltip_id){
    $.ajax({
        type: "POST",
        url: "modcentral.php",
        data: "modo=cargarExtra&id_comm=" + id_comm + "&tooltip_id="+tooltip_id,
        success: function(datos){
            try {
                var obj = eval("(" + datos + ")");

                if (obj.ok) {
                    $("#"+obj.tooltip_id).html( obj.html );
                }
            }catch(e){
            //console.log("ERROR:" + e + "\n" +datos);
            }
        }
    });
}



function EnviarFiltrosChk(){

    var paso = ((new Date()).getTime() - EnviarFiltrosChk.time );

    if (paso>(EnviarFiltrosChk.retraso-50)){
        $("#filtros_estados").submit();
    }
}

EnviarFiltrosChk.retraso = 1000;



function EnviarFiltros2Chk(){

    var paso = ((new Date()).getTime() - EnviarFiltros2Chk.time );

    if (paso>(EnviarFiltros2Chk.retraso-50)){
        $("#soloasignados_chk").submit();
    }
}


EnviarFiltros2Chk.retraso = 100;

function EnviarFiltros3Chk(){

    var paso = ((new Date()).getTime() - EnviarFiltros3Chk.time );

    if (paso>(EnviarFiltros3Chk.retraso-50)){
        $("#mostrarcerradas_chk").submit();
    }
}


EnviarFiltros3Chk.retraso = 100;

function EnviarFiltros4Chk(){

    var paso = ((new Date()).getTime() - EnviarFiltros4Chk.time );

    if (paso>(EnviarFiltros4Chk.retraso-50)){
        $("#mostrarpendientegestor_chk").submit();
    }
}


EnviarFiltros4Chk.retraso = 100;


function EnviarFiltros5Chk(){

    var paso = ((new Date()).getTime() - EnviarFiltros5Chk.time );

    if (paso>(EnviarFiltros5Chk.retraso-50)){
        $("#mostrarpendientelogistica_chk").submit();
    }
}


EnviarFiltros5Chk.retraso = 100;

function EnviarFiltros6Chk(){

    var paso = ((new Date()).getTime() - EnviarFiltros6Chk.time );

    if (paso>(EnviarFiltros6Chk.retraso-50)){
        $("#mostrarenestudio_chk").submit();
    }
}


EnviarFiltros6Chk.retraso = 100;


function genLista(){
    var changed_str = "";
    var i = 0;

    $("input.selcomm:checked").each(function(){
        var newid = $(this).attr("id_comm");
        changed_str += "," + newid;
        i++;
    });

    if (!i) return false;

    console.log("changed:"+changed_str);
        
    $(".need_changed_str").val(changed_str)
    return true;
}


function enviaMe(){

    console.log("enviaMe");

    var $this = $(this);
    var et = $this.val();


    if ( !et ) return;
    if ( et==-1) return;


    var master = $this.data('master');

    console.log("Master:"+master+",et:"+et);


    $(master).submit();
}


function Desfiltra(buscacodcom,buscacodcomform){
    // $("#buscacodcom").val("");
    //$("#buscacodcomform").submit();
    $(buscacodcom).val("");
    $(buscacodcom).remove();
    $(buscacodcomform).submit();
}



        
function ConmutarSolapa(id_comm){
    //if ( AmpliarSolapasDeComm.ultimaAmpliada != id_comm)
    //		CerrarSolapa(AmpliarSolapasDeComm.ultimaAmpliada);

    if ( AmpliarSolapasDeComm.ultimaAmpliada == id_comm) {
        //la proxima vez que se pulse, se reabrira esta
        AmpliarSolapasDeComm.ultimaAmpliada = false;

        //La solapa abierta es la actual, la cerramos.
        CerrarSolapa(id_comm);
        return;
    }

    AmpliarSolapasDeComm(id_comm);
}


function checkUnlogin(obj){
    if (obj["ok"])
        return false;

    if (obj["logout"]){
        GB_show("Login","login.php?modo=popup",470,600);
        return true;
    }
    return false;
}



var htmlCargando = "<center style='height: 185px;font-weight:bold'><img style='margin-top:80px' zalign='center' src='img/ajaxian.gif'> Cargando...</center>" ;

function AmpliarSolapasDeComm(id_comm){

    var enlace = "#a_" +id_comm;

    if ($(enlace).length){
        $(enlace).removeClass("sinleer");
        $(enlace).addClass("leido");
    }

    if($("#datos_"+id_comm).length){
        $("#datos_"+id_comm).removeClass("sinleer");
        $("#datos_"+id_comm).addClass("leido");
    }

    var name = "#contenedor_" + id_comm;

    if ( $(name).length  ){
        $(name).html( htmlCargando );

        var nombre2 = "#titulo_" + id_comm ;
        var nombre3 = "#datos_" + id_comm ;

        $(nombre3).addClass("seleccionado_cabecera");

        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: "modo=cargaSolapa&id_comm="+id_comm,
            success: function(datos){
                try {
                    //alert(datos);
                    var obj = eval("(" + datos + ")");

                    if(!obj) return;

                    if(checkUnlogin(obj)) return;

                    if (obj["ok"]) {
                        var nameent = "#contenedor_" + obj.id_comm;
                        $(nameent).html("<div class='solapascontenedor'>"+ obj.html+"</div>" );

                        if ( AmpliarSolapasDeComm.ultimaAmpliada != obj.id_comm)
                            CerrarSolapa(AmpliarSolapasDeComm.ultimaAmpliada);
                        AmpliarSolapasDeComm.ultimaAmpliada = obj.id_comm;


                        Pagina.cuandoNuevaSolapa();

                    //console.log("Marcando noabiertas transparents");
                    //$(".filaListado[id!=datos_"+obj.id_comm+"]").css("opacity","0.5");
                    }
                }catch(e){
                    alert("ERROR.605: " + e);
                }
            }
        });

    }
}

AmpliarSolapasDeComm.ultimaAmpliada = 0;


function CerrarSolapa(id_comm){
    if (!id_comm) return;

    var nombre = "#contenedor_" + id_comm;
    var nombre2 = "#titulo_" + id_comm;
    var nombre3 = "#datos_" + id_comm;

    if ( $(nombre).length  ){
        $(nombre).html( "" );
        //$(nombre2).removeClass("seleccionado");
        $(nombre3).removeClass("seleccionado_datos");
        $(nombre3).removeClass("seleccionado_cabecera");
    }


    $(".filaListado").css("opacity","1");
}


function marcaSolapaAbierta( modo){
    var nameClaseSolapasel ="solapasel";
    $("#es_"+modo).addClass( nameClaseSolapasel );
}


/*
 * Recarga una solapa de solapa
 *
 */
function RecargaSolapaModo(id_comm,modo){
    var name = "#contenedor2_" + id_comm;

    var nameClaseSolapasel ="solapasel";
    var $contenedor = $(name);
    var $zonasolapas = $( "#contenedor_" + id_comm);

    $(".subsolapa",$zonasolapas).removeClass(nameClaseSolapasel);

    $("#es_"+modo,$zonasolapas).addClass( nameClaseSolapasel );

    if ( $(name).length  ){

        var nombre2 = "#titulo_" + id_comm ;
        var nombre3 = "#datos_" + id_comm ;

        $(nombre3).addClass("seleccionado_cabecera");

        $(name).html( htmlCargando );

        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: "modo=cargaSubSolapa&id_comm="+id_comm+"&submodo="+modo,
            success: function(datos){
                try {

                    var obj = eval("(" + datos + ")");
                    if(checkUnlogin(obj)) return;
							
                    if (obj.ok) {
                        $contenedor.html(obj.html);

                        Pagina.cuandoNuevaSolapa();
                    }
                }catch(e){
                //alert("ERROR: " + e+ ", code:"+datos);
                }
            }
        });

    }
	
    return false;
}


function Revelador(){

    var t = Revelador.altoindex;

    var namepossible = "#titulo_" + t;
    var namepossible2 = "#datos_" + t;
    var namepossible3 = "#a_" + t;


    if ( $(namepossible).length ){
        $(namepossible).show();//debe ser simplemente show, porque si utilizamos algo mas espectacular, se estropea el css
        $(namepossible2).show();
		
        setTimeout(function(){
            $(namepossible3).cluetip({
                local:true,
                tracking: true
            });
        },0);
    }

    t--;

    if (t<Revelador.bajoindex){
        //hemos superado el indice alto
        Revelador.activo = false;//no necesita correr mas
    }

    Revelador.altoindex = t;//por tanto, bajoindex apuntara al siguiente elemento a mostrar

    if (Revelador.activo){
        setTimeout("Revelador()",10);
    }
}

Revelador.empezar = function (desde, hasta){
    Revelador.activo = true;

    Revelador.bajoindex = desde;

    if (!Revelador.altoindex)
        Revelador.altoindex = hasta;

    setTimeout("Revelador()",200);//empieza
}


/*
 * Carga lineas extra en el fondo
 *
 */
function MasLineas(){

    if($.browser.msie){

        ////modo:resultados_pagina
        //tipo_resultadospagina:100

        var size = parseInt(MasLineas.paginasize) + 10;

        document.location = "modcentral.php?modo=resultados_pagina&tipo_resultadospagina="+size;
        return;
    }

    function CerrandoOpcionMaslineas(){
        $("#cajaMaslineas").html("<font color='gray' style='color:gray'>No hay más líneas</font>");
    }



    $.ajax({
        type: "POST",
        url: "modcentral.php",
        data: "modo=cargarMasLineas&last_id_comm=" + MasLineas.last_id_comm+"&offset="+(MasLineas.offset),
        success: function(datos){
            try {
                if (MasLineas.last_id_comm == 9999999999){
                    //alert('sale por return');
                    CerrandoOpcionMaslineas();
                    return;
                } 
                var obj = eval("(" + datos + ")");
                if(checkUnlogin(obj)) return;

                if (obj["ok"]) {
                    
                    if(obj["yaestantodas"]){
                        CerrandoOpcionMaslineas();
                    }

                    oldidcom = MasLineas.last_id_comm;
                    newidcom = obj.last_id_comm
                    MasLineas.last_id_comm = newidcom;
                    
                    $("#lineas_de_comm").append( obj.html );
                    
                    if (obj.ordenlogico){
                        for(var t=newidcom;t<oldidcom;t++){
                            var namepossible = "#titulo_" + t;
                            var namepossible2 = "#datos_" + t;

                            if ( $(namepossible).length ){
                                $(namepossible).hide();
                                $(namepossible2).hide();
                            }
                        }
                        //alert('Nuevo=' + newidcom + 'Old=' + oldidcom);
                        
                        Revelador.empezar(newidcom,oldidcom);
                        
                    } else {
                        MasLineas.offset += parseInt(obj.paginasize,10);
                    }
                    
                }
            }catch(e){
                alert("ERROR.784: " + e+ ", code:"+datos);
            }
        }
    });
    return false;
}


$(function(){


    //

    $(window).ready(function(){
        if(Global.id_autoabrir)
            ConmutarSolapa(Global.id_autoabrir)
    });


});



$(function(){

    Pagina.click_Cliente = function(id_cliente,id_comm){
        console.log("Pagina.click_Cliente");

        ConmutarSolapa(id_comm);
        return;

        if(0)
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: "modcliente.php",
                data: "modo=vistazo&id_cliente=" + id_cliente +"&id_comm=" + id_comm,
                success: function(datos){
                
                    if(!datos || !datos["ok"]){
                        return;
                    }

                    if(datos["error"]){
                        alert(datos["error"]);
                    }else {
                        $("#hueco_para_cliente").html(datos["html"]);
                    }
                }
            });
    };

});


$(function(){

     Pagina.gui_hayDatosNuevos = function(){

        console.log("aparece un interface muy bonito");


        $("#gui_nuevosmensajes").removeClass("oculto");
     };



    /*
        Trae del servidor la "firma" de los datos que hay ahora visible, de modo que si se detectan cambios, se pide el refresco de la pagina
    */
    Pagina.update_gransuma = function(){

        if( Global.gransuma<=0){
            return;//modo desconectado, no avisa de novedades
        }

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "modcentral.php",
            data: "modo=gransuma",
            success: function(datos){

                if(!datos || !datos["ok"]){
                    return;
                }

                var nueva_suma = datos["gransuma"];

                if(nueva_suma!=Global.gransuma  && nueva_suma>0 ){
                    Pagina.gui_hayDatosNuevos();
                }

                Global.gransuma = nueva_suma;

                console.log("gransuma:"+Global.gransuma);
            }
        });
    };

    if(1){
        //Revision continua de mensajes nuevos
        var delay = 10000 + (Math.random()*6000);

        setInterval(function(){
            Pagina.update_gransuma();
        },delay);
    }
});






$(function(){

    Pagina.recargarParaComm = function(id_comm){
        setTimeout(function(){
            if(id_comm)
                document.location.href = "modcentral.php?modo=autoabrir&id_abrir="+id_comm +"&r="+Math.random();
            else
                document.location.href = "modcentral.php?r="+Math.random();

        },100);
    };

    Pagina.incidenciaBotonEnEstudio = function(id_comm){
        Pagina.enviarCambioEstado('#apply_status_en_estudio',id_comm);
        Pagina.ocultaBoton(this);
        Pagina.CambiaText('#status_text','En estudio');
        Pagina.recargarParaComm(id_comm);
    };

});


$(function(){
    Pagina.CambiaValue = function(este,valor){
        $(este).val(valor);
    };

    Pagina.CambiaText = function(este,valor){
        $(este).html(valor);
    };

    Pagina.ocultaBoton = function(este){
        $(este).hide();
    };


    Pagina.bindings_incidencias = function(){

        console.log("Pagina.bindings_incidencias");


        //console.log("v1-mcjs")




    };
});


$(function(){

    Pagina.bindings_solapa = function(){
        

        console.log("He. subsolapa_datos, binding");



        $("#CambiarAsignacionClienteManual").click(function(){

            var codigopropuesto = $("#cod_seracliente").val();

            var id_comm = $("#CambiarAsignacionClienteManual").attr("data-idcomm");


            if(!codigopropuesto){
                $("#cod_seracliente").focus();
                return;
            }


            $.ajax({
                type: "POST",
                dataType: 'json',
                url: "modcentral.php",
                data: "modo=asignacionclientemanual&codigo=" + encodeURIComponent(codigopropuesto) + "&id_comm="+id_comm,
                success: function(datos){

                    if(!datos || !datos["ok"]){
                        return;
                    }

                    if(datos["aceptado"]){
                        $("#caja_codigorapido").html("<font color='green'><b>Código asignado</b></font>");

                        document.location = "modcentral.php?modo=autoabrir&id_abrir="+id_comm+"&r="+Math.random();
                    } else {
                        $("#cod_seracliente").val("");

                        if(datos["error"]){
                            alert(datos["error"]);
                            return;
                        }
                    }
                //modo=autoabrir&id_abrir=3838&r=0.4803141891025007

                }

            });
        });
        

        $("#btn_altarapida").click(function(){

            console.log("btn_altarapida pulsado");

            var id_comm = $("#incidencia_id_comm").val();
            var codigopropuesto = $.trim( $("#entrandocodigo").val());
            var nombre = $.trim($("#entrandonombre").val())

            if(!codigopropuesto || !nombre){
                alert("Nombre y codigo son obligatorios");
                return;
            }

            $.ajax({
                type: "POST",
                dataType: 'json',
                url: "modcentral.php",
                data: "modo=intentaaltarapida&nuevocodigo=" + encodeURIComponent(codigopropuesto) +"&nuevonombre=" + encodeURIComponent(nombre)  + "&id_comm="+id_comm,
                success: function(datos){

                    if(!datos || !datos["ok"]){
                        return;
                    }

                    if(datos["aceptado"]){
                        $("#caja_altarapida").html("<font color='green'><b>Alta realizada</b></font>");

                        console.log("recargando para:"+id_comm);
                        Pagina.recargarParaComm(id_comm);
                    } else {
                        var $campo = $("#entrandocodigo");
                        
                        $campo.val("");

                        if(datos["error"]){
                            alert(datos["error"]);
                        } else {
                            alert("Datos de alta incorrectos");
                        }
                    }
                }

            });

        });

        $("#solapa_cambia_delegacion").change(function(){
            var id_location = parseInt($("#solapa_cambia_delegacion").val(),10);
            var id_comm = parseInt($("#solapa_cambia_delegacion").attr("data-idcomm"),10);

            if(id_location>0 && id_comm >0){

            } else {
                return;
            }

            console.log("info:,idcom:"+id_comm+",idloc:"+id_location);


            $.ajax({
                type: "POST",
                dataType: 'json',
                url: "modcentral.php",
                data: "modo=cambiodelegacionpedido&id_location=" + id_location + "&id_comm="+id_comm,
                success: function(datos){

                    if(!datos || !datos["ok"]){
                        return;
                    }

                    if(datos["aceptado"]){
                        Pagina.recargarParaComm(id_comm);
                    } else {
                        $("#solapa_cambia_delegacion").val("");

                        if(datos["error"]){
                            alert(datos["error"]);
                        }


                    }
                }
            });

        });


        $("#btn_asgnacion_parcial_cliente").click(function(){


            var from_to = $("#form_asgnacion_parcial_cliente input[name=from_to]").val();
            var id_comm = $("#form_asgnacion_parcial_cliente input[name=id_comm]").val();
            var id_contact = $("#form_asgnacion_parcial_cliente input[name=id_contact]").val();


            $.ajax({
                type: "POST",
                url: "modcentral.php",
                data: "modo=asgnacion_parcial_cliente&from_to=" + encodeURIComponent(from_to) + "&id_comm="+id_comm + "&id_contact="+id_contact,
                success: function(){
                           return;                         
                }
            });

            Pagina.recargarParaComm(id_comm);
        })




    };


})


$(function(){


   		Pagina.btn_reenviar = function(){

			var id_comm = $("#id_comm_reenvio").val();
			var newto = $("#newdireccion_reenvio").val();
			var newasunto = $("#newasunto_reenvio").val();
			var mensaje = $("#mensaje_reenvio").val();


			$.ajax({
					type: "POST",
					url: "ajax.php",
					data: "modo=reenviar&id_comm="+id_comm+
							"&newto="+encodeURIComponent(newto)+
							"&newasunto="+encodeURIComponent(newasunto)+
							"&mensaje="+encodeURIComponent(mensaje),
					success: function(datos){
							try {
								if(!datos) return;
								var obj = eval("(" + datos + ")");
								if(checkUnlogin(obj)) return;

								if (obj.ok) {
									alert(obj.msg);
								}
							}catch(e){
								//alert("ERROR: " + e+ ", code:"+datos);
                                alert(datos);
							}
					  }
			});

			return false;
		};
});



$(function(){

    Pagina.btn_guardarCambios = function(){
        $("#nocambiarestados").val(1);
        $("#formularioincidencias").submit();
    };


});

