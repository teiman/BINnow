<?php

/**
 * Pagina de entrada a la aplicación
 *
 *
 * @package binow
 */

include("tool.php");
include( __ROOT__ ."/inc/paginacompleta.php");

switch($modo) {
    case "login":
        $login = trim($_REQUEST["login"]);
        $pass = trim($_REQUEST["pass"]);

        $login_s = sql($login);
        $pass_s = sql($pass);


        $sql = "SELECT * FROM users WHERE (user_login='$login_s') AND (pass_login='$pass_s') AND deleted='0' ";
        $row = queryrow($sql);

        $esLogueado = $row["id_user"];

        if (!$esLogueado) {
            $page->addVar('page', 'esErrorLogin', 'error');
            break;
        }

        $page->addVar('page', 'esErrorLogin', 'false');

        limpiarSesion();

        setSesionDato("id_user",$row["id_user"]);
        setSesionDato("id_usuario_logueado",$row["id_user"]);
        setSesionDato("id_profile_active",$row["id_profile"]);
        setSesionDato("user_groups",genGroupUser($row["id_user"]) );
        setSesionDato("user_perfiles",genPerfilesUser($row["id_user"]) );
        setSesionDato("user_nombreapellido",$row["s_name1"] . " " . $row["s_name2"]  );
        setSesionDato("user_data",$row );
        setSesionDato("id_nodo_permisos_user", $row["id_nodo"]);


        session_write_close();


        header("Location: modreporting.php?modo=init&especial=rompeframes");
        exit();
        break;
    default:
        break;
}



$page->readTemplatesFromInput('login.htm');


$page->addVar('headers', 'titulopagina', 'Entrar' );


$page->addVar("cabeza","nologin","<!--");
$page->addVar("cabeza","nologin2","-->");


$page->Volcar();

