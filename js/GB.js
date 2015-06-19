    GB_ANIMATION = false;

    function genBox(){
        GB_show("Login","login.php?modo=popup",470,600);
        return false;
    }

    /*
    //break frames, util cuando venimos de un popup de login
    if (top.location != location) {
              top.location.href = document.location.href ;
    }
    */

    function buscaIdContacto(){
        GB_show("Seleccion cliente","ajax.php?modo=selectcontact&r="+Math.random(),470,600);
    }


    function recogeSeleccion( id, name ) {
        $("#buscaid_contacto_txt").val(name);
        $("#buscaid_contacto").val(id);
        GB_hide();

        if(name && id>0)
            $("#buscaid_contactoform").submit();
    }

    function resetContact(){
        $("#buscaid_contacto_txt").val("");
        $("#buscaid_contacto").val(0);
    }


    function CajaOpinar(){
        GB_Area(po_gb_recepcionllamada,"opinar","altaComentario",400,600);
        //var receptor = "ECOMM:<patTemplate:var name="user_nombreapellido"/>";
        var receptor = "binow:";
        $("input[name=aquien]").val(receptor);
        $("input[name=quien]").val("");
        $("input[name=escorreoelectronico]").val(0);
        $("input[name=esllamadarecibida]").val(1);
        $(".cargafichero").addClass("oculto");
        $(".focuseame").delay(1).focus();
        $(".carganotas").removeClass("oculto");
        $(".mododeenvio").val("agnadirllamada");

        return false;
    }

    function CajaLlamar(){
        GB_Area(po_gb_recepcion_realizada,400,600);

        //var receptor = "binow:<patTemplate:var name="user_nombreapellido"/>";
        var receptor = "binow:";
        $("input[name=quien]").val(receptor);
        $("input[name=aquien]").val("");
        $("input[name=esllamadarecibida]").val(0);
        $("input[name=escorreoelectronico]").val(0);
        $(".mododeenvio").val("agnadirllamada");
        $(".cargafichero").addClass("oculto");
        $(".carganotas").removeClass("oculto");
        $(".focuseame2").delay(1).focus();

        return false;
    }

    function EscribirEmails(){
        GB_Area(po_gb_enviocorreo,"opinar","altaComentario",400,600);

        //var receptor = "ECOMM:<patTemplate:var name="user_nombreapellido"/>";
        var receptor = "binow:";
        $("input[name=quien]").val(receptor);
        $("input[name=aquien]").val("");
        $("input[name=esllamadarecibida]").val(0);
        $("input[name=escorreoelectronico]").val(1);
        $(".mododeenvio").val("enviaremail");

        $(".cargafichero").addClass("oculto");

        $(".bloquedatocorreo").removeClass("oculto");
        $(".carganotas").removeClass("oculto");

        $(".focuseame2").delay(1).focus();

        return false;
    }

    function EscribirFax(){
        GB_Area(po_gb_enviofax,"opinar","altaComentario",400,600);

        //var receptor = 'ECOM:<patTemplate:var name="user_nombreapellido"/>';
        var receptor = 'binow:';
        $("input[name=quien]").val(receptor);
        $("input[name=aquien]").val("");
        $("input[name=esllamadarecibida]").val(0);
        $("input[name=escorreoelectronico]").val(1);
        //$(".mododeenvio").val("enviaremail");
        $(".mododeenvio").val("enviarfax");


        $(".cargafichero").removeClass("oculto");
        $(".carganotas").addClass("oculto");


        $(".bloquedatocorreo").removeClass("oculto");

        $(".focuseame2").delay(1).focus();

        return false;
    }
