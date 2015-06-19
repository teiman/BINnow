<?php

/**
 * Ayudas para crear paginas de ejecucion no interrumpible
 *
 * @package binow
 */


/* Serie de ajustes a PHP para que un script no sea interrumpible, y continue hasta que termine su tarea */
/* Sin estos ajustes, el servidor web mataria la tarea de PHP tan pronto detecte que se ha cerrado el navegador */

if(1){
    set_time_limit (0);//run script forever
    ignore_user_abort(TRUE);//run script in background
    ini_set("buffering ","0");
}

/* Funciones amigables con un contexto de ejecucion continua tipo cron */

$esHtml = isset($_REQUEST["modo"])?$_REQUEST["modo"]:false;

$cr = ($esHtml=="html")?"<br>":"\n";

function cron_Cabecera(){ //Cabecera que distingue si estamos haciendo output para html o texto plano
	global $esHtml;
	
	if ($esHtml){
		header("Content-type: text/html; charset=utf-8");
		header("Pragma: no-cache");
		echo "<pre style='font-size: 11px'>";
	} 
}


function cron_final(){
	global $esHtml;
	if ($esHtml){
		echo "</pre>";
	}
}

function cron_flush(){
	@ob_flush();
	@flush();
}



function timestamp(){
	cron_flush();
	return intval(microtime(true)) . ": ";
}



function AddLog($mensaje){

}


function log_evento($evento,$extra = ""){


	$s_evento = sql($evento);
	$s_extra = sql($extra);
	$sql = "INSERT INTO cap_logs (date_cap,event_cap,log_cap) VALUES(NOW(),'$s_evento','$s_extra')";
	query($sql);

/*
cap_logs
* id_cap_log
* log_cap
* date_cap -> fecha de acción del evento de captura de datos
* event_cap -> Evento
* inf_cap
 */

}

$proceso = rand();

function log_start(){
	$proceso = rand();
}

function log_end(){

}



function mkdir_recursive($pathname, $mode){
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
    return is_dir($pathname) || @mkdir($pathname, $mode);
}


function CrearSiNoExiste($path){
	$path = NormalizarPath($path);

	mkdir_recursive($path, 0775);
}


