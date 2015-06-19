


function EliminarUsuario(){
   if (confirm("¿Quiere eliminar este usuario?")){
      location.href="modusuarios.php?modo=eliminar&id="+Global.id+"&r=" + Math.random();
   }
}


function Eliminar(){
	EliminarUsuario();
}

function ComprobarDatos(){

	var po_aviso = "Todos los campos son obligatorios";

  if ( esVacioCampo("name") ) {
		alert(po_aviso);
        return false;
  }

  if ( esVacioCampo( "user_login" )) {
		alert(po_aviso);
       return false;
  }

  if ( esVacioCampo( "pass_login" )) {
		alert(po_aviso);
       return false;
  }
  
  
  /*
	if (esVacio( Email) ){
       alert("La dirección de email es obligatoria");
       return false;
  }

  if ( Email.indexOf("@") == -1){
       alert("El formato de la dirección de email es erróneo");
       return false;
  }

  if ( Email.indexOf(".") == -1){
       alert("El formato de la dirección de email es erróneo");
       return false;
  }

  if ( Email.indexOf("http://") >=0 ){
       alert("El formato de la dirección de email es erróneo");
       return false;
  }*/

  return true;
}



function addGrupoCaja(newgroup){

	removeGrupoCaja(newgroup);//normalmente no sera necesario
	var grupos =	$("#groupismember").val();
	grupos = grupos + "," + newgroup;
	$("#groupismember").val(grupos);

}


function removeGrupoCaja(viejogrupo){


	var grupos = 	$("#groupismember").val();


	grupos = grupos.replace(viejogrupo,"");
	grupos = grupos.replace(",,",",");

	$("#groupismember").val(grupos);
}



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
								//console.log("cambiando contenido...");
								$("#"+obj.tooltip_id).html( obj.html );
								//console.log("...cambiado contenido");
							}
						}catch(e){
							//alert("ERROR: " + e+ ", code: ...");
							alert("Code: "+datos);
							//console.log("ERROR:" + e + "\n" +datos);
						}
				  }
		});
}


function AgnadirGrupo(){
	var id_grupo = $("#elijeGrupo").val();
	var textgrupo=  $("#elijeGrupo option:selected").text();

	var canAdd = true;

	$("#grupoEsMiembro option").each( function(){

		var myval = $(this).val();
		if (myval == id_grupo)
			canAdd = false;
	});

	if (!canAdd)
		return;

	var html = $('<div/>').text(textgrupo).html();

	addGrupoCaja(id_grupo);

	var newOption =  $("<option value='"+id_grupo+"'>" + html + "</option>");

	$(newOption).dblclick( function(){
		removeGrupoCaja( $(this).val() );
		$(this).remove();
	});

	$(newOption).click( function(){
            if(prompt("Eliminar grupo?")){
		removeGrupoCaja( $(this).val() );
		$(this).remove();
            }
	});

	$("#grupoEsMiembro").append(newOption );

}

function AgnadirGrupo2(){
	var id_grupo = $("#elijeGrupo2").val();
	var textgrupo=  $("#elijeGrupo2 option:selected").text();

	var canAdd = true;

	$("#grupoEsMiembro2 option").each( function(){

		var myval = $(this).val();
		if (myval == id_grupo)
			canAdd = false;
	});

	if (!canAdd)
		return;

	var html = $('<div/>').text(textgrupo).html();

	addGrupoCaja(id_grupo);

	var newOption =  $("<option value='"+id_grupo+"'>" + html + "</option>");

	$(newOption).dblclick( function(){
		removeGrupoCaja( $(this).val() );
		$(this).remove();
	});

	$(newOption).click( function(){
            if(prompt("¿Eliminar grupo?")){
                removeGrupoCaja( $(this).val() );
                $(this).remove();
            }
	});

	$("#grupoEsMiembro2").append(newOption );

}


function postCarga(){


	$(".removeOnClick").dblclick( function(){
		removeGrupoCaja( $(this).val() );
		$(this).remove();
	});

}




