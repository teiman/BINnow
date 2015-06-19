


function muestraMenuSimple(){

    if(muestraMenuSimple.visible){

        $("#root-menu-div").hide();
        muestraMenuSimple.visible = false;
        return;
    }

    $("#root-menu-div").attr("style","");
    $("#root-menu-div").show();
    $("body").click(function(e){
        if(e.pageX>200){
            $("#root-menu-div").hide();
            muestraMenuSimple.visible = false;
        }
    });

    muestraMenuSimple.visible = true;
}

    

$(function(){

   var $logo = $("#botonMenu");
   var $icon = $("<img src='images/show-menu.png' id='tienepunto' style='position:absolute;top:5px;left:8px;display:none'>");

   $("body").append($icon);

   if($logo && $logo.length){
        $logo.hover(
           function() {
                $("#tienepunto").show();
           },
           function() {
                $("#tienepunto").hide();
           }
    );

   }

});

