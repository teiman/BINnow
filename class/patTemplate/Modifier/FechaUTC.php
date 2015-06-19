<?php

class patTemplate_Modifier_FechaUTC extends patTemplate_Modifier {

    function modify($fecha, $params = array()) {
        list($agno, $mes, $dia) = explode("-", $fecha);

        $date = new DateTime("{$agno}-{$mes}-{$dia} 0:0:0", new DateTimeZone("Europe/Madrid"));
        $unix_real = $date->format("U");


        $salida = "<span class='js-autofecha2' data-unixtimeuniversal='$unix_real'>" . $dia . "-" . $mes . "-" . $agno . "</span>";

        return $salida;
    }

}
