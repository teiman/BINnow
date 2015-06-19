<?php

class patTemplate_Modifier_Fecha extends patTemplate_Modifier
{
	/*
    var $defaults = array(
                        'decimals'  => 2,
                        'point'     => '.',
                        'separator' => ','
                    );
     *
     */
	function modify($fecha, $params = array())
	{
	    //$params = array_merge($this->defaults, $params);

		list($fecha, $hora) = explode(" ",$fecha);
		
	//	list($agno,$mes,$dia) = split("-",$fecha);

	//	list($h,$m,$s) = split(":",$hora);


		list($agno,$mes,$dia) = explode("-",$fecha);
		
	//	$salida = sprintf($params["format"], $dia , $mes , $agno, $h,$m, $s );
		//$salida = sprintf($params["format"], $dia , $mes , $agno );
		 $salida = $dia . "-" . $mes . "-" . $agno ;

		//$templates	= $this->parseString( $salida );
		//echo "salida:$salida,  ", $hora , "| $h, $m, $s, ($salida)". $params["format"]  ." \n";

	    return $salida;
	}
}

?>