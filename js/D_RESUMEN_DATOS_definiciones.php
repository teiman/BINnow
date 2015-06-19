<?php

chdir("..");
include("tool.php");

header("Content-type: application/javascript");


$tabla = "D_RESUMEN_DATOS";


echo "var columnas = [";


$sql = "SELECT * FROM D_CAMPOS WHERE tabla_nueva = '$tabla' ";


$res = query($sql);

while($row = Row($res)){
    $id_global = $row["id_global"];
    $qq_descripcion_humana = @json_encode($row["descripcion_humana"]);//tiene que ser UTF8!
    if ($qq_descripcion_humana=="null"){
        $qq_descripcion_humana = json_encode(iso2utf($row["descripcion_humana"]));//tiene que ser UTF8!
    }


    $tipo_js = $row["tipo_js"];
        
    $tipo = $tipo_js;

    $oculto = $row["oculto_js"]?1:0;

    echo "{\"id\":\"$id_global\",\"nombre\":$qq_descripcion_humana,\"tipo\":\"$tipo\",\"oculto\":\"$oculto\"},\n";
}

echo "{}];\n";

echo "var trans = {\n";

mysql_data_seek($res,0);

while($row = Row($res)){

    $qq_descripcion_humana = @json_encode($row["descripcion_humana"]);//tiene que ser UTF8!
    if ($qq_descripcion_humana=="null"){
        $qq_descripcion_humana = json_encode(iso2utf($row["descripcion_humana"]));//tiene que ser UTF8!
    }

    $id_global = $row["id_global"];

    echo "'$id_global':" .$qq_descripcion_humana . ",\n";
}

echo "'zguardianz':false};\n";



?>


var columnas_id2tipo = {};

columnas_id2tipo._tabla = "D_RESUMEN_DATOS";


var columnas_virtuales = [ "D_RESUMEN_DATOS_devolucion_neta_pkin" ];


