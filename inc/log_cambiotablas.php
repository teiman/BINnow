<?php


function anotar_tabla_actualizada($tabla,$comentario=""){

    $tabla_s = sql($tabla);
    $comentario_s = sql($comentario);

    $sql = "INSERT binow.log_cambiotablas (tabla,actualizada,comentario) VALUES ('$tabla_s',NOW(),'$comentario_s') ";
    query($sql);


    $sql = "UPDATE binow.ag_frescuratablas SET ultima_actualizacion=NOW() WHERE tabla='$tabla_s'";
    query($sql);    
}


function get_fecha_actualizacion($tabla){

    $tabla_s = sql($tabla);
    $sql = "SELECT ultima_actualizacion FROM ag_frescuratablas WHERE tabla='$tabla_s'";
    $row = queryrow($sql);

    return $row["ultima_actualizacion"];
}


