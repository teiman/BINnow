<?php

function genera_claves_trans($tabla_nueva ="D_RESUMEN_DATOS"){
    $datos = array();

    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva='$tabla_nueva' ";

    $res = query($sql);

    while($row=Row($res)){

        $key = $row["id_global"];
        $value = iso2utf($row["descripcion_ascii"]);
        //$value = str_replace(".",". ",$value);//??


        $datos[$key] = $value;
    }

    return $datos;
}

$claves_trans = genera_claves_trans();


function code2nombre($code){
    global $code2txt,$claves_trans;
    if(isset($code2txt[$code])){
        return $code2txt[$code];
    }

    if (isset($claves_trans[$code])){
        $name = $claves_trans[$code];
        return $code2txt[$name];
    }

    return "";
}

function genera_code2txt($tabla_nueva ="D_RESUMEN_DATOS"){
    $datos = array();

    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva='$tabla_nueva' ";

    $res = query($sql);

    while($row=Row($res)){

        $key = $row["descripcion_ascii"];
        $value = iso2utf($row["descripcion_humana"]);
        $value = str_replace(".",". ",$value);


        $datos[$key] = $value;
    }

    return $datos;
}


$code2txt = genera_code2txt();


function genera_code2tipo($tabla_nueva ="D_RESUMEN_DATOS"){
    $datos = array();

    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva='$tabla_nueva' ";

    $res = query($sql);

    while($row=Row($res)){

        $key = $row["descripcion_ascii"];
        $value = $row["tipo_js"];

        switch($value){
            default:
            case "nombre":
                break;
        }

        $datos[$key] = $value;
    }

    return $datos;
}


function code2tipo($code,$tabla="D_RESUMEN_DATOS"){
    global $code2tipo;
    if(isset($code2tipo[$code])) return $code2tipo[$code];

    $code = str_replace($tabla. "_","",$code);

    return $code2tipo[$code];
}

$code2tipo = genera_code2tipo();


function genera_code2mysqltipo($tabla_nueva ="D_RESUMEN_DATOS"){
    $datos = array();

    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva='$tabla_nueva' ";

    $res = query($sql);

    while($row=Row($res)){

        $key = $row["descripcion_ascii"];
        $value = $row["tipo_mysql"];

        $datos[$key] = $value;
    }

    return $datos;
}


$code2mysqltipo = genera_code2mysqltipo("D_RESUMEN_DATOS");




