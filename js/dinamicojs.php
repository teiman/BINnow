<?php


chdir("..");
include("config/config.php");


function is_firefox() {
	$agent = '';
	// old php user agent can be found here
	if (!empty($HTTP_USER_AGENT))
		$agent = $HTTP_USER_AGENT;
	// newer versions of php do have useragent here.
	if (empty($agent) && !empty($_SERVER["HTTP_USER_AGENT"]))
		$agent = $_SERVER["HTTP_USER_AGENT"];
	if (!empty($agent) && preg_match("/firefox/si", $agent))
		return true;
	return false;
}


date_default_timezone_set ("Europe/Berlin");





$desactivarFirebug = !$usandoFirebug;

define('TIME_BROWSER_CACHE','36000');
$last_modified = filemtime(__FILE__);

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND
	strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified) {
  header($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified',TRUE,304);
  header('Pragma: public');
  header('X-Macrojs: yes');
  header('Last-Modified: '.gmdate('D, d M Y H:i:s',$last_modified).' GMT');
  header('Cache-Control: max-age='.TIME_BROWSER_CACHE.', must-revalidate,public');
  header('Expires: '.gmdate('D, d M Y H:i:s',time() + TIME_BROWSER_CACHE).'GMT');
  // That's all, now close the connection:
  header('Connection: close');
  die();
}





if(!ob_start("ob_gzhandler")) ob_start();

header("Content-Type: text/javascript");

function agnadirFichero($fichero){
    global $output;

    
    if(file_exists($fichero)) {
    
        $sText = file_get_contents($fichero);
    
        if (strlen($sText)>3){                                
            $tiempoUltimo = filemtime($fichero);           

            if ( $tiempoUltimo > $output["ultimotiempo"]){
                $output["ultimotiempo"] = $tiempoUltimo;
            }

            echo "/* " . $fichero . " */\n";
            $output["js"] = $output["js"] . "\n" .$sText;  
        }
    } else {

       echo "/* no encuentra:" . $fichero . " */\n";
    }

}

echo "/* mtime:". $last_modified . " */\n";


$output = array("js"=>"","ultimotiempo"=>0);

$modulo = isset($_REQUEST["modo"])?$_REQUEST["modo"] . ".js":false;
$extra = isset($_REQUEST["extra"])?$_REQUEST["extra"]:"";

//agnadirFichero("jquery.min.1.6.4.js");
agnadirFichero("js/jquery-1.6.2.min.js");
//agnadirFichero("jquery-1.6.2.js");
agnadirFichero("js/underscore-min.js");

if($desactivarFirebug)
    agnadirFichero("js/firebug.js");

if($modulo and $modulo!=".js"){
    agnadirFichero("js/pages/" . $modulo);
}

agnadirFichero("js/tienepunto.js");

if($extra)
switch($extra){

    case "login":
        agnadirFichero("js/jquery.corners.js");
        agnadirFichero("js/jquery.cookie.js");
        break;
    case "modvisorreporting":
        agnadirFichero("js/ui/jquery-ui-1.8.16.custom.js");
        agnadirFichero("js/jquery.debounce.js");
        agnadirFichero("js/jquery.cookie.js");
        break;

    case "modreporting":
        agnadirFichero("js/ui/jquery-ui-1.8.16.custom.js");
        agnadirFichero("js/jquery.debounce.js");
        agnadirFichero("js/jquery.cookie.js");
        break;            
    case "panel":
        agnadirFichero("js/storage.js");
        agnadirFichero("js/basica.js");
        agnadirFichero("css/greybox/greybox.js");
        agnadirFichero("js/GB.js");
        agnadirFichero("js/cluetip/jquery.cluetip.js");
        {
             //agnadirFichero("menu/jquery.menu.min.js");
             agnadirFichero("js/menu/jquery.menu.js");
            /*
            agnadirFichero("jquery.hoverintent.js");
            agnadirFichero("superfish/js/superfish.js");
             *
             */

            }
        break;

    case "mapa":
        agnadirFichero("js/ui/jquery-ui-1.8.16.custom.js");
        agnadirFichero("js/jquery.debounce.js");
        agnadirFichero("js/jquery.cookie.js");
        agnadirFichero("js/jquery.gamequery-0.4.0.js");//se usa?
       // agnadirFichero("jquery.corners.js");
        agnadirFichero("js/menu/jquery.menu.min.js");
        break;
    case "basica":        
        agnadirFichero("js/storage.js");
        agnadirFichero("js/basica.js");      
        {
             agnadirFichero("js/menu/jquery.menu.min.js");
            /*
            agnadirFichero("jquery.hoverintent.js");
            agnadirFichero("superfish/js/superfish.js");
             *
             */

            }
        agnadirFichero("js/jquery.cookie.js");
        break;
    case "central":
        agnadirFichero("js/storage.js");
        agnadirFichero("js/basica.js");
        agnadirFichero("css/greybox/greybox.js");
        agnadirFichero("js/GB.js");
        agnadirFichero("js/cluetip/jquery.cluetip.js");      
        {
             agnadirFichero("js/menu/jquery.menu.min.js");
            /*
            agnadirFichero("jquery.hoverintent.js");
            agnadirFichero("superfish/js/superfish.js");
             *
             */

            }

        break;
}




/*
    Termina y sale
                    */

$output_js = $output["js"];

//TODO: minimiza?

//header("Last-Modified: ".date("D, d M Y H:i:s T", $output["ultimotiempo"] ));
header("Last-Modified: ".date("D, d M Y H:i:s T", $last_modified ));
header("ETag: '".md5($output_js)."'");

echo $output_js;

ob_flush();

