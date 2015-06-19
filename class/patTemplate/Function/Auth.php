<?PHP
/**
 * patTemplate function that calculates the current time
 * or any other time and returns it in the specified format.
 *
 * $Id: Time.php 454 2007-05-30 15:34:37Z gerd $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate function that calculates the current time
 * or any other time and returns it in the specified format.
 *
 * $Id: Time.php 454 2007-05-30 15:34:37Z gerd $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */




function puedeHacerPerfil( $id_profile,$command,$defecto="") {

    $name = "puedeVerCanalPerfil_" . $id_profile . "_" . $command;

    if (isset($_SESSION[$name])) {
        return $_SESSION[$name];
    }

    $sql = "SELECT way FROM allowdisallows WHERE id_profile=%d AND path LIKE '%s' AND deleted=0  LIMIT 1 ";

    $sql = sprintf($sql,$id_profile,$command);

    $row = queryrow($sql);

    //Permisos especificos de este usuario (seran excepciones respecto al grupo)
    if ($row["way"]=='d' || $row["way"]=='a') {
        $_SESSION[$name] = $row["way"];
        return $row["way"];
    }

    $_SESSION[$name] = $defecto;
    return $defecto;
}






function puedeHacer($command,$defecto=true) {
    $id_profile = getSesionDato("id_profile_active");
    $name = "puedeHacer_" . $command . "_" . $id_profile;

    if (isset($_SESSION[$name]) ) {
        return $_SESSION[$name];
    }

    $way = puedeHacerPerfil($id_profile,$command);

    //permisos especificos de usuario
    if ($way=="d") {
        $_SESSION[$name] = false;
        return false;
    }
    if ($way=="a") {
        $_SESSION[$name] = true;
        return true;
    }

    //TODO: hay un bug somewhere

    $grupos = getSesionDato("user_groups");

    $way_final = "";

    if(is_array($grupos))
        foreach($grupos as $key=>$id_grupo) {

            $id_profile = getIdProfileFromGroup($id_grupo);

            $way = puedeHacerPerfil($id_profile,$command);

            if ($way=="d") {
                $way_final = "d"; // Si por un grupo estamos prohibidos, estamos definitivamente p
            } else 		if ($way=="a") {
                if ($way_final== "")
                    $way_final = "a"; // Si por algun grupo estamos permitidos, entonces estamos permitidos
            } else {

            }

        }

    //permisos especificos de usuario
    if ($way_final=="d") {
        $_SESSION[$name] = false;
        return false;// al menos prohibido en un grupo
    }
    if ($way_final=="a") {
        $_SESSION[$name] = true;
        return true;
    }

    $_SESSION[$name] = $defecto;
    //Si no hay nada especifico, no hay restricciones
    return $defecto;
}



class patTemplate_Function_Auth extends patTemplate_Function {
    /**
     * name of the function
     * @access	private
     * @var		string
     */
    var $_name	=	'Auth';

    /**
     * Overridden because the time should not be calculated at compile time but at runtime
     *
     * @var integer
     */
    var $_type  =   PATTEMPLATE_FUNCTION_RUNTIME;

    /**
     * call the function
     *
     * @access	public
     * @param	array	parameters of the function (= attributes of the tag)
     * @return	string	content to insert into the template
     */
    function call( $params, $content ) {

        $html = "";

        $command = $params["command"];

        if (!$command) return ""; //Si no hay comando, seguro que esta permitido.

        $defecto = true;
        $contrario = false;
        $saleContent = true;

        $saleNada = "";



        if(isset($params["defecto"])) {
            $defecto = ($params["defecto"]=="true")?true:false;
        }
        if(isset($params["contrario"])) {
            $contrario = ($params["contrario"]=="true")?true:false;
        }

        if(0)  $saleNada = "[$command:desactivado(c:$contrario)]";


        if (puedeHacer($command,$defecto)) {
            $html = $saleNada;
            $saleContent = true;
        } else {
            $html = "oculto";
            $saleContent = false;
        }

        if(!strlen($content)) {
            $content = $html;
        }

        if($contrario)
            $saleContent = !$saleContent;

        if ($saleContent) {
            return $content;
        } else {
            return $saleNada;
        }
    }
}

