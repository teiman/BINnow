<?php

$serial_registra = "[".$_SERVER["SCRIPT_NAME"].":". rand(9000000,99999999). "]";//genera un id "unico" que nos sirve para identificar procesos

function registra($texto){
    global $serial_registra;
    $txt = date("Y-m-d H:i:s") . " ". $serial_registra ." " . $texto ;
    error_log($txt . "\n",3,"/var/www/registros/registro.log");//TODO: logrotate
    echo $txt ." <br>\n" ;

    @ob_flush();
    @flush();
}


function gen_sql_copiaDatos_completa($tabla_origen= "D_Empleados",$tabla_destino= "D_RESUMEN_DATOS"){
    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva = '$tabla_destino'  AND nombre_jd != '' ";
    $res = query($sql);

    while($row=Row($res)){
        $select[] = $row["descripcion_ascii"];

        $nombrenew = $row["descripcion_ascii"];
        $nombrejd = $row["nombre_jd"];

        $tipo_conversion = $row["tipo_conversion"];
        switch($tipo_conversion){
             case "string2index":
                $selectjd[] = " ASCII($nombrejd)";
                break;
             case "cantidad/100":
                $selectjd[] = " $nombrejd* 0.01 as $nombrenew ";//divide entre 100
                break;
             case "int4moneda":
                $selectjd[] = " $nombrejd* 0.0001 as $nombrenew "; //divide entre 10000
                break;
             case "int2moneda":
                $selectjd[] = " $nombrejd* 0.01 as $nombrenew ";//divide entre 100
                break;
             case "juliana2date":
                $selectjd[] = " MAKEDATE(LEFT($nombrejd, 3)-100 +2000,RIGHT($nombrejd,3) ) ";
                break;
             default:
                 $selectjd[] = $nombrejd; //simplemente se copia
        }

    }

    $dsttabla = "binow.". $tabla_destino;
    $orgtabla = "jd_entrada.". $tabla_origen;

    $out = "INSERT $dsttabla \n";
    $out .= "(". join(",",$select) .")\n";
    $out .= " SELECT " . join(",",$selectjd) . "\n";

    $out .= "FROM $orgtabla ";

    return $out;
}


function gen_sql_copiaDatos_interna($tabla_nombre= "D_RESUMEN_DATOS",$tabla_origen="D_Empleados_RAW", $tabla_destino= "D_Empleados"){
    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva = '$tabla_nombre'  AND nombre_jd != '' ";
    $res = query($sql);

    while($row=Row($res)){
        $select[] = $row["nombre_jd"];

        $nombrenew = $row["nombre_jd"];
        $nombrejd = $row["nombre_jd"];

        $selectjd[] = $nombrejd; //simplemente se copia
    }

    $dsttabla = "jd_entrada.". $tabla_destino;
    $orgtabla = "jd_entrada.". $tabla_origen;

    $out = "INSERT $dsttabla \n";
    $out .= "(". join(",",$select) .")\n";
    $out .= " SELECT " . join(",",$selectjd) . "\n";

    $out .= "FROM $orgtabla ";

    return $out;
}


function gen_sql_copiaDatos($tabla_origen= "D_Empleados",$tabla_destino= "D_RESUMEN_DATOS"){
    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva = '$tabla_destino' AND nombre_jd != '' ";
    $res = query($sql);

    while($row=Row($res)){
        $select[] = $row["descripcion_ascii"];

        $nombrenew = $row["descripcion_ascii"];
        $nombrejd = $row["nombre_jd"];

        $tipo_conversion = $row["tipo_conversion"];
        switch($tipo_conversion){
             //case "string2index":
             //   $selectjd[] = " ASCII($nombrejd)";
             //   break;
             case "cantidad/100":
                $selectjd[] = " $nombrejd* 0.01 as $nombrenew ";//divide entre 100
                break;
             case "int4moneda":
                $selectjd[] = " $nombrejd* 0.0001 as $nombrenew "; //divide entre 10000
                break;
             case "int2moneda":
                $selectjd[] = " $nombrejd* 0.01 as $nombrenew ";
                break;
             case "juliana2date":
                $selectjd[] = " MAKEDATE(LEFT($nombrejd, 3)-100 +2000,RIGHT($nombrejd,3) ) as $nombrenew ";
                break;
             default:
                 $selectjd[] = $nombrejd; //simplemente se copia
        }

    }

    $dsttabla = "binow.". $tabla_destino;
    $orgtabla = "jd_entrada.". $tabla_origen;

    $out = "INSERT $dsttabla \n";
    $out .= "(". join(",",$select) .")\n";
    $out .= " SELECT " . join(",",$selectjd) . "\n";

    $out .= "FROM $orgtabla WHERE $tabla_origen.es_nuevo=1 ";

    return $out;
}



function gen_sql_updateDatos($tabla_origen= "D_Empleados",$tabla_destino= "D_RESUMEN_DATOS"){
    $sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva = '$tabla_destino'  AND nombre_jd != ''  ";
    $res = query($sql);

    $updates = array();

    while($row=Row($res)){
        $nombrenew = $row["descripcion_ascii"];
        $nombrejd = $row["nombre_jd"];

        $tipo_conversion = $row["tipo_conversion"];
        switch($tipo_conversion){
             case "cantidad/100":
                $jdvalue = " T2.$nombrejd* 0.01  ";//divide entre 100
                break;
             case "int4moneda":
                $jdvalue = " T2.$nombrejd* 0.0001  "; //divide entre 10000
                break;
             case "string2index":
                $jdvalue = " ASCII(T2.$nombrejd)";
                break;
             case "int2moneda":
                $jdvalue = " T2.$nombrejd* 0.01  ";
                break;
             case "juliana2date":
                $jdvalue = " MAKEDATE(LEFT(T2.$nombrejd, 3)-100 +2000,RIGHT(T2.$nombrejd,3) ) ";
                break;
             default:
                $jdvalue = "T2.". $nombrejd; //simplemente se copia
                break;
        }

        $updates[] =  "T1.". $row["descripcion_ascii"] ." = $jdvalue ";
    }

    $dsttabla = "binow.". $tabla_destino;
    $orgtabla = "jd_entrada.". $tabla_origen;

    $out = "UPDATE $dsttabla as T1,$orgtabla as T2 \n";
    $out .= "SET ". join(", ",$updates) ."\n";
    $out .= " WHERE T2.es_nuevo=0 and T1.id_venta=T2.id_venta_destino  ";

 
    return $out;
}