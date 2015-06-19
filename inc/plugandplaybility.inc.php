<?php



function NormalizarPath($path){
	// Convierte cadenas del tipo c:/files// o c:/files  en c:/files/
	$path = $path . DIRECTORY_SEPARATOR;

	$path = str_replace("//","/",$path);
	$path = str_replace("//","/",$path);
	$path = str_replace("//","/",$path);
	$path = str_replace("\\\\","\\",$path);

	return $path;
}


function getPathBaseModule($module){

	$module = str_replace(".php","",$module);
	//$module = str_replace(".","",$module);

	$dir = "gateway/". $module . "/";

	return $dir;
}


function getValidModule($moddir,$mod=""){
	$elemento = NormalizarPath( $moddir ). $mod;

	if ( file_exists($elemento))
		return $elemento;

        error_log("Intentando cargar modulo moddir:$moddir,mod:$mod, FALLA");
	return false;
}

/**
 * comprueba si modulo esta autorizado para correr
 * @param string nombre del modulo
 * @return boolean
 */
function isAuthorizedModule($module){
	$module_s = sql($module);

	$sql = "SELECT enabled FROM gateway WHERE module='$module_s'  ";
	$row = queryrow($sql);

	return $row["enabled"];
}



/**
 * lista los modulos instalados escaneando el directorio
 * @return array modulos
 */
function genListaModulosPasarelas(){
	$modulos = array();

	$dir = "./gateway/";

	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if ( $file =="." or $file =="..") continue;

				if (!strstr($file, '.gw')) continue;

				$modulos[] = $file .".php";
			}
			closedir($dh);
		}
	}

	return $modulos;
}


function unix_raw(){
    return date("U")." ";
}



function marcarProcesoCorriendo($proceso){
	//Linux
	system("touch procesos/". $proceso . ".pid");
}

$global_cwd =  getcwd();

function desmarcarCorriendoModulo($proceso){
        global $global_cwd;


        if(!$proceso)return;

	unlink( $global_cwd ."/procesos/" . $proceso. ".pid");

        //echo unix_raw()."cwd:" . getcwd(). CR;
        
	echo unix_raw()."*** Cerrando $proceso ***". CR;
        
}


function estaCorriendoProceso($proceso){

    $path = "procesos/" .  $proceso . ".pid";


    $bloqueado = 60*30;//30 minutos

	$is = file_exists($path);

    if($is){
        $modified = filemtime($path);
        $actual = date("U");

        $tiempo = $actual - $modified;

        if($tiempo>$bloqueado){
            echo unix_raw()."*** FORZANDO cerrar $proceso que corrio mas de $bloqueado segundos ***". CR;
            error_log("FATAL: se forzo abrir $proceso que estaba bloqueado $tiempo segundos");
            desmarcarCorriendoModulo($proceso);
            return true;
        }
    }
//	echo "*** Corriendo $proceso? ($is) ***". CR;

	return $is;
}


function abortarRunGateway(){
	global $corriendoGateway;
	echo unix_raw()."*** Completo: proceso general y saliendo  ***". CR;
	desmarcarCorriendoModulo($corriendoGateway);
}


function adquirirLlave($proceso){

    //error_log("Se invoco codigo en un momento erroneo");
    //die("proceso:$proceso");

  // Open PID file
  $tmpfilename = "procesos/$proceso.pid";
  if (!($handle_lockfile = @fopen($tmpfilename,"a+")))
  {
    // Script already running - abort
    return false;
  }

  // Obtain an exclusive lock on file
  // (If script is running this will fail)
  if (!@flock( $handle_lockfile, LOCK_EX | LOCK_NB, $wouldblock) || $wouldblock) {
    // Script already running - abort
    @fclose($handle_lockfile);
    return false;
  }

  // Write our PID
  @ftruncate($handle_lockfile,0);
  @fseek($handle_lockfile, 0, 0);
  @fwrite($handle_lockfile, getmypid());
  @fflush($handle_lockfile);
  return true;
}

