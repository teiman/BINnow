

$(function() {
        var w= $(window).width();//fix for the evil² ie.
        $("#cabeza").width(w);

        console.log("hello");

        var anchorazonable_txt =(w-165-1) + "px";

        var $lineas_de_comm = $("#lineas_de_comm");
        var $cajafiltros = $("#cajafiltros");

        $("#link_central",$("#navcontainer")).addClass("pageSelected");

        $("#cajaaplicadores",$cajafiltros).hide();

        //Si se pulsa varias veces, solo se envia una
        $("#tramitados_chk",$cajafiltros).click(function(){
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


        $("#lista_etiquetas_status",$cajafiltros).change( enviaMe ).data("master","#etiquetador");
        $("#lista_status",$cajafiltros).change( enviaMe ).data("master","#etiquetador3");
        $("#lista_etiquetas_locations",$cajafiltros).change( enviaMe ).data("master","#etiquetador4");
        $("#lista_etiquetas_cotizacion",$cajafiltros).change( enviaMe ).data("master","#etcot");
        $("#lista_etiquetas_siniestros",$cajafiltros).change( enviaMe ).data("master","#etsin");


        $("form.autoenviocambia select").change(function(){
            genLista();

            var newval = $(this).val;
            if(!newval || newval==-1) return;

            $(this).closest("form").submit();
        });

        $(".need_genlista",$cajafiltros).submit( genLista );

        $(".selcomm",$lineas_de_comm).change(function(){
                if ( $('.selcomm:checked',$lineas_de_comm).length>0 ){
                        $("#cajaaplicadores",$cajafiltros).show();
                } else {
                        $("#cajaaplicadores",$cajafiltros).hide();
                }
        });


        $(".filaDatos",$lineas_de_comm).click( function(){
                var $this = $(this);
                var myid = new String( $this.attr("id") );
                var myid2 = myid.replace("datos_","");

                $("#a_"+myid2).click();
        });


        if ( $.browser.msie ){
                $('.ik').ifixpng();
        }

        Global.altaComentario = $("#altaComentario").clone();
        $("#altaComentario").remove();

        $("#buscaid_contacto_txt",$cajafiltros).click(function(){buscaIdContacto()} );


        setTimeout(function(){
                    //$('.local-load',$("#lineas_de_comm")).cluetip({local:true,tracking: true});

                    if($('.local-load',$lineas_de_comm).length>0)
                        $('.local-load',$lineas_de_comm).cluetip({local:true,tracking: true});
        },0);

        setTimeout(function(){
                    if($("ul.sf-menu").length>0)
                        jQuery('ul.sf-menu').superfish();
        },0);


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

});


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

    if($("#datos_"+id_comm).length){
        $("#datos_"+id_comm).removeClass("sinleer");
        $("#datos_"+id_comm).addClass("leido");
        
    }

	var enlace = "#a_" +id_comm;

	if ($(enlace).length){
		$(enlace).removeClass("sinleer");
		$(enlace).addClass("leido");
	}

	var name = "#contenedor_" + id_comm;

	if ( $(name).length  ){
		$(name).html( htmlCargando );

		var nombre2 = "#titulo_" + id_comm ;
		var nombre3 = "#datos_" + id_comm ;

		//$(nombre2).addClass("seleccionado");
		//$(nombre3).addClass("seleccionado_datos");
                $(nombre3).addClass("seleccionado_cabecera");

		$.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: "modo=cargaSolapa&id_comm="+id_comm,
                    success: function(datos){
                        try {
                            //alert(datos);
                            var obj = eval("(" + datos + ")");
                            if(checkUnlogin(obj)) return;

                            if (obj.ok) {
                                var nameent = "#contenedor_" + obj.id_comm;
                                $(nameent).html("<div class='solapascontenedor_sinalto'>"+ obj.html+"</div>" );

                                if ( AmpliarSolapasDeComm.ultimaAmpliada != obj.id_comm)
                                        CerrarSolapa(AmpliarSolapasDeComm.ultimaAmpliada);
                                AmpliarSolapasDeComm.ultimaAmpliada = obj.id_comm;

                                Pagina.bindings_solapa();


                                //$(".filaListado[id!=datos_4744]").css("opacity","0.5");
                            }
                        }catch(e){
                                alert("ERROR: " + e);
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

    //$(".filaListado").css("opacity","1");
}


function marcaSolapaAbierta( modo){
	var nameClaseSolapasel ="solapasel";
	$("#es_"+modo).addClass( nameClaseSolapasel );
}

function RecargaSolapaModo(id_comm,modo){
	var name = "#contenedor2_" + id_comm;


	var nameClaseSolapasel ="solapasel";
        var $contenedor = $(name);

	$("#es_datos",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_documento",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_traza",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_etiquetas",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_riesgo",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_reenviar",$contenedor).removeClass(nameClaseSolapasel);
	$("#es_reglas",$contenedor).removeClass(nameClaseSolapasel);

	$("#es_"+modo,$contenedor).addClass( nameClaseSolapasel );



	if ( $(name).length  ){

		var nombre2 = "#titulo_" + id_comm ;
		var nombre3 = "#datos_" + id_comm ;

		//$(nombre2).addClass("seleccionado");
		///$(nombre3).addClass("seleccionado_datos");

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
								var nombre = "#contenedor2_" + obj.id_comm ;
								$(nombre).html( obj.html );
							}
						}catch(e){
							alert("ERROR: " + e+ ", code:"+datos);
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

	//console.log("Revelador:,t:"+t+",namepossible:"+namepossible+",altoindex:"+Revelador.altoindex+",bajoindex:"+Revelador.bajoindex);

	if ( $(namepossible).length ){
		$(namepossible).show();//debe ser simplemente show, porque si utilizamos algo mas espectacular, se estropea el css
		$(namepossible2).show();

		//		$('.local-load').cluetip({local:true,tracking: true, zshowTitle: false});

		setTimeout(function(){
			$(namepossible3).cluetip({local:true,tracking: true});
			//console.log('haciendo tooltip para:'+namepossible3);
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

		//console.log("empieza:,desde:"+desde+",hasta:"+hasta);

		//que esta activo y debe correr
		Revelador.activo = true;

		//ajusta margenes
		Revelador.bajoindex = desde;

		if (!Revelador.altoindex)
			Revelador.altoindex = hasta;

		setTimeout("Revelador()",200);//empieza
}


function MasLineas(){
    $.ajax({
        type: "POST",
        url: "modcentral.php",
        data: "modo=cargarMasLineas&last_id_comm=" + MasLineas.last_id_comm+"&offset="+(MasLineas.offset),
        success: function(datos){
            try {
                var obj = eval("(" + datos + ")");
                if(checkUnlogin(obj)) return;

                if (obj.ok) {
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

                            Revelador.empezar(newidcom,oldidcom);
                    } else {
                            MasLineas.offset += parseInt(obj.paginasize,10);
                    }
                }
            }catch(e){
                    alert("ERROR: " + e+ ", code:"+datos);
            }
          }
    });
    return false;
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





