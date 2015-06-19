<?php

class patTemplate_Modifier_FechahoraUTC extends patTemplate_Modifier {

    function modify($fecha, $params = array()) {
        //$params = array_merge($this->defaults, $params);

        list($fecha, $hora) = explode(" ", $fecha);
        list($h, $m, $s) = explode(":", $hora);
        list($agno, $mes, $dia) = explode("-", $fecha);

        //$unix_localizado = mktime($h,$m,$s,$mes,$dia,$agno);			    
        //$server_offset = date("O") / 100 * 60 * 60; // Seconds from GMT
        //$unix_real = $unix_localizado - $server_offset;

        $date = new DateTime("{$agno}-{$mes}-{$dia} {$h}:{$m}:{$s}", new DateTimeZone("Europe/Madrid"));
        $unix_real = $date->format("U");


        $salida = "<span class='js-autofecha' data-unixtimeuniversal='$unix_real'><nobr>" . $dia . "-" . $mes . "-" . $agno . "</nobr>" . " " . "<nobr>" . $h . ":" . $m . "</nobr></span>";

        return $salida;
    }

}
