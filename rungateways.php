<?php

/**
 * Gestion de pasarelas
 *
 * Alta/Baja/modificación de pasarelas
 * @package binow
 */

header("Content-type: text/plain",true);

define( "GATEWAY_WRAPPER", true );
define( "NO_SESSION",1 );
define( "NOPAGINA",1 );
define ( "CR", "<br>\n" );

include_once("tool.php");

include_once("inc/pasarelas.inc.php");
include_once("class/config.class.php");//no deberia hacer falta


if (isset($_REQUEST["forzarmodulo"])) {
    $moduloforzado = $_REQUEST["modulo"];
}


$corriendoGateway = "rungateway-". intval(rand()*9000);
$corriendoGeneral = "rungateway-general";



marcarProcesoCorriendo($corriendoGateway);

header("Content-type: text/plain");
//header("Content-type: text/html");

$cr = "<br>\n";


if (estaCorriendoProceso($corriendoGeneral)) {
    $f = "procesos/" .  $corriendoGeneral . ".pid";

    $desde = date ("F d Y H:i:s.", filemtime($f));

    echo unix()."*** Ya esta corriendo, $f, desde $desde *** $cr";
    //abortarRunGateway();
    //if(1)desmarcarCorriendoModulo($corriendoGeneral); //debug
    //exit();
}

marcarProcesoCorriendo($corriendoGeneral);




function myErrorHandler($errno, $errstr, $errfile, $errline) {
    global $corriendoGeneral;

    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
        case E_USER_ERROR:
            echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
            echo "  Fatal error on line $errline in file $errfile";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...(limpiando procs)<br />\n";
            desmarcarCorriendoModulo($corriendoGeneral);
            abortarRunGateway();

            exit(1);
            break;

        case E_USER_WARNING:
            echo "<b>WARNING</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            echo "<b>NOTICE</b> [$errno] $errstr<br />\n";
            break;

        default:
            echo "Unknown error type: [$errno] $errstr<br />\n";
            break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

$selected_module = "";

// set to the user defined error handler
$old_error_handler = set_error_handler("myErrorHandler");

function handleShutdown() {
    global $corriendoGeneral,$selected_module;
    $error = error_get_last();
    if($error !== NULL) {
        $info = "[SHUTDOWN] file:".$error['file']." | ln:".$error['line']." | msg:".$error['message'];
        echo $info . "<br>\n";
        echo "Aborting...(limpiando procs)<br />\n";
        desmarcarCorriendoModulo($selected_module);
        desmarcarCorriendoModulo($corriendoGeneral);
        abortarRunGateway();
    }
}

register_shutdown_function('handleShutdown');


$sql = "SELECT * FROM gateway WHERE lastrun=1 LIMIT 1";
$row = queryrow($sql);

$viejo = $row["module"];

$selected = false;
$cogeSiguiente = false;

$modulos = genListaModulosPasarelas();


$necesitamosInfofax = false;

echo var_export($modulos,true);

echo unix() . " Modulo corrido en la fase anterior: '$viejo' $cr";

foreach($modulos as $modulo) {

    if  ( !isAuthorizedModule( $modulo ) ) {
        echo unix()."*** saltando modulo no autorizado '".$modulo."' ***$cr";
        continue;
    }

    if ( estaCorriendoProceso($modulo)  ) {
        echo unix()."*** saltando modulo que esta en funcionamiento '".$modulo."' ***$cr";
        continue;
    }


    if($modulo=="infofax.gw.php"){
        $necesitamosInfofax = true;
    }


    //Si este modulo fue el ultimo en usarse, se salta
    if ($viejo == $modulo) {

        //si es el primero en la lista, se coge para correr por defecto.
        if (!$selected) {
            if(0)echo unix() . " Preseleccionado '$modulo' $cr";
            $selected = $modulo;
        }

        //el proximo que veamos, sera el escogido
        $cogeSiguiente = true;
        if(0)echo unix() . " El proximo que veamos sera el escogido $cr";
        continue;
    }

    if(0)echo unix() . " posible '$modulo' $cr";

    //al menos cogeremos un valido
    if (!$selected)
        $selected = $modulo;

    //AJA!.. este es el siguiente al ultimo en correr, asi que correremos este, y saldremos para que realmente sea el que se usara
    if($cogeSiguiente) {
        if(0)echo unix() . " cogeSiguiente: '$modulo' $cr";
        $selected = $modulo;
        break;
    }

}


if (!$selected) {
    abortarRunGateway();
}


$selected_s = sql($selected);

query("UPDATE gateway SET lastrun='' ");
query("UPDATE gateway SET lastrun=1 WHERE module='$selected_s' ");
//NOTA: se marca antes de correr, porque una pasarela puede tener un error, y fallar, de modo que no hacemos
// nada importante despues.



//Aun con todo, algo puede salir mal, de modo que solo tratamos de correr una que exista.
if ($selected) {
    $selected_module = $selected;

    marcarProcesoCorriendo($selected);

    $carpetaModulo = genCarpetaPasarela($selected);

    echo unix()."*** invocando pasarela (". "gateway/" . $carpetaModulo . "/" . $selected . ") ***$cr";
    try {
        include( "gateway/" . $carpetaModulo . "/" . $selected );
    }catch(Exception $e) {

        echo unix()."*** ERROR pasarela: ". $selected . "  ***$cr";
        echo var_export($e,true) . $cr;
    }
    echo unix()."*** pasarela ". $selected . " se completo ***$cr";

    desmarcarCorriendoModulo($selected);

    if($selected == "mail.gw.php" and $necesitamosInfofax){
        $selected = "infofax.gw.php";
        marcarProcesoCorriendo($selected);

        $carpetaModulo = genCarpetaPasarela($selected);

        echo unix()."*** invocando pasarela (". "gateway/" . $carpetaModulo . "/" . $selected . ") ***$cr";
        try {
            include( "gateway/" . $carpetaModulo . "/" . $selected );
        }catch(Exception $e) {

            echo unix()."*** ERROR pasarela: ". $selected . "  ***$cr";
            echo var_export($e,true) . $cr;
        }
        echo unix()."*** pasarela ". $selected . " se completo ***$cr";

        desmarcarCorriendoModulo($selected);
        $necesitamosInfofax = false;
    }

} else {
    echo unix()."ERROR: ninguna pasarela fue encontrada cualificada para correr$cr";
}




desmarcarCorriendoModulo($corriendoGeneral);

abortarRunGateway();

if (0) {
    echo "<script> setTimeout('document.location.reload()',2000);</script>";
}
