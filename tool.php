<?php

/**
 * tool.php
 *
 * Cargador de librerias esenciales y configuracion
 * @package binow
 */


if (!defined("NO_SESSION")) {
	if (session_id() == "") {
		session_start();
	}
}

$modo = (isset($_REQUEST["modo"])?$_REQUEST["modo"]:false);

define('__ROOT__', (dirname(__FILE__)));

/* Funciones de compatibilidad con PHP mas antiguo */

if(function_exists("get_magic_quotes_gpc")){
	if (get_magic_quotes_gpc()) {
		function stripslashes_profundo($valor)    {
			$valor = is_array($valor) ?
			array_map('stripslashes_profundo', $valor) :
			stripslashes($valor);
			return $valor;
		}

		$_POST = array_map('stripslashes_profundo', $_POST);
		$_GET = array_map('stripslashes_profundo', $_GET);
		$_COOKIE = array_map('stripslashes_profundo', $_COOKIE);
		$_REQUEST = array_map('stripslashes_profundo', $_REQUEST);
	}
}


if(!function_exists("_")){
	function _($text){
		return $text;
	}
}

if(!function_exists("_split")){
	if(function_exists("mb_split")){
		function _split($a,$b){
			return mb_split($a,$b);
		}
	} else {
		function _split($a,$b){
			return split($a,$b);
		}
	}
}

function valido8($data){
	$valid_utf8 = (@iconv('UTF-8','UTF-8',$data) === $data);

	if(!$valid_utf8)
	$data = utf8_encode($data);

	return $data;
}


include_once("config/config.php");
include_once("inc/clean.inc.php");
include_once("inc/db.inc.php");

function mysqlescape($str){
	forceconnect();
	return mysql_real_escape_string($str);
}

include_once("inc/html.inc.php");
include_once("inc/supersesion.inc.php");
include_once("inc/combos.inc.php");
include_once("inc/auth.inc.php");

include_once("inc/plugandplaybility.inc.php");
include_once("class/json.class.php");//comunicacion

include_once("class/cursor.class.php");
include_once("class/config.class.php");

include_once("class/pagina.class.php");


$script = basename($_SERVER['SCRIPT_NAME']);
$script = substr($script, 0, -4);


$template = array();
$template["modname"] = $script;

$_SESSION["base_estaticos"] = $base_estaticos;

if(!function_exists("invocarHook")){
    function invocarHook(){
        //nada
    }
}

error_reporting(E_ERROR);