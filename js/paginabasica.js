

    $(function() {

        var w= $(window).width();
        w = w - 175;
        var w2 = w;

        $(".cajalistado").css("width",w + "px");
        $(".cajaedicion").css("width",w + "px");

        $(".maximoRazonable").css("width",w2+"px");


        $(".imagebotonborrar").click( function(i){
            if ( confirm("¿Esta seguro de que quiere borrar este elemento?")){
                return true;
            }
            return false;
        });


        $(".imagebotonborrar").attr("title","Borrar");
        $(".imagebotoneditar").attr("title","Editar");

        $(".colbtn").attr("valign","center");

         // Match all link elements with href attributes within the content div
        if(0)
        $('.contooltip_nw').each( function() {

            $(this).qtip( {
                position: { corner:{ tooltip:'bottomLeft',target: 'topRight'} },
                content: '<div> '+ $(this).attr("tooltip")+'</div>', // Give it some content, in this case a simple string
                    style: {
                        border: { width: 1, radius: 3 },
                        tip: true, // Give it a speech bubble tip with automatic corner detection
                        name: 'cream' // Style it according to the preset 'cream' style
                    }
               });
        });


        var href = document.location.href + " ";

        $("#navcontainer li a").each(function(){

            var myhref = $(this).attr("href") + "";
            if ( href.match( myhref ) ){
                $(this).addClass("pageSelected");
            }
        });


        $("#change-list-size").each( function(){
            $(this).html("<option></option><option>5</option>"+
                    "<option>10</option><option>15</option><option>20</option>"+
                    "<option>30</option><option>50</option><option>75</option><option>100</option>");

            $(this).click( function(){

                var listsize = $(this).val();

                if (!listsize) return;

                $("#extracontainer").html("<form id='sendme' style='visibility:hidden' method='post'>"+
                    "<input type='hidden' name='modo' value='change-list-size'>"+
                    "<input type='hidden' name='list-size' value='"+listsize+"'></form>");

                $("#sendme").submit();
            });
        });

        function buscar(){
            var estaBuscando = $(".buscarElementoListado").val();
            var titleViejo = $(".buscarElementoListado").attr("title");
            if (estaBuscando==titleViejo)
                estaBuscando = "";


            $("#filtra-list-value").val(estaBuscando);
            $("#filtra-list").submit();

        };


        $(".buscarElementoListado").keypress(function(event) {
                if (event.keyCode != '13') {
                    //event.preventDefault();
                    return;
                }
                buscar();
        });


        $(".buscarElementoListadoBoton").click( buscar );


        $('.buscarElementoListado').each(function(){

            $(this).attr("title","Buscar..");

            this.value = $(this).attr('title');
            $(this).addClass('text-label');

            $(this).focus(function(){
                if(this.value == $(this).attr('title')) {
                    this.value = '';
                    $(this).removeClass('text-label');
                }
            });

            $(this).blur(function(){
                if(this.value == '') {
                    this.value = $(this).attr('title');
                    $(this).addClass('text-label');
                }
            });
        });

        $("#validateMe").each(function(){
            try{
            jQuery.validator.messages.required = " Este campo es obligatorio. ";

            $(this).validate();
            }catch(e){};
        });

        if(typeof window.postCarga == 'function') {
            postCarga();
        }


        if($("#menufive>img").length>0){
            var options = {minWidth: 200, arrowSrc: 'arrow_right.gif', copyClassAttr: true};
            $('#menufive>img').menu(options, '#menufivelist');
        }


    });
