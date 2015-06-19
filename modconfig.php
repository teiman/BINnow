<?php

/**
 * Gestion de parametros de configuracion
 *
 * modificación de parametros
 * @package binow
 */


include("tool.php");

include("inc/paginabasica.inc.php");

$auth = canRegisteredUserAccess("modconfig",false);
if ( !$auth["ok"] ){	include("moddisable.php");	 }


$mostrarListado = false;
$mostrarEdicion = false;


$page->addVar('headers', 'titulopagina', _('Gestion de parametros')  );
$page->addVar('page', 'labelalta',  _("Alta de parametro") );
$page->addVar('page', 'labellistar',  _("Listar") );

if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 100;

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo){

	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;

		break;

	case "filtrar-elemento":

		$filtranombre_s = sql(trim($_REQUEST["filtrar-elemento"]));

		$extracondicion  = " AND system_param_title LIKE '%$filtranombre_s%' ";

		/*<input type="hidden" name="modo" value="filtrar-elemento" />
	<input type="hidden" name="filtrar-elemento" value="" id="filtra-list-value" />*/
		$mostrarListado = true;
		break;

	case "guardarcambios":
	
		$config->set(trim($_POST["system_param_title"]),$_POST["system_param_value"] );
		
		$mostrarListado = true;
		break;

	case "guardaralta":
		$config->altaclave(trim($_POST["system_param_title"]),$_POST["system_param_value"] );

		$mostrarListado = true;
		break;

	case "modificar":
		$mostrarEdicion = true;


		$id_s = sql($_REQUEST["id"]);
		$row = queryrow( "SELECT * FROM system_param WHERE id_system_param='$id_s'  ");

		$metodo = "Modificar";
		$newmodo = "guardarcambios";
		break;

	case "alta":
		$mostrarEdicion = true;

		$metodo = "Alta";
		$newmodo = "guardaralta";
		break;
    case "eliminar":

        $id =  CleanID($_REQUEST["id"]);

        $sql = "DELETE FROM system_param WHERE id_system_param='$id'";
        query($sql);


		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}


if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_parametro.htm' );

	$page->addVar( 'edicion', 'modname',		$template["modname"] );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );


	$page->addVar( 'edicion', 'id_system_param',	$row["id_system_param"] );
	$page->addVar( 'edicion', 'system_param_title',	$row["system_param_title"] );
	$page->addVar( 'edicion', 'system_param_value',	$row["system_param_value"] );

	//$page->addVar( 'edicion', 'activahtml',	$config->get("enabled")?"checked='checked'":"");

	//$page->addArrayFromCursor( 'edicion',$config, array("id_system_param","system_param_title",'system_param_value')  );

}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_parametros.htm' );

	$maxfilas = $_SESSION[ $template["modname"] . "_list_size"];
	$min = intval($_REQUEST["min"]);

	//die("n:".$maxfilas);
	
	$list = array();

	$sql = "SELECT * FROM system_param WHERE id_system_param > 0 $extracondicion ORDER BY `system_param_title` ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_system_param"]
				,"name"=>$row["system_param_title"]
				,"value"=>$row["system_param_value"]

		);

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );
	$page->configNavegador( $min, $maxfilas,$numFilas);
}

$page->Volcar();
