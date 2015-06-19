<?php

/**
 * Gestion de grupos
 * @package binow
 */



include("tool.php");
include("class/group.class.php");
include("inc/paginabasica.inc.php");


$auth = canRegisteredUserAccess("modgrupos", false);
if (!$auth["ok"]) {
    include("moddisable.php");
}



$mostrarListado = false;
$mostrarEdicion = false;

$page->addVar('headers', 'titulopagina', 'Gestion de grupos');
$page->addVar('page', 'labelalta', "Alta de grupos");
$page->addVar('page', 'labellistar', "Listar");


if (!$_SESSION[$template["modname"] . "_list_size"])
    $_SESSION[$template["modname"] . "_list_size"] = 100;


$group = new Grupo();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch ($modo) {
    case "filtrar-elemento":

        $filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

        $extracondicion = " AND `group` LIKE '%$filtranombre_s%' ";

        /* <input type="hidden" name="modo" value="filtrar-elemento" />
          <input type="hidden" name="filtrar-elemento" value="" id="filtra-list-value" /> */
        $mostrarListado = true;
        break;
    case "change-list-size":
        $listsize = $_REQUEST["list-size"];

        if ($listsize)
            $_SESSION[$template["modname"] . "_list_size"] = $listsize;

        $mostrarListado = true;

        break;
    case "guardarcambios":
        $id = CleanID($_POST["id_group"]);

        if ($group->Load($id)) {
            $group->set("group", $_POST["group"], FORCE);
            $group->set("id_profile", $_POST["id_profile"], FORCE);
            $group->Modificacion();
        }

        $mostrarListado = true;
        break;

    case "guardaralta":
        $group->set("group", $_POST["group"], FORCE);
        $group->set("id_profile", $_POST["id_profile"], FORCE);

        $group->Alta();
        $mostrarListado = true;
        break;

    case "modificar":
        $mostrarEdicion = true;

        $id = CleanID($_POST["id"]);
        $group->Load($id);

        $nombreUsuarioMostrar = html($group->getNombre());
        $metodo = "Modificar";
        $newmodo = "guardarcambios";
        break;

    case "alta":
        $mostrarEdicion = true;

        $metodo = "Alta";
        $newmodo = "guardaralta";
        break;
    case "eliminar":
        $id = CleanID($_REQUEST["id"]);

        $sql = "DELETE FROM groups WHERE id_group='$id'";
        query($sql);
        $mostrarListado = true;
        break;
    default:
        $mostrarListado = true;
        break;
}



if ($mostrarEdicion) {
    $page->configMenu($newmodo);

    $page->setAttribute('edicion', 'src', 'edicion_grupos.htm');

    $page->addVar('edicion', 'modname', $template["modname"]);
    $page->addVar('edicion', 'modoediciontxt', $metodo);
    $page->addVar('edicion', 'modoedicion', $newmodo);
    $page->addVar('edicion', 'optionsselectprofile', genComboProfiles($group->get("id_profile")));

    $page->addArrayFromCursor('edicion', $group, array("id_group", "group"));
}

if ($mostrarListado) {
    $page->configMenu("listar");

    $page->setAttribute('listado', 'src', 'listado_grupos.htm');

    $maxfilas = 100;
    $min = intval($_REQUEST["min"]);


    $list = array();

    $sql = "SELECT * FROM groups WHERE id_group>0 $extracondicion  ORDER BY `group` ASC LIMIT $min,$maxfilas";
    $res = query($sql);

    $numFilas = 0;
    while ($row = Row($res)) {
        $estiloApropiado = ($numFilas % 2) ? "filaImpar" : "filaPar";
        $numFilas++;

        $fila = array("modname" => $template["modname"], "id" => $row["id_group"], "group" => $row["group"]);

        $list[] = $fila;
    }

    $page->addRows('list_entry', $list);

    $page->configNavegador($min, $maxfilas, $numFilas);
}


$page->Volcar();

