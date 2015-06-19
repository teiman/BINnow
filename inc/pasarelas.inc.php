<?php

/**
 * Ayudas para escribir gateways
 *
 * @package binow
 */

function marca($texto=false){
	return time() . ": $texto";
}

function unix(){
    return date("U")." ";
}


/**
 * calcula el nombre de carpeta conocido el nombre del modulo
 * ocr.gw.php =>   ocr.gw
 * @param string nombre del modulo
 * @return carpeta donde esta el modulo
 */
function genCarpetaPasarela($selected){
    $selected = $selected . "finalfichero";

    $out = str_replace(".phpfinalfichero","",$selected);
 
    return $out;
}



function archivadorOnline( $sourceFile, $viewFile , $tag ){
	global $cr;

	$out = array();


	$sources = getParametro("gw_sourcefiles_path");
	$viewfiles = getParametro("gw_viewfiles_path");

	$parts = pathinfo( $sourceFile );
	$newSourceName = md5($sourceFile) .".". $parts["extension"];


	$ficheroDestino = NormalizarPath($sources . "/") . $newSourceName;


	if ( file_exists($sourceFile) ){
		if ( copy( $sourceFile, $ficheroDestino ) ){
			echo time() . "Se ha copiado SRC a [$ficheroDestino]" . $cr;
			$out["source"] = $newSourceName;
		} else {
			echo time() . "No se ha podido crear [$ficheroDestino]" . $cr;
		}

	} else {
		echo time() . ": se esperaba '$sourceFile' pero no se encontro" . $cr;
	}

	$parts = pathinfo( $viewFile );
	$newViewName = md5($viewFile) .".". $parts["extension"];

	$ficheroDestino = NormalizarPath($viewfiles ) . $newViewName;

	if ( file_exists($viewFile) ){
		if ( copy( $viewFile, $ficheroDestino ) ){
			//$pedido->NormalizacionNombrePDF($nuevoNombrefichero);//se guarda
			echo time() . "Se ha copiado VIEW a [$ficheroDestino]" . $cr;
			$out["viewfile"] = $newViewName;
		} else {
			echo time() . "No se ha podido crear [$ficheroDestino]" . $cr;
		}

	} else {
		echo time() . ": se esperaba '$viewFile' pero no se encontro" . $cr;
	}


	return $out;
}

