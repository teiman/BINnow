


    $(function() {

        function inString(str)
        {
            return str.replace(/[^A-Za-z0-9]/g, '_') ; 
        }


        $(".autooculta").each(function(){
               //$(this)[0].classList
               var lista = $(this).attr('class').split(" ");
               var len = lista.length; 
               var encontrada = false;

               for(t=0;t<len;t++){
                    var clase = lista[t] + "";
                    if(clase.indexOf("autooculta_") == 0){
                        var encontrada = "." + clase.replace("autooculta_", "");
                        break;   
                    }
               }
               
               var GUID = inString(document.location.href)+"_autooculta_"+inString(encontrada);

               //console.log("encontrada:"+encontrada+",en:"+GUID);                       

               if(encontrada){
                    $(this).attr("id",GUID);
                    //$(this).data("guid","on");

                    var actual = "on";
                    var possible = $.storage({get:GUID});                   
                    
                    if (possible =="off"){
                       actual = "off";     
                    } 

                    //console.log("guardado:"+possible+",en:"+GUID);

                    $(this).data("activo",actual);

                    
                    $(this).click(function(){
                        $(encontrada).toggle();
                        var actual = ($(this).data("activo")=="on")?"off":"on";
                        $(this).data("activo",actual);                        

                        $.storage({id:GUID,value:actual}); 
                        console.log("update:"+actual+",en:"+GUID);                       
                    });        

                    if(actual=="off"){
                        $(encontrada).hide();                        
                    }           
               }
        });        



    });
