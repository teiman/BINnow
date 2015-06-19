
function Eliminarpasarela(){
   if (confirm("¿Quiere eliminar esta pasarela?")){
      location.href="modpasarelas.php?modo=eliminar&id="+id_gateway+"&r=" + Math.random();
   }
}


function ComprobarDatos(){
	var po_aviso = "Todos los campos son obligatorios";

  if ( esVacioCampo("module") ) {
		alert(po_aviso);
        return false;
  }


	return true;
}
