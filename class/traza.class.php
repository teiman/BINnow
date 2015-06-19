<?php



include_once("class/group.class.php");
include_once("class/users.class.php");


function getNombreEstado($id_estado){
        static $estados = array();

        if (isset($estados[$id_estado]))
            return $estados[$id_estado];


	$sql = "SELECT * FROM status WHERE id_status = '$id_estado' LIMIT 1";
	$row = queryrow($sql);

        $estados[$id_estado] = $row["status"];

	return $row["status"];
}



function getNombreDelegacionTraza($id_location){
    static $estados = array();

    if (isset($estados[$id_location]))
        return $estados[$id_location];

    $id_location_s = sql($id_location);

    $sql = "SELECT name FROM locations WHERE id_location='$id_location_s' ";
    $row = queryrow($sql);

    $nombre = $row["name"];

    //echo "Nombre{$nombre},id_location:$id_location\n";

    $estados[$id_location] = $nombre;

    return $nombre;
}




/*
 *  Devuelve un array con todos los datos de traza de un pedido
 *  @param id_comm
 */
function genTrazaPorPedido($id_comm){

	$id_comm = CleanID($id_comm);

	$sql = "SELECT * FROM trace WHERE (id_comm = '$id_comm') ORDER BY date_change ASC ";
	$res = query($sql);

	$filas = array();

	/*
       	id_comm  	int(10)  	 	UNSIGNED  	No  	 	 	  Navegar los valores distintivos   	  Cambiar   	  Eliminar   	  Primaria   	  Único   	  Índice   	 Texto completo
	id_user 	smallint(5) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_group 	smallint(5) 		UNSIGNED 	No 			Navegar los valores distintivos 	Cambiar 	Eliminar 	Primaria 	Único 	Índice 	Texto completo
	id_status
     */

    //error_log("TRAZA: calculando traza de $id_comm");

    $numFilas = 0;
	$estaba_antes_id = -1;
	$estaba_antes_tiempo = "";
	$estaba_antes_estado = "";
        $totalMinutos = 0;
        $dx = 0;
        $dxtotal = 0;
        $totalSegundos = 0;
        $hora_unix = 0;
        $hora = "";

        $r = intval(rand()*1000);

	while($row = Row($res) ){

            $id_usuario	= $row["id_user"];
            $id_group	= $row["id_group"];
            $id_status	= $row["id_status"];
            $id_delegacion  = $row["id_location"];
            $hora		= $row["date_change"];
            $hora_unix      = strtotime($hora);

            //error_log("TRAZA: calculando hora_unix;$hora_unix,id_delegacion:$id_delegacion,id_status:$id_status");

            if ( $estaba_antes_id >=0 ){  //si estaba antes en una delegacion
                    //ha estado N minutos en $estaba_antes_id
                    $t1 = $hora_unix;
                    $t2 = $estaba_antes_tiempo;

                    $dx = ($t1 - $t2);

                    if ( $estaba_antes_estado != "eliminado" and $estaba_antes_estado!="tramitado"){
                            $minutos[ $estaba_antes_id ] += $dx;
                            $enestados[ $estaba_antes_estado ] += $dx;
                            if ($dx >0)
                                    $totalSegundos += $dx;
                    }

                    $dxtotal = $dxtotal + $dx;
                    //error_log("r($r) dxt{$dxtotal},dx{$dx},t1{$t1},t2{$t2},hora{$hora_unix}");
            }

            $usuario = ( getNombreUsuarioFromId( $id_usuario ) );
            $grupo = ( getNombreGrupoFromId( $id_group ) );
            $estado	= ( getNombreEstado($id_status) );
            $delegacion = getNombreDelegacionTraza($id_delegacion);

            //$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";

            $datosfila = array();

            $datosfila["usuario"] = $usuario;
            $datosfila["grupo"] = $grupo;
            $datosfila["estado"] = $estado;
            $datosfila["tiempo"] = CleanDatetimeDBToDatetimeES($hora);
            $datosfila["tiempoacumulado"] = CleanSecondsAHumano($dxtotal);
            $datosfila["delegacion"] = $delegacion;


            //error_log("TRAZA r($r) dxt{$dxtotal},dx{$dx},t1{$t1},t2{$t2},hora{$hora_unix}");

            $filas[] = $datosfila;

            $estaba_antes_id = $id_delegacion;
            $estaba_antes_tiempo = $hora_unix;
            $estaba_antes_estado = $estado;
            $numFilas++;
	}

	/* Desde el ultimo registro hasta ahora mismo*/
	$hora = time();//date("Y-m-d H:i:s");//hora actual
	if ( $estaba_antes_id ){
            //ha estado N minutos en $estaba_antes_id
            $t1 = $hora_unix;
            $t2 = $estaba_antes_tiempo;

            $dx = ($t1 - $t2)/60;

            if ( $estaba_antes_estado != "eliminado" and $estaba_antes_estado!="tramitado"){
                    $minutos[ $estaba_antes_id ] += $dx;
                    $totalMinutos += $dx;
                    $enestados[ $estaba_antes_estado ] += $dx;
            }
	}

	return $filas;
}


