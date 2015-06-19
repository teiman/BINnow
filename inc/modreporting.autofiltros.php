<?php

function getUserAttribute($dato,$datosUsuario){
    $val = "";

    return $val;
}

/* Simulando */



$id_user_s = sql(getSesionDato("id_user"));       
$datosUsuario = getSesionDato("user_data");//queryrow("SELECT * FROM users WHERE id_user='$id_user_s' ");
//NOTA: se puede coger esta informacion de getSesionDato("user_data"), y no haria falta lanzar esta query. Pero entonces los cambios no serian inmediatos

$nombre = $datosUsuario["name"];


$autofiltros = array();

$datoNulo = -1;//indica "deselige"

$filtramodo = $_REQUEST["filtromodo"];

if($_REQUEST["listaid"]==$datoNulo)
    $filtramodo = "";

aplicarAutoFiltros($autofiltros,$user_type,$datosUsuario);



/*
 * popula un array con filtros apropiados para este usuario
 *
 * @param autofiltros  array a popular
 * @param user_type tipo de usuario
 * @param datosUsuarios array con todos los datos conocidos de este usuario
 */
function aplicarAutoFiltros(&$autofiltros,$user_type,$datosUsuario){
    //Posible expansion aqui
}
