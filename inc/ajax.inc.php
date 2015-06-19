<?php


//$json = new Services_JSON();

function getNumFax($id_delegacion){
	global $FilasAfectadas;

	$id_delegacion = CleanID($id_delegacion);
	$sql = "SELECT 1 FROM pedidos WHERE medio_recepcion='F' AND eliminado=0
	AND estado!='tramitado' AND estado!='eliminado' AND id_delegacion='$id_delegacion'";

	query($sql);
	return $FilasAfectadas;
}

function getNumEmails($id_delegacion){
	global $FilasAfectadas;

	$id_delegacion = CleanID($id_delegacion);
	$sql = "SELECT 1 FROM pedidos WHERE medio_recepcion='E' AND eliminado=0 AND estado!='tramitado' AND estado!='eliminado' AND id_delegacion='$id_delegacion'";

	query($sql);

	return $FilasAfectadas;
}

function getNum($estado,$medio){
	global $FilasAfectadas;

	$sql = "SELECT 1 FROM pedidos WHERE medio_recepcion='$medio' AND eliminado=0 AND estado='$estado'";

	query($sql);

	return $FilasAfectadas;
}




function getNumRetenido($medio,$id_delegacion=0){
	global $FilasAfectadas;

	$partedelegacion = "";

	$horas = intval(getParametro("tiempo_alerta"));

	$segundoscaduca = 3600 * $horas;

	$id_delegacion = CleanID($id_delegacion);

	$medio = sql($medio);

	if ($id_delegacion>0){
		$partedelegacion = " AND (id_delegacion ='$id_delegacion') ";
	}

	$sql = "SELECT 1 FROM pedidos WHERE eliminado=0 AND estado!='tramitado' AND estado!='eliminado' AND ((UNIX_TIMESTAMP()-UNIX_TIMESTAMP(fecha_captura)) > $segundoscaduca ) $partedelegacion AND (medio_recepcion='$medio')";
	query($sql);

	return $FilasAfectadas;;
}

function getarrayDelegacionesAcceso(){

    $delegacionesAccesibles = array();
      $sql = "SELECT id_delegacion FROM delegaciones WHERE eliminado=0";
      $res = Query($sql);
      while($row = Row($res)){
        $id_delegacion = $row["id_delegacion"];
        $auth = autorizacionesPantalla($id_delegacion);
        if ($auth["p3"] =="RW" or $auth["p3"] == "RO"){
            $delegacionesAccesibles[] = $id_delegacion;
        }
      }
      return $delegacionesAccesibles;
}
