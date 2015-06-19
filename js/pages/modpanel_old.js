

      var Global = new Object();

        function CargarExtraFlotante(id_comm,tooltip_id){
                    $.ajax({
                            type: "POST",
                            url: "modcentral.php",
                            data: "modo=cargarExtra&id_comm=" + id_comm + "&tooltip_id="+tooltip_id,
                            success: function(datos){
                                    try {
                                        var obj = eval("(" + datos + ")");
                                        //console.dir(obj);
                                        if (obj.ok) {
                                            $("#"+obj.tooltip_id).html( obj.html );
                                        }
                                    }catch(e){
                                        //alert("ERROR: " + e+ ", code: ...");
                                        //alert("Code: "+datos);
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


    function genLista(){
                //alert("foo!")
        var changed_str = "";
        var i = 0;

        $("input.selcomm:checked").each(function(){
            var newid = $(this).attr("id_comm");
            changed_str += "," + newid;
            i++;
        });

        if (!i) return false;

                //$(".listaseleccionados").val(changed_str);
        $("#list_id_comm").val(changed_str);
        $("#list_id_comm2").val(changed_str);
        $("#list_id_comm3").val(changed_str);
        $("#list_id_comm4").val(changed_str);
                $("#list_id_task").val(changed_str);
                $("#list_id_comm2s").val(changed_str);
                $("#list_id_comm2c").val(changed_str);

          $("#list_id_setcod").val(changed_str);

              //  console.log("cs:"+changed_str)
        return true;
    };


    function enviaMe(){
        var $this = $(this);
        var et = $this.val();

        if ( !et ) return;
        if ( et==-1) return;

        var master = $this.data('master');
        $(master).submit();
    }


    $(function() {
        var w= $(window).width();//fix for the evil² ie.
        $("#cabeza").width(w);

        var anchorazonable_txt =(w-165-1) + "px";

        $("#link_central").addClass("pageSelected");
        $("#cajaaplicadores").hide();

        $("#tramitados_chk").click(function(){
            EnviarFiltrosChk.time = (new Date()).getTime();
            setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso);
        });

        $("#eliminados_chk").click(function(){
            EnviarFiltrosChk.time = (new Date()).getTime();
            setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso);
        });

        $("#traspasados_chk").click(function(){
            EnviarFiltrosChk.time = (new Date()).getTime();
            setTimeout(EnviarFiltrosChk,EnviarFiltrosChk.retraso)
        });




        $("#lista_etiquetas_status").change( enviaMe ).data("master","#etiquetador");
        $("#lista_status").change( enviaMe ).data("master","#etiquetador3");
        $("#lista_canales").change( enviaMe ).data("master","#etiquetador_canal");
        $("#lista_etiquetas_locations").change( enviaMe ).data("master","#etiquetador4");
        $("#lista_etiquetas_cotizacion").change( enviaMe ).data("master","#etcot");
        $("#lista_etiquetas_siniestros").change( enviaMe ).data("master","#etsin");

        $("#etiquetador_canal").submit( genLista );
        $("#etiquetadorsetcon").submit( genLista );

        $("#etsin").submit( genLista );
        $("#etcot").submit( genLista );

        $("#etiquetador").submit( genLista );
        $("#etiquetador2").submit( genLista );
        $("#etiquetador3").submit( genLista );
        $("#etiquetador4").submit( genLista );

        try {
            MasLineas.num_lineas    = '<patTemplate:var name="num_lineas"/>';
            MasLineas.last_id_comm  = '<patTemplate:var name="last_id_comm"/>';//en listados ordenados, indica el id mas bajo visto
            MasLineas.offset        = 0;//por defecto no hay offset, en listados desordenados, indica el offset de pagina
            MasLineas.paginasize    = '<patTemplate:var name="paginasize"/>';//tamaño de pagina
            MasLineas.listadodesordenado = <patTemplate:var name="desordenado"/>;//indica si se ordenan de forma numerica descendiente o no
        } catch(e){};

        $(".selcomm").change(function(este){
            if ( $('.selcomm:checked').length>0 ){
                $("#cajaaplicadores").show();
            } else {
                $("#cajaaplicadores").hide();
            }
        });


        $("input[type=text]").css("");

        $(".filaDatos").click( function(){
            var $this = $(this);
            var myid = new String( $this.attr("id") );
            var myid2 = myid.replace("datos_","");

            $("#a_"+myid2).click();
        });


        if ( $.browser.msie ){
            //"$(document).pngFix();
            $('.ik').ifixpng();
        }


        Global.altaComentario = $("#altaComentario").clone();
        $("#altaComentario").remove();


        if($("#buscaid_contacto_txt").length )
            $("#buscaid_contacto_txt").click( buscaIdContacto );

        $(".insertabloque").click(function(event){
            event.stopPropagation();

            $(".lcgenerico").hide();

            var etiqueta =  "Comunicaciones: "+ $(this).html();
            var idbloque = inString(etiqueta);

            var fulletiqueta = "#bloquecompleto_"+idbloque;

            if ($(fulletiqueta).length >0){
                $(fulletiqueta).remove();
            }

            NuevoBloque(etiqueta,idbloque);
            return false;
        });




        jQuery('ul.sf-menu').superfish();//.find('ul').bgIframe({opacity:false});

    });

function AutoOcultar(bloque){

    //$(".lcgenerico").hide();
    $(".bloque_"+bloque).toggle();
}

function inString(str)
{
    return str.replace(/[^A-Za-z0-9]/g, '_') ;
}


function NuevoBloque(etiqueta,idbloque){

        $.ajax({
                type: "POST",
                url: "modvistacliente.php",
                data: "modo=cargarNuevoBloque&etiqueta="+encodeURIComponent(etiqueta)+"&idbloque="+idbloque,
                success: function(datos){

                        try {

                            var obj = eval("(" + datos + ")");

                            //if(checkUnlogin(obj)) return;

                            if (obj.ok) {


                                $(obj.html).insertAfter("#afterfiltros");

                                $("#focuscarga").click();
                            } else {

                                alert("no ok!");
                            }
                        }catch(e){
                            alert("ERROR: " + e+ ", code:"+datos);
                        }
                  }
        });
}

