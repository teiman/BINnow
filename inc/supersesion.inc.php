<?php

/**
 * Utilitarios trabajar con sesiones de forma indirecta 
 *
 * @package binow
 */



/*
 * Carga datos de sesion de forma indirecta
 *
 * @param $nombre nombre de la variable de sesion que se desea recuperar
 */
function getSesionDato($nombre){
    global $debug_mode;

    switch($nombre){
        case "statuspendiente":
            if (isset($_SESSION[$nombre]))
                return $_SESSION[$nombre];
                
            $dato = getParametro("binow.statuspendiente");
            
            $_SESSION[$nombre] = $dato;
            return $dato;


        case "PerfilActivo":
                //Esta mal pero funciona (?) y si lo arreglas deja de funcionar (?!)
                if (is_array($_SESSION[$nombre]))
                        $_SESSION[$nombre]=array();

                return unserialize($_SESSION[$nombre]);

        case "Parametros":
                if (isset($_SESSION[$nombre])){
                        return $_SESSION[$nombre];
                }

                $row = queryrow("SELECT * FROM ges_parametros","Cargando parametros");
                $_SESSION[$nombre] = $row;

                return $row;

        case "IdLenguajeDefecto": //Idioma para productos en altas, bajas, etc...

                if (isset($_SESSION[$nombre])){
                        return $_SESSION[$nombre];
                }
                global $lang;

                $_SESSION[$nombre] = $lang;
                return $lang;

        case "IdLenguajeInterface": //Idioma del usuario
                //TODO:
                // leer del usuario

                return getSesionDato("IdLenguajeDefecto");

        case "id_user": //Idioma para productos en altas, bajas, etc...

                if (isset($_SESSION[$nombre])){
                        return $_SESSION[$nombre];
                }

                return 0;
        default:
                return $_SESSION[$nombre];
    }

}

function invalidarSesion($clase) {	
	$_SESSION[$clase] =false;	
}


function limpiarSesion(){

	foreach( $_SESSION as $key=>$value ){
		unset($_SESSION[$key]);
	}
}



function setSesionDato($dato,$valor) {	
	global $_SESSION;
	
	if (is_object($valor)){
	 	$_SESSION[$dato] = serialize($valor);
	 	return;		
	}
	
	
	$_SESSION[$dato] = $valor;
}
