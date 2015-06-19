

$.event.props = $.event.props.join('|').replace('layerX|layerY|', '').split('|');

var pagina = (function(){
    var old_rel = -1;

    var mostrando=false;

    function oculta(rel){
        var rname = "#detalles_orden_"+rel;
        $(rname).html("");

        var r2name = "#orden_orden_"+rel;
        $(r2name).removeClass("seleccionado");
    }

    return {
        click:function(rel,loader){

            if(mostrando==rel){
                mostrando = false;
                oculta(rel);                 
                return;
            }

            mostrando = rel;

            var rname = "#detalles_orden_"+rel;

            $(rname).html("<center>Cargando..</center>");

            oculta(old_rel);
            old_rel = rel;

            var r2name = "#orden_orden_"+rel;
            $(r2name).addClass("seleccionado");

            loader(rel);
        },
        muestra:function(rel,html){
            var rname = "#detalles_orden_"+rel;
            $(rname).html("<table width='100%' class='muestralineas'>"+html+"</table>");
        }
    };
})();


pagina.cargador = (function(){


    function calculaPorcentaje($total,$parte){
       if(!$total) return 0;

       var $p = $parte/$total;
       if($p>1) $p = 1;
       return $p;
    }


    function Mathceil(dato){
        return Math.ceil(dato);
    }

    
    function genAutoBarrita($solicitada,$enviada,$atrasada,$cancelada,$validada,$gestionada){
        var $a_entregar = $solicitada - $cancelada;//no se usa
        var $pendiente =0;//TODO
        var $atrasadapendiente =  ($atrasada>$pendiente)?$atrasada:$pendiente;
        var $resto = $solicitada - ($enviada+$cancelada+$atrasadapendiente);

        if($resto<0) $resto = 0;

        var $anchura = 50;

        var $tamagnos = {};
        var $newfila = {};


        var $p_enviada = Mathceil(calculaPorcentaje($solicitada,$enviada) * $anchura);
        $tamagnos["enviada"] = $p_enviada;

        var $p_cancelada = Mathceil(calculaPorcentaje($solicitada,$cancelada) * $anchura);
        $tamagnos["cancelada"] = $p_cancelada;

        var $p_atrasada = Mathceil(calculaPorcentaje($solicitada,$atrasadapendiente) * $anchura);
        $tamagnos["atrasada"] = $p_atrasada;

        var $p_resto = Mathceil(calculaPorcentaje($solicitada,$resto) * $anchura);
        $tamagnos["resto"] = $p_resto;

        var $p_validada = Mathceil(calculaPorcentaje($validada,$resto) * $anchura);
        $tamagnos["validada"] = $p_validada;

        var $p_gestionada = Mathceil(calculaPorcentaje($gestionada,$resto) * $anchura);
        $tamagnos["gestionada"] = $p_gestionada;


        if(!$p_enviada && !$p_cancelada && !$p_atrasada && !$p_resto){
            $tamagnos["resto"] = $anchura;
        }


        var $newtotal = $p_enviada+$p_cancelada+$p_atrasada+$p_resto;

        if($newtotal>$anchura ){
         var $factor = $anchura/$newtotal;
         $tamagnos["enviada"] = Mathceil( $tamagnos["enviada"]*$factor);
         $tamagnos["cancelada"] = Mathceil( $tamagnos["cancelada"]*$factor);
         $tamagnos["atrasada"] = Mathceil( $tamagnos["atrasada"]*$factor);
         $tamagnos["resto"] = Mathceil( $tamagnos["resto"]*$factor);
         $tamagnos["validada"] = Mathceil( $tamagnos["validada"]*$factor);
         $tamagnos["gestionada"] = Mathceil( $tamagnos["gestionada"]*$factor);
        }

        $newfila["sxenviada"] = $tamagnos["enviada"];
        $newfila["sxcancelada"] = $tamagnos["cancelada"];
        $newfila["sxatrasada"] = $tamagnos["atrasada"];
        $newfila["sxresto"] = $tamagnos["resto"];
        $newfila["sxvalidada"] = $tamagnos["validada"];
        $newfila["sxgestionada"] = $tamagnos["gestionada"];

        var link = "<a href='javascript:void()'  class='sx_barrita'>";
        var link_end = "</a>"

        var html = "";
        html += "<span class='sx_enviada' style='width: "+$newfila["sxenviada"]+"px'></span>";
        html += "<span class='sx_cancelada' style='width: "+$newfila["sxcancelada"]+"px'></span>";
        html += "<span class='sx_atrasada' style='width: "+$newfila["sxatrasada"]+"px'></span>";
        html += "<span class='sx_resto' style='width: "+$newfila["sxresto"]+"px'></span>";
        html += "<span class='sx_validada' style='width: "+$newfila["sxvalidada"]+"px'></span>";
        html += "<span class='sx_gestionada' style='width: "+$newfila["sxgestionada"]+"px'></span>";
            
        return "<td>"+link+html+link_end+"</td>";
    }

    function getAutoBarritaEstado($estado){
        var $anchura = 50;
        var s_s = "sx_s_nada";

        switch($estado){
            case "Servido":
                s_s = "sx_s_servido";
                break;
             case "Cancelada":
                s_s = "sx_s_cancelada";
                break;
            case "Órdenes atrasadas":
            case "Ordenes atrasadas":
                s_s = "sx_s_atrasadas";
                $estado ="Ord. atrasada";
                break;
            case "En sistema":
                s_s = "sx_s_sistema";
                break;
            case "Validada":
                s_s = "sx_s_validada";
                break;
            case "Gestionada":
                s_s = "sx_s_gestionada";
                break;
            default:
                s_s = "sx_s_preparacion"
                break;
        }

 /*
Servido: Verde botella
Cancelada: Rojo vino
Ordenes Atrasadas: Naranja
En preparación: amarillo claro
En sistema: Blanco
*/

        var link = "<a href='javascript:void()'  class='zsx_barrita noenlace' title='"+_.escape($estado)+"' style='border:1px solid gray;text-align:center;' >";
        var link_end = "</a>"
        
        html = "<span class='"+s_s+"' style='height: 15px;width:80px;font-size:11px'> "+$estado+"</span>";

        return "<td>"+link+html+link_end+"</td>";

    }


    function filtraDesc(texto){
        if(texto=="null" || texto==null || !texto)
            return "--";

        return _.escape(texto);
    }



    return {
        /*
         * Descarga las filas de una orden dada
         */
        load:function(numero_orden){
            //console.log("llamado cache.cargador");

            $.ajax({
                type: "POST",
                url: "modnocanal.php",
                data: "modo=lineas&numero_orden=" + numero_orden,
                dataType:"json",
                success: function(datos){
                    if (datos["ok"]) {                        

                        /* Prepara el html */

                        var lineas = "";
                        var primeraLinea = " class='primeraLinea' ";
                        var autobarrita = "<td></td>";

                        $(datos.data).each(function(i,item){
                            //console.dir(item);

                            if(item["autobarrita"]){
                                //autobarrita = genAutoBarrita(item["cantidad_solicitada"],item["cantidad_enviada"],item["cant_orden_atrasada"],item["cantidad_cancelada"])
                                autobarrita = getAutoBarritaEstado(item["estado_calculado"]);
                            }


                            lineas += "<tr "+primeraLinea+">"
                            + autobarrita
                            + "<td>"+_.escape(item["numero_albaran"])+"</td>"
                            + "<td>"+_.escape(item["numero_documento"])+"</td>"
                            + "<td>"+_.escape(item["2_num_articulo"])+"</td>"
                            + "<td>"+filtraDesc(item["descripcion"])+"</td>"
                            + "<td>"+_.escape(item["ean_isbn"])+"</td>"
                            + "<td>"+_.escape(item["cantidad_solicitada"])+"</td>"
                            + "<td>"+_.escape(item["cantidad_enviada"])+"</td>"
                            + "<td>"+_.escape(item["cant_orden_atrasada"])+"</td>"
                            + "<td>"+_.escape(item["cantidad_cancelada"])+"</td>"
                            + "<td class='cajadinero'>"+_.escape(item["precio_unitario"])+"</td>"
                            + "<td class='cajadinero'>"+_.escape(item["precio_total"])+"</td>"
                            + "<td align='center'>"+_.escape(item["fecha_de_salida_y_recogida_por_transportista"])+"</td>"
                            + "<td align='center'>"+_.escape(item["entrega_prometida"])+"</td>"
                            + "<td align='center'>"+_.escape(item["fecha_cancel"])+"</td>"
                            //+ "<td>"+_.escape(item["dto"])+"</td>"                           
                            + "<td>"+_.escape(item["num_oc_ov_ot_rel"])+"</td>"
                            + "<td>"+_.escape(item["cp_3"])+"</td>"
                            + "<td>"+_.escape(item["pedido_binow"])+"</td>"                            
                            + "</tr>";

                            primeraLinea = "";
                        });

                        /* Carga en pantalla */
                        pagina.muestra(numero_orden,lineas);

                    }
                }
            });
        }
    }

})();



$(function() {

    $("a.handler").click(function(){
        var rel = $(this).attr("rel");
        pagina.click(rel,pagina.cargador.load);
    });


    $("td.alternoclick").each(function(){

       $(this).addClass("manita2");

       $(this).click(function(){
            var rel = $(this).attr("rel");
            pagina.click(rel,pagina.cargador.load);
       });

    });


    /*
    $("#filtro_tp_ord").change(function(){        
        var valor = $(this).val();
        $("#hidden_tp_ord").val( valor );  
        $("#filtrador").submit();
    });

    $("#filtro_numero_orden").keyup(function(event){
      if (event.which == 13) {
            var valor = $(this).val();

            $("#hidden_filtro_numero_orden").val(valor);
            $("#filtrador").submit();

            event.preventDefault();
        }  
    });
    */


});


$(function(){


    function serializar(){
        var valor = "";

        valor = $("#filtro_tp_ord").val();
        $("#hidden_tp_ord").val( valor );

        valor = $("#filtro_numero_orden").val();
        $("#hidden_filtro_numero_orden").val(valor);

        valor = $("#filtro_n_oc").val();
        $("#hidden_filtro_n_oc").val(valor);

        valor = $("#filtro_n_articulo").val();
        $("#hidden_filtro_n_articulo").val(valor);

        valor = $("#filtro_ean_isbn").val();
        $("#hidden_filtro_ean_isbn").val(valor);

        

        valor = $("#filtro_n_albaran").val();
        $("#hidden_filtro_n_albaran").val(valor);

        valor = $("#filtro_n_doc").val();
        $("#hidden_filtro_n_doc").val(valor);

        valor = $("#filtro_desde").val();
        $("#hidden_filtro_desde").val(valor);

        valor = $("#filtro_hasta").val();
        $("#hidden_filtro_hasta").val(valor);

        valor = $("#filtro_estado_calculado").val();
        $("#hidden_filtro_estado_calculado").val(valor);

        valor = $("#filtro_cod_cliente").val();
        $("#hidden_cod_cliente").val(valor);

        valor = $("#filtro_num_dest_envio").val();
        $("#hidden_filtro_num_dest_envio").val(valor);

        valor = $("#filtro_compagnia").val();
        $("#hidden_filtro_compagnia").val(valor);


        valor = $("#filtro_gestor").val();
        $("#hidden_filtro_gestor").val(valor);
    }

    //pagina.serializar = serializar;


    $("#boton_filtrosavanzados").click(function(){
        //$("#filtros_avanzados").toggle();
    });
   
    $("#boton_filtrar").click(function(){
       serializar();
       $("#filtrador").submit();
    });


    $(".serializar").keyup(function(event){
        if (event.which == 13) {
           serializar();
           $("#filtrador").submit();
           event.preventDefault();
        }

    });

    $("#boton_resetfiltros").click(function(){
        $("#filtro_tp_ord").val("");
        $("#filtro_numero_orden").val("");
        $("#filtro_n_oc").val("");
        $("#filtro_n_articulo").val("");
        $("#filtro_ean_isbn").val("");
        $("#filtro_n_binow").val("");
        $("#filtro_n_albaran").val("");
        $("#filtro_n_doc").val("");
        $("#filtro_desde").val("");
        $("#filtro_hasta").val("");
        $("#filtro_estado_calculado").val("");
        $("#filtro_cod_cliente").val("");
        $("#filtro_num_dest_envio").val("");
        $("#filtro_compagnia").val("");
        $("#filtro_gestor").val("");
       serializar();
       $("#filtrador").submit();
    });

});




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
      yearSuffix: ''};

   $.datepicker.setDefaults($.datepicker.regional['es']);
});



/*
 * Crea bindings 
 */
$(function(){

   $(".datepickerme",$("#cajafiltros")).datepicker();

   $("#borraFechas").click(function(){

       $("#filtro_hasta").val("");
       $("#filtro_desde").val("");

   });


});














