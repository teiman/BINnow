<?php


function CleanParaWeb($valor){
	return htmlentities($valor,ENT_QUOTES,'UTF-8');
}


function CleanFechaFromDB($fecha){
	if ($fecha == "0000-00-00")
		return "";

        if(!$fecha)
                return "";
        if($fecha=="null" or $fecha=="NULL")
                return "";


	list($agno,$mes,$dia) = explode("-",$fecha);
	return($dia . "-" . $mes . "-" . $agno);
}


function CleanDatetimeToFechaES($fecha){
	$datos = explode(" ",$fecha);
	
	return CleanFechaFromDB($datos[0]);	
}


function CleanDatetimeDBToDatetimeES($fecha){
	$datos = explode(" ",$fecha);

	return CleanFechaFromDB($datos[0]). " " . $datos[1];	
}



function CleanFechaES($fecha){
	if (!$fecha)
		return "";

	$fecha	= str_replace("/","-",$fecha);

	if ($fecha == "DD-MM-AAAA")
		return "";
        
	list($dia,$mes,$agno) = explode("-",$fecha);
	return($agno . "-".$mes."-".$dia);
}





function Corta($str,$len,$padstr=".."){
	$reallen = strlen($str);
	$lenpad = strlen($padstr);

	if ($reallen+$lenpad <= $len)
		return $str;

	$newstr = substr($str, 0, $len-$lenpad) .$padstr;

	return $newstr;
}



function CleanInt($int){
	return intval($int,10);	
}


function CleanDinero($val){
	return CleanFloat($val);	
}

//Heavy, quita metacaracteres y espacios. Util para palabras
function CleanTo($text,$to="")  {
	$text = str_replace("'",$to,$text);
	$text = str_replace("\\",$to,$text);
	$text = str_replace("@",$to,$text);
	$text = str_replace("#",$to,$text);
	$text = str_replace(" ",$to,$text);
	$text = str_replace("\t",$to,$text);
	
	return $text;	
}


function CleanText($text){
	return CleanTo($text," ");	
}

function Clean($text){
	return CleanTo($text," ");
}

//Para limpiar nombres
function CleanPersonales($text,$to=" ")  {
	$text = str_replace("'",$to,$text);
	$text = str_replace("\\",$to,$text);
	$text = str_replace("#",$to,$text);
	$text = str_replace(" ",$to,$text);
	$text = str_replace("\t",$to,$text);	
	return $text;	
}


//Para identificadores 
function CleanID($IdentificadorNumerico) {
	return 	intval($IdentificadorNumerico);
}

//Convierte texto en html
function CleanToHtml($str) {	
	$str = htmlentities($str,ENT_QUOTES,'UTF-8'); 
	return str_replace("\n","<br>",$str);	
	//return nl2br($str);
}

function html($str){
	return 	htmlentities($str,ENT_QUOTES,'UTF-8'); ;
}

function entichar($chr){
	return "&#" . ord($chr) . ";";
}


function CleanFloat($val) {	
	$val = str_replace(",", ".", $val );
	return (float)$val;	
}


//Para DNI
function CleanDNI($local) {
	$local = trim($local);
	return strtoupper(trim(CleanTo($local))); 	
}


function sql($dato){
	return CleanRealMysql($dato);
}

function CleanRealMysql($dato,$quitacomilla=true){
	global  $link;
	
	if (!$link){
		//NOTA:
		//  mysql real escape necesita exista una conexion,
		// ..por eso si no hay ninguna establecida, la abrimos. 
		forceconnect();
	}
		
	if ($quitacomilla)
		$dato = str_replace("'"," ",$dato);
	$dato_s = mysqlescape($dato);
	return $dato_s;
}


function FormatMoney($val,$symbol=" &euro;") {
	$val = CleanDinero($val);
	//return htmlentities(money_format('%.2n $euro;', $val),ENT_QUOTES,'ISO-8859-15');
	//return money_format('%.2n &euro;', $val);
	return number_format($val, 2, ',', "."). $symbol;
}


function FormatUnidades($val) {
	return number_format($val, 0, ',', ".");
}


function FormatUnits($val) {
	return $val . " u.";	
}


if(function_exists("iconv")) {
	function iso2utf($text) {	
		return iconv("ISO-8859-1","UTF8",$text);
	}
	function utf8iso($text){
		return iconv("UTF8","ISO-8859-1//TRANSLIT",$text);		
	}	
	
} else {
	//TODO: buscar alternativa que no sea lenta
	function iso2utf($text) {	
		return $text;
	}
	function utf8iso($text){
		return $text;		
	}			
}



function descodifica_mime($var){

        $entra = $var;
	$procesa = $var;
	if(strlen($var)==100 and  stristr($var,"=?") and stristr($var,"?=")===FALSE ){
		$procesa = $var . "?=";
	}

        $mime = false;

	if ( stristr($var,"iso-8859-1?")){
                $mime = "iso-8859-1";
		$newvar = iconv_mime_decode($procesa,2,$mime . "//TRANSLIT");
	} else if ( stristr($var,"iso-8859-15?")){
                $mime = "iso-8859-15";
		$newvar = iconv_mime_decode($procesa,2,$mime . "//TRANSLIT");
	} else if ( stristr($var,"Windows-1252?")){
                $mime = "Windows-1252";
		$newvar = iconv_mime_decode($procesa,2,$mime . "//TRANSLIT");
	} else if (stristr($var,"UTF-8?")){
                $mime = "UTF-8";
		$newvar = iconv_mime_decode($procesa,2,$mime . "//TRANSLIT");
	} else {
                $mime = "UTF-7";
		$txt = preg_replace( '/=\?([^?]+)\?/', '=?iso-8859-1?', $procesa);
		$newvar = iconv_mime_decode( $txt, 0, "UTF-8//TRANSLIT" );
	}
        
	if ($newvar){
            if($mime and $mime!="UTF-8"){
                $newvar = iconv($mime, "UTF-8//IGNORE", $newvar);
            }
        }

        if($newvar)
           $var = $newvar;

	return $var;
}


/**
 * Convert number of seconds into hours, minutes and seconds
 * and return an array containing those values
 *
 * @param integer $seconds Number of seconds to parse
 * @return array
 */
function secondsToTime($seconds)
{
	// extract hours
	$hours = floor($seconds / (60 * 60));

	// extract minutes
	$divisor_for_minutes = $seconds % (60 * 60);
	$minutes = floor($divisor_for_minutes / 60);

	// extract the remaining seconds
	$divisor_for_seconds = $divisor_for_minutes % 60;
	$seconds = ceil($divisor_for_seconds);

	// return the final array
	$ret = array(
		"h" => (int) $hours,
		"m" => (int) $minutes,
		"s" => (int) $seconds,
	);

        if ($hours>0){
            $patron = sprintf("%d horas, %d min %d s",$hours,$minutes,$seconds);
        } else  if($hours<=0 and $minutes>0){
            $patron = sprintf("%d minutos, %d s",$minutes,$seconds);
        } else {
            $patron = sprintf("%d segundos",$seconds);
        }

        $ret["txt"] = $patron;

        return $ret;
}

function CleanSecondsAHumano($seconds){
    $ret = secondsToTime($seconds);

    return $ret["txt"];
}

