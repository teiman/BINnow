<?php

class patTemplate_Modifier_Fechahora2 extends patTemplate_Modifier
{
	function modify($fecha, $params = array())
	{
	    //$params = array_merge($this->defaults, $params);

		list($fecha, $hora) = explode(" ",$fecha);
		list($h,$m,$s) = explode(":", $hora);
		list($agno,$mes,$dia) = explode("-",$fecha);

		 $salida = "<nobr>". $dia."-".$mes."-".$agno. "</nobr>". " " . "<nobr>".$h.":". $m . "</nobr>" ;

	    return $salida;
	}
}
