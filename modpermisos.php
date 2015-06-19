<?php

/**
 * Gestion de permisos
 *
 * Alta/Baja/modificación de permisos
 * @package binow
 */
include("tool.php");


include("inc/paginabasica.inc.php");
include_once(__ROOT__ . "/class/users.class.php");
include_once(__ROOT__ . "/class/permisos.class.php");

$auth = canRegisteredUserAccess("modusuarios", false);
if (!$auth["ok"]) {
    include("moddisable.php");
}

$mostrarListado = false;
$mostrarEdicion = false;

switch ($modo) {

    case "creararbol":
        $id_user = CleanID($_POST["id_user"]);

        $id_nodo_user = nodo::crearSub(0, "and");

        //$sql = "UPDATE users SET id_nodo='$id_nodo_user' WHERE id_user='$id_user' ";
        $sql = "UPDATE users SET id_nodo='$id_nodo_user' WHERE id_user='$id_user' ";
        query($sql);


        $_SESSION["id_nodo_editar"] = $id_nodo_user;
        $_SESSION["id_user_editar"] = $id_user;
        break;
    case "carga":

        $id_nodo_user = $_POST["id_rama"];
        $id_user = CleanID($_POST["id_user"]);


        $_SESSION["id_nodo_editar"] = $id_nodo_user;
        $_SESSION["id_user_editar"] = $id_user;
        break;
    default:
        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;
}



switch ($modo) {
    case "toggleObliga":
        $id_nodo = $_POST["id_nodo"];

        $datos = nodo::datos($id_nodo);

        $obligatorio = ($datos["obligatorio"] == 1);
        $nuevo_valor = ($obligatorio) ? "0" : "1";

        $sql = "UPDATE arbol_permisos SET obligatorio='$nuevo_valor' WHERE id_nodo='$id_nodo' ";
        query($sql);

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;

    case "actualiza_condiciones":
        $condicion = $_POST["condicion"];
        $comparador = $_POST["comparador"];
        $param1 = $_POST["param1"];
        $param2 = $_POST["param2"];
        $id_nodo = $_POST["id_nodo"];

        nodo::update($condicion, $comparador, $param1, $param2, $id_nodo);

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;

    case "toggleTipoNodo":
        $id_nodo = CleanID($_POST["id_nodo"]);
        $newtipo = $_POST["newtipo"];

        nodo::cambiaTipo($id_nodo, $newtipo);

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;

    case "crearSubNodo":
        $id_padre = CleanID($_POST["id_padre"]);

        $datos = nodo::datos($id_padre);
        $id_and1 = nodo::crearSub($id_padre, "and");
        nodo::crearSub($id_padre, "and");

        nodo::agnade($datos["dato"], $id_and1);

        $sql = "UPDATE arbol_permisos SET tipo='1', dato='and' WHERE id_nodo='$id_padre'  ";
        query($sql);

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;
    case "agnadenodo":
        $id_padre = 0;
        if (isset($_POST["id_padre"]))
            $id_padre = $_POST["id_padre"];

        nodo::agnade($_POST["dato"], $id_padre);

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;
    case "eliminarNodo":

        $id_user = $_SESSION["id_user_editar"];

        /* Si no sabemos que user estamos editando, no es seguro continuar */
        if ($id_user) {
            $id_nodo = $_POST["id_nodo"];
            nodo::eliminar($id_nodo);

            $id_nodo_user = CleanID($_POST["id_rama"]);

            /* Si el borrado era el original, necesitamos un nuevo original */
            if ($id_nodo == $id_nodo_user) {

                $id_nodo_user = nodo::crearSub(0, "and");

                $sql = "UPDATE users SET id_nodo='$id_nodo_user' WHERE id_user='$id_user' ";
                query($sql);


                $_SESSION["id_nodo_editar"] = $id_nodo_user;
            }
        }


        break;

    case "resetearArbol":
        arbol::limpiar();

        $id_nodo_user = CleanID($_POST["id_rama"]);
        break;
}

$page->setTitulo('Gestion de permisos de datos');

$page->addVar('page', 'labelalta', ("Edicion perfil datos"));
$page->addVar('page', 'labellistar', ("Listar"));

$page->configMenu($newmodo);

$page->setAttribute('edicion', 'src', 'edicion_permisos.html');


$page->addVar('edicion', 'id_rama', $id_nodo_user);
$page->addVar('edicion', 'id_user_retorno', $_SESSION["id_user_editar"]);

$html = gui_permisos::get_ui_subarbol($id_nodo_user, $id_nodo_user);
$page->addVar('edicion', 'ui_permisos', $html);


$page->Volcar();
