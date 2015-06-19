<?php

/**
 *
 * @package binow
 */



/**
 * Gestion de contactos
 *
 * Alta/Baja/modificación de contactos
 * @package binow
 */



include("tool.php");
include("class/contacts.class.php");


include("inc/paginabasica.inc.php");

$auth = canRegisteredUserAccess("modcontacts",false);
if ( !$auth["ok"] ){	include("moddisable.php");	 }



$maxfilas = 100;

$page->addVar('headers', 'titulopagina', _('Gestion contactos'));
$page->addVar('page', 'labelalta', _("Alta contacto") );
$page->addVar('page', 'labellistar', _("Listar") );

if (!$_SESSION[ $template["modname"] . "_list_size"])
	$_SESSION[ $template["modname"] . "_list_size"] = 100;

$mostrarListado = false;
$mostrarEdicion = false;


$contact = new Contacto();

$modo = (isset($_REQUEST["modo"]))?$_REQUEST["modo"]:false;

$nombreUsuarioMostrar = "";

$min = 0;

switch($modo){
	case "filtrar-elemento":

		$filtranombre_s = sql($_REQUEST["filtrar-elemento"]);
		$extracondicion  = " AND (contact_name  LIKE '%$filtranombre_s%' or  contact_code LIKE '$filtranombre_s'  ) ";

        //COLLATE utf8_general_ci

        //`name` COLLATE utf8_general_ci LIKE '%renee%';
		$mostrarListado = true;

                $_SESSION["offset_contacts"] = 0;

		break;
	case "change-list-size":
		$listsize = $_REQUEST["list-size"];

		if ($listsize)
			$_SESSION[ $template["modname"] . "_list_size"] = $listsize;

		$mostrarListado = true;
		break;
	case "navto":
		$min = intval($_REQUEST["offset"]);
		$_SESSION["offset_contacts"] = $min;
		$mostrarListado = true;
		break;

	case "navlast":

		$sql = "SELECT count(id_contact) as max FROM contacts ";
		$row = queryrow($sql);

		$max = $row["max"];

		$_SESSION["offset_contacts"] = $max -$maxfilas ;
		$mostrarListado = true;
		break;
	case "guardarcambios":
		$id =  CleanID($_POST["id_contact"]);

		if ( $contact->Load($id) ){
			$contact->set("contact_name",$_POST["contact_name"]);
			$contact->set("contact_code",$_POST["contact_code"]);
			$contact->set("priority",$_POST["priority"] );
			$contact->Modificacion();
		}

		$mostrarListado = true;
		break;

	case "guardaralta":

		$contact->set("contact_name",$_POST["contact_name"]);

		$contact->Alta();
		$mostrarListado = true;

		break;

	case "modificar":
		$mostrarEdicion = true;

		$id = CleanID($_POST["id"]);
		$contact->Load($id);


		$nombreUsuarioMostrar = html($contact->getNombre());
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

		$sql = "UPDATE contacts SET eliminado=1 WHERE id_contact='$id'";
		query($sql);

		$mostrarListado = true;
        break;
	default:
		$mostrarListado = true;
		break;

}



if ($mostrarEdicion){
	$page->configMenu($newmodo);

	$page->setAttribute( 'edicion', 'src', 'edicion_contacto.htm' );
	$page->addVar( 'edicion', 'modoediciontxt',	$metodo );
	$page->addVar( 'edicion', 'modoedicion',	$newmodo );

	$page->addVar( 'edicion', 'modname', $template["modname"] );
	$page->addVar( 'edicion', 'contact_name', $contact->get("contact_name")  );
	$page->addVar( 'edicion', 'id', $contact->get("id_contact") );
	$page->addVar( 'edicion', 'priority', $contact->get("priority") );
	$page->addVar( 'edicion', 'contact_code', $contact->get("contact_code") );
}

if ($mostrarListado){
	$page->configMenu("listar");

	$page->setAttribute( 'listado', 'src', 'listado_contactos.htm' );
	$page->addVar( 'listado', 'modoediciontxt',	$metodo );
	$page->addVar( 'listado', 'modoedicion',	$newmodo );


	$min = intval($_SESSION["offset_contacts"]);

	$list = array();

	$sql = "SELECT * FROM contacts
		WHERE eliminado=0 and id_contact>0 $extracondicion
		ORDER BY contact_code ASC, contact_name ASC LIMIT $min,$maxfilas";
	$res = query($sql);

	$numFilas =0;
	while($row = Row($res) ){
		$estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
		$numFilas++;

		$fila = array("modname"=>$template["modname"], "id"=>$row["id_contact"],
			"contact_name"=>$row["contact_name"],"contact_code"=>$row["contact_code"]
			);

		$list[] = $fila;
	}

	$page->addRows('list_entry', $list );

	$page->configNavegador( $min, $maxfilas,$numFilas);
}

$page->Volcar();
