

/*
 * Inicializando el objeto Global. 
 */

var Global = Global || []

Global.ocupados = [];
Global.saliendo = false;
Global.numLlamadas = 0;
Global.debug = function(data,bloquenombre){
    return "";
};
Global.alert = function(){
    alert("");
};


/*
 * Cola de ajax en procesamiento. Permite tener un handler a ajax enviados para poder abortarlos.
 */

$.xhrPool = [];
$.xhrPool.abortAll = function() {
    _.each(this, function(jqXHR) {
        jqXHR.abort();
    });
};
$.ajaxSetup({
    beforeSend: function(jqXHR) {
        $.xhrPool.push(jqXHR);
    }
});


/*
 * Arregla un fallo extraño producido de webkit
 */
if ($.browser.webkit) { //patch a unwanted warning in chrome.
    $.event.props = $.event.props.join('|').replace('layerX|layerY|', '').split('|');
}





/*
 * 
 Ayuda a debugging
 *
 */
$(function(){

    $("#mostrardebug").click(function(){
        $("#debuglog").toggle();
    });

    function gen_tabla(){
        return $("<table border=1></table>");
    }

    function gen_row(){
        return $("<tr></tr>");
    }

    function gen_item(texto){
        var item = $("<td>"+_.escape(texto)+"</td>");
        return item;
    }

    /*
     * Feedback para el usuario de que se estan realizando operaciones de carga de datos
     */
    Global.actualizaIconos = function(){
        //console.log("nL:"+Global.numLlamadas);

        if(Global.numLlamadas>0){            
            $(".js-iconocarga").show();
        }else{
            $(".js-iconocarga").hide();
        }        
    };

    Global.debug  = function(data,bloque){
        var table = gen_tabla();
        var row;


        if(!data){

            row = gen_row();
            row.append(gen_item("Bloque"));
            row.append(gen_item(bloque));
            table.append(row);

            row = gen_row();
            row.append(gen_item("Datos vacios!"));
            row.append(gen_item(""));
            table.append(row);

            $("#debuglog").append(table);
            return;
        } else {
            row = gen_row();
            row.append(gen_item("Bloque"));
            row.append(gen_item(bloque));
            table.append(row);

            if(data["sql"]){
                row = gen_row();
                row.append(gen_item("sql"));
                row.append(gen_item(data["sql"]));
                table.append(row);
            }

            $("#debuglog").append(table);
        }

    };
    


    
});

/*
 *
 Mantiene un CSS que distingue los bloques en proceso de carga
 * 
 */
$(function(){
    Global.marca_Cargando = function(bloquenombre){
        var name = bloquenombre + "";
        name = name.split("_");
        var bloque = "#"+name[1];
	
        if($(bloque).length >0){
            $(bloque).addClass("bloque_cargando");
        } else {
        //console.log("Global.marca_Cargando: No encuentro:"+bloquenombre+",bloque:"+bloque);	
        }
    };

    Global.marca_Cargado = function(bloquenombre){
        var name = bloquenombre + "";
        name = name.split("_");
        var bloque = "#"+name[1];

        if($(bloque).length >0){
            $(bloque).removeClass("bloque_cargando");
        }	
    //console.log("Global.marca_Cargado:"+bloquenombre);	
    };
});    
    

/*
 * Inicializacion pagina / bindings
 *
 */
$(function() {
    var t="test";


    var ajaxlist = [];

    /* ---------- ticker de noticias --------------- */

    if(typeof $('#js-news').ticker == "function")
    $('#js-news').ticker({
        displayType: 'fade',
        pauseOnItems: 12000,
        titleText: 'Noticias'   
    });

    /* ----------  --------------- */

    $("#listaUsuarios").change(function(){
        //console.log("listaUsuarios-listaUsuarios");
        Global.cargarAutoBloques("rellamando");	
        Alternativas.click();
    });
    
    $("#codigoCliente").keyup(function(e) {
        if(e.keyCode == 13) {
            Global.cargarAutoBloques("rellamando");
            Alternativas.click();
        }
    });    
    
    $("#codigoArticulo").keyup(function(e) {
        if(e.keyCode == 13) {
            Global.cargarAutoBloques("rellamando");
            Alternativas.click();
        }
    });        
    
    Global.filtraPorCodigoCliente = function(){
	
        var cod = $("#codigoCliente").val()*1;
        $.ajax({
            type: 'POST',
            url: "ajax.php",
            data: "modo=comprobarcodclientevisible&cod_cliente="+jQuery.trim(cod),
            dataType:"json",
            success: function(data){		    
                if(data["visible"]){				
                    Global.cargarAutoBloques("rellamando");
                    Alternativas.click();					
                }else {
                    $("#codigoCliente").val("");
                    $("#codigoCliente_txt").val("&nbsp;");
                    
                    Global.corrige_filtraPorCliente();
                }
            }
        });
                
	
    };
    
    Global.filtraPorCodigoArticulo = function(){	
        var cod = $("#codigoArticulo").val() || "";
        
        if(cod=="") return;
                
        $.ajax({
            type: 'POST',
            url: "ajax.php",
            data: "modo=comprobarcodarticulovisible&cod_articulo="+jQuery.trim(cod),
            dataType:"json",
            success: function(data){		    
                if(data["visible"]){				
                    Global.cargarAutoBloques("rellamando");
                    Alternativas.click();					
                }else {
                    $("#codigoArticulo").val("");
                    $("#codigoArticulo_txt").val("&nbsp;");
                    
                    Global.corrige_filtraPorArticulo();
                }
            }
        });
	
    };
        

    

    var bloquesCache = {};

    Global.cargarAutoBloques = function(modoactual){    
        console.log("cargarAutoBloques: Buscando autobloques..");
        $("#debug3").html(" ");

        //modpanelcomv_incidencias y otros
        $(".autocargarBloque").each(function(){

            if(Global.saliendo) return;

            var bloquenombre = $(this).attr("id");
            var extra = $(this).attr("data-extraparams");
            var dinamico = $(this).attr("data-update");

            //los estaticos no se recargan
            if(modoactual=="rellamando" && dinamico=="estatico")
                //if(dinamico=="estatico")
                return;

            var num = parseInt(Math.random()*900%900+101-1,10);

            //console.log("autobloque:"+bloquenombre+",extra:"+extra+",rnum:"+num);

            if(Global.ocupados[bloquenombre]){

                console.log("autobloque: este bloque parece ocupado..");

                //ya se solicito, estamos esperando
                if(Global.ocupados[bloquenombre].abort){
                    console.log("autobloque: lo abortamos..");

                    Global.ocupados[bloquenombre].abort();//si se puede abortar, pues lo reintantamos
                } else {
                    console.log("autobloque: somos pacientes y esperamos..");

                    return; //si no se puede abortar, pues esperamos
                }
            }
	    
            var filtromodo = Combos.get_filtromodo();//OBSOLETO
            var filtracliente = jQuery.trim($("#codigoCliente").val());
            var filtroarticulo = jQuery.trim($("#codigoArticulo").val());
            var listaid = Combos.get_listaid();//OBSOLETO
            var reply = Combos.get_todo();
            
            if(listaid==-1) filtromodo = "self";

            var urlremota = "memoriabloques.php";
            var urlremota = "bloque_" + bloquenombre + ".php";

            var solicitud = "modulo=memoriabloques&modo="+bloquenombre
            +"&"+extra +"&listaid="+encodeURIComponent(listaid)
            + reply.url
            +"&filtromodo="+filtromodo
            +"&filtracliente="+encodeURIComponent(filtracliente)
            +"&filtroarticulo="+encodeURIComponent(filtroarticulo);
		

            var recuperaBloque = function(data){
                //console.log("[229] recuperaBloque: Cargando data de "+bloquenombre+",listaid:"+listaid+",nL:"+Global.numLlamadas);

                bloquesCache[solicitud] = data;

                Global.ocupados[bloquenombre] = false;
                Global.numLlamadas = Global.numLlamadas-1;

                Global.actualizaIconos();
                Global.debug(data,bloquenombre);

                
                if(!data) {
                    //$("#"+bloquenombre).html("");//porque?
                    console.log("oops.. data parece vacio!")
                    return;
                }

                if(!data["ok"]){
                    console.log("oops.. problema de lado servidor!")
                    
                    if(data["logout"]){
                        alert("La sesión ha terminado, por favor vuelve a loguear");
                    }
                    
                    return;
                }

                if(data["html"]==undefined ) data["html"] = "";
                
                
                var sql = _.escape(data["sql"])+" ";                
                var viewsql = sql.replace(/\n/g, '<br />');                
                //viewsql = viewsql.replace(/ /g, '&nbsp;');                
                $("#debug3").append( $("<br><br /><b>"+bloquenombre+"</b><br /><div class='top'>"+viewsql+ "</div></br>"  ));

                //console.log("[252] recuperaBloque: cargamos datos...("+bloquenombre+"), ahora nL:"+Global.numLlamadas );

                $("#"+bloquenombre).html(data["html"]);

                $("a").click(function(){
                    Global.saliendo = true;
                    $.xhrPool.abortAll();

                    console.log("recuperaBloque: se abortaron todos los ajax..");
                });
		
                Global.hay_cambiosBloques();
                Global.marca_Cargado(bloquenombre);		
            };

            var limpiaSale = function (){
                //console.log("[265] limpiaSale: error carga de "+bloquenombre+",listaid:"+listaid+",nL:"+Global.numLlamadas);

                Global.ocupados[bloquenombre] = false;
                Global.numLlamadas = Global.numLlamadas-1;

                Global.actualizaIconos();
            };

            if(bloquesCache[solicitud]){
                console.log("cargarAutoBloques: recuperando bloque del cache:"+solicitud);

                Global.ocupados[bloquenombre] = llamada;
                Global.numLlamadas = Global.numLlamadas+1;
                
                recuperaBloque( bloquesCache[solicitud] );

                Global.actualizaIconos();
            } else {
		
                Global.marca_Cargando(bloquenombre);
		
                var llamada = $.ajax({
                    type: 'POST',
                    url: urlremota,
                    data: solicitud,
                    dataType:"json",
                    success: recuperaBloque,
                    error: limpiaSale
                });
                
                Global.ocupados[bloquenombre] = llamada;
                Global.numLlamadas = Global.numLlamadas+1;

                //console.log("[286] cargarAutoBloques: hacemos una nueva llamada,nL:"+ Global.numLlamadas);
                Global.actualizaIconos();
            }

            
            

        });
    }; //end cargarautobloque;

    //Global.cargarAutoBloques();

    setTimeout(function(){
        $("#listaEmpresas").change();
    },10);
});
            


/*
 *
 Autoajusta algunos tamaños para mejor visualizacion en pantallas pequeñas
 *     
 */
$(function(){
    var ancho =  screen.width;
    var resclass = "res-" + ancho;

    $("#body").addClass(resclass);

    if (ancho<=1024){
        $("#body").addClass("res-pequegna");
        $("#row1_2").hide();//hack, de momento no se usa.

        $("td").css("font-size","10px");
        $("h4").css("font-size","11px");
    }
});



/*
 * 
 UI_alternativas grafica de ventas o grafica de tiempos
 *
 */
	
var Alternativas = Alternativas || [];

$(function(){   
    Alternativas.seccion = "";
	    
    Alternativas.inicia = function(){								
        $(".js-alternativas a").each(function(){
            $(this).click(function(){
                var rel = $(this).attr("rel")+"";
                rel = rel.split(" ");
                var ocultar = rel[1];
                var mostrar = rel[0];

                //console.log("mostrar:"+mostrar);
			
                $(ocultar).hide();			
                $(mostrar).show();
                Alternativas.seccion = mostrar;
			
			
                $("a",$(this).parent()).addClass("desactivado").removeClass("activado");
                $(this).addClass("activado").removeClass("desactivado");			
                
                //hack                               
                if(Grafica && Grafica.muestra_tiempos)
                    Grafica.dibuja_tiempos_agno();
            });
		    
		    
		   
        });
		
        $(".js-alternativas a").eq(0).click();
    };
	    
    Alternativas.click = function(){		
        if(Alternativas.seccion=="#grafica-ventas"){
            Grafica.cargar_ventas();
        }else {
            Grafica.cargar_tiempos();		    
        }		
    };

    Alternativas.inicia();
});


function recogeSeleccionArticulo(code,name){	
    
    console.log("recogeSeleccionArticulo:code:"+code+",name:"+name);
    
    $("#codigoArticulo").val(code);
    $("#codigoArticulo_txt").html( _.escape(name) );
    
    GB.hide();
    Global.filtraPorCodigoArticulo();
}	    
        
function recogeSeleccionCode(code,name){		    
    $("#codigoCliente").val(code);
    
    $("#codigoCliente_txt").html( _.escape(name) );
    
    GB.hide();
    Global.filtraPorCodigoCliente();            
}	
        
        
$(function(){
            
    $("#codigoArticulo").click(function(){
        GB.buscaCodArticulo();
    });		
            
    $("#codigoCliente").click(function(){                
        GB.buscaCodContacto();
    });
   
   $("#codigoArticulo").change(function(){
        var valor = $(this).val();
        if(valor == ""){            
            $("#codigoArticulo_txt").html(" ");
        }   
   });
   $("#codigoCliente").change(function(){
        var valor = $(this).val();
        if(valor == ""){            
            $("#codigoCliente_txt").html(" ");
        }          
   });



});


$(function(){
    

    var rx_cliente = /([?&]cod_cliente)=([^#&]*)/g;
    var rx_articulo = /([?&]cod_articulo)=([^#&]*)/g;

    Global.corrige_filtraPorCliente = function(){            
        
        
            
        var codigoCliente = jQuery.trim($("#codigoCliente").val());

        if(codigoCliente){
                                    
            var extra = "&cod_cliente="+encodeURIComponent(codigoCliente);

            console.log("se añadira "+extra+" a los enlaces que lo necesiten");

            $("a").each(function(){
                var $this = $(this);
                var href = $this.attr('href') || "";

                if( href.indexOf("cod_cliente")==-1 && href!=""){ //no esta
                    //necesita cod_cliente                    
                    var interogante = href.indexOf("?")==-1?"?":"";//aporta el "?" si es necesario
                    
                    href += interogante + extra;
                }else {
                    href = href.replace(rx_cliente, '$1='+encodeURIComponent(codigoCliente));
                }
                
                $this.attr("href",href);                                                
            });
        }else {
            
            $("a").each(function(){
                var $this = $(this);
                var href = $this.attr('href') || "";

                if( href.indexOf("cod_cliente") && href!=""){ //no esta
                    //quitar cod_* que no necesita
                    href = href.replace(rx_cliente, '');
                    
                    
                    $this.attr("href",href);
                }                
            })   
            
        }
    };

    Global.corrige_filtraPorArticulo = function(){            
        var codigoArticulo = jQuery.trim($("#codigoArticulo").val());

        if(codigoArticulo){
                                    
            var extra = "&cod_articulo="+encodeURIComponent(codigoArticulo);

            //console.log("se añadira "+extra+" a los enlaces que lo necesiten");

            $("a").each(function(){
                var $this = $(this);
                var href = $this.attr('href') || "";

                if( href.indexOf("cod_articulo")==-1 && href!=""){ //no esta
                    //necesita cod_cliente
                    
                    var interogante = href.indexOf("?")==-1?"?":"";//aporta el "?" si es necesario
                    
                    href += interogante + extra;                 
                }else {
                    href = href.replace(rx_articulo, '$1='+encodeURIComponent(codigoArticulo));
                }             
                
                $this.attr("href",href);
            });
        } else {
            
            //console.log("se Quitara articulo a los enlaces que lo necesiten");
            
            $("a").each(function(){
                var $this = $(this);
                var href = $this.attr('href') || "";

                if( href.indexOf("cod_articulo") && href!=""){ //no esta
                    //quitar cod_* que no necesita
                    href = href.replace(rx_articulo, '');
                    
                    //href = href.replace("cod_articulo","x");//innecesario
                    $this.attr("href",href);
                }                
            })            
        }
    };
    

    Global.hay_cambiosBloques = function(){       
        //console.log("LA(lobal.hay_cambiosBloques): limpiando ajustando")
        Global.corrige_filtraPorCliente();  
        Global.corrige_filtraPorArticulo();
    };
    
})
