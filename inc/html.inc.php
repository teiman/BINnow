<?php

/**
 * Ayudas para generar html
 *
 * @package binow
 */


function configNavegador( &$page,$min, $maxfilas,$numFilas ){//NOTA: posiblemente obsoleto
	global $template;

	
	
	$siguienteDisabled = "";
	$anteriorDisabled = "";

	$numActivos = 0;
	$pagSiguiente = 0;
	$pagAnterior = 0;

	$numActivos = 0;

	if ( $min>=$maxfilas) {
		$pagAnterior = $min - $maxfilas;
		$numActivos++;
	}  else {
		$anteriorDisabled = "disabled='disabled'";
	}

	if  ($numFilas < $maxfilas) {
		$pagSiguiente = $min;
		$siguienteDisabled = "disabled='disabled'";
	} else {
		$numActivos++;
		$pagSiguiente = $min + $maxfilas;
	}


	if (!$numActivos) return;//no hay botones activos, asi que ocultamos el navegador, que no es necesario.


	$page->setAttribute( 'navegador', 'src', 'navegador.htm' );



	$page->addVar( 'navegador', 'modname', $template["modname"] );

	$page->addVar( 'navegador', 'paganterior', $pagAnterior );
	$page->addVar( 'navegador', 'pagsiguiente', $pagSiguiente );

	$page->addVar( 'navegador', 'antdisabledhtml', $anteriorDisabled );
	$page->addVar( 'navegador', 'sigdisabledhtml', $siguienteDisabled );
}



function g($tag="br",$txt ="", $clas="") {
	if($clas!="")
		$clas = " class=\"$clas\" ";
	
	return "<$tag $clas>$txt</$tag>";
}
 
 
function gColor($color,$txt,$bold=false){
	if(!$bold)
		return "<font color='$color'>$txt</font>";
	return "<font color='$color'><b>$txt</b></font>";
			
}
 