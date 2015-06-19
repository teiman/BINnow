<?php

/**
 * Gestion de usuarios
 *
 * Alta/Baja/modificación de usuarios
 * @package binow
 */


include("tool.php");

include_once(__ROOT__ . "/class/users.class.php");
include_once(__ROOT__ . "/inc/paginabasica.inc.php");
include_once(__ROOT__ . "/class/permisos.class.php");


$auth = canRegisteredUserAccess("modusuarios",false);
if ( !$auth["ok"] ) {
    include("moddisable.php");
}


$page->addVar('headers', 'titulopagina', _('Gestión de usuarios') );
$page->addVar('page', 'labelalta',  _("Alta de usuario") );
$page->addVar('page', 'labellistar', _("Listar") );


if (!$_SESSION[ $template["modname"] . "_list_size"])
    $_SESSION[ $template["modname"] . "_list_size"] = 100;

$mostrarListado = false;
$mostrarEdicion = false;


$permisos = new sql_permisos();
$usuario = new Usuario();

$modo = $_REQUEST["modo"];

$nombreUsuarioMostrar = "";

switch($modo) {

    case "filtrar-elemento":

        $filtranombre_s = sql($_REQUEST["filtrar-elemento"]);

        $extracondicion  = " AND (name LIKE '%$filtranombre_s%' OR s_name1 LIKE '%$filtranombre_s%' OR s_name2 LIKE  '%$filtranombre_s%' or email LIKE '%$filtranombre_s%' or phone LIKE '%$filtranombre_s%' or user_login ='$filtranombre_s') ";

        $_SESSION["offset_usuarios"] = 0;
        /*<input type="hidden" name="modo" value="filtrar-elemento" />
	<input type="hidden" name="filtrar-elemento" value="" id="filtra-list-value" />*/
        $mostrarListado = true;
        break;

    case "navto":
        $min = intval($_REQUEST["offset"]);
        $_SESSION["offset_usuarios"] = $min;
        $mostrarListado = true;
        break;
    case "navlast":

        $sql = "SELECT count(id_user) as max FROM users ";
        $row = queryrow($sql);

        $max = $row["max"];

        $_SESSION["offset_usuarios"] = $max -$_SESSION[ $template["modname"] . "_list_size"];
        $mostrarListado = true;
        break;

    case "change-list-size":
        $listsize = $_REQUEST["list-size"];

        if ($listsize)
            $_SESSION[ $template["modname"] . "_list_size"] = $listsize;

        $mostrarListado = true;

        break;
    case "guardarcambios":

        $id =  CleanID($_POST["id_user"]);

        if ( $usuario->Load($id) ) {

                $usuario->set("name", trim($_POST["name"]));
                $usuario->set("user_login", trim($_POST["user_login"]));
                $usuario->set("pass_login", trim($_POST["pass_login"]));
                $usuario->set("s_name1", trim($_POST["s_name1"]));
                $usuario->set("s_name2", trim($_POST["s_name2"]));
                $usuario->set("phone", trim($_POST["phone"]));
                $usuario->set("email", trim($_POST["email"]));
            $usuario->set("id_profile", $_POST["id_profile"] );
                //$usuario->set("avatar", trim($_POST["avatar"]));                

            $usuario->set("id_profile", $_POST["id_profile"] );


            $usuario->Modificacion();
        }

        $groups = $_REQUEST["groupsismember"];




        $grupos = explode(",",$groups);

        if (count($grupos)>0) {

            query("BEGIN");
            query("DELETE FROM user_groups WHERE id_user='$id'");

            foreach($grupos as $idgrupo) {

                if ($idgrupo) {
                    $idgrupo_s = sql($idgrupo);


                    $sql = "INSERT user_groups ( id_user, id_group) VALUES ( '$id','$idgrupo_s')";
                    query($sql);
                }
            }
            query("COMMIT");


        }


        $mostrarListado = true;

        break;

    case "guardaralta":


    /*
                 * Detectamos si ya existe
                 * 
    */
        $login_s = sql($_POST["user_login"]);
        $sql = "SELECT user_login FROM users WHERE user_login='$login_s' and deleted='0'";
        $row = queryrow($sql);

        if($row) {
            // La pantalla no se recargara, pero tampoco guardara.
            // TODO: algun tipo de sistema de reportar al usuario sobre este problema
            header("HTTP/1.0 204 No Content");
            exit();
        }

        $usuario->set("name", $_POST["name"] , FORCE);
        $usuario->set("user_login", $_POST["user_login"] , FORCE );
        $usuario->set("pass_login", $_POST["pass_login"]  , FORCE);
        $usuario->set("s_name1", $_POST["s_name1"]  , FORCE);
        $usuario->set("s_name2", $_POST["s_name2"]  , FORCE);
        $usuario->set("phone", $_POST["phone"]  , FORCE);
        $usuario->set("email", $_POST["email"]  , FORCE);
        $usuario->set("id_profile", $_POST["id_profile"]  , FORCE);

        $usuario->Alta();

        $mostrarListado = true;

        $groups = $_REQUEST["groupsismember"];


        $id = $usuario->get("id_user");

        $grupos = explode(",",$groups);

        if (count($grupos)>0) {

            query("DELETE FROM user_groups WHERE id_user='$id'");

            foreach($grupos as $idgrupo) {

                if ($idgrupo) {
                    $idgrupo_s = sql($idgrupo);

                    $sql = "INSERT user_groups ( id_user, id_group) VALUES ( '$id','$idgrupo_s')";
                    query($sql);
                }
            }
        }
        break;

    case "modificar":
        $mostrarEdicion = true;

        $id = CleanID($_POST["id"]);
        $usuario->Load($id);

        $nombreUsuarioMostrar = html($usuario->getNombre());
        $metodo = "Modificar";
        $newmodo = "guardarcambios";

        break;

    case "alta":
        $mostrarEdicion = true;

        $metodo = "Alta";
        $newmodo = "guardaralta";
        query("DELETE FROM user_groups WHERE id_user='0'");
        break;
    case "eliminar":
        $id =  CleanID($_REQUEST["id"]);

        $sql = "UPDATE users SET deleted='1', user_login=CONCAT('eliminado_',user_login) WHERE id_user='$id'";
        query($sql);
        $mostrarListado = true;

        break;
    default:
        $mostrarListado = true;
        break;

}







if ($mostrarEdicion) {
    $page->configMenu($newmodo);
    $page->setAttribute( 'edicion', 'src', 'edicion_usuarios.htm' );

    $page->addVar( 'edicion', 'modname',		$template["modname"] );
    $page->addVar( 'edicion', 'modoediciontxt',	$metodo );
    $page->addVar( 'edicion', 'modoedicion',	$newmodo );

    $page->addVar( 'edicion', 'optionsselectprofile', genComboProfiles($usuario->get("id_profile"), array("id"=>0) ) );

    $page->addVar( 'edicion', 'listaDeGrupos', genComboGrupos() );
    $page->addVar( 'edicion', 'listaDeGrupos2', genComboGruposDelegaciones() );

    include_once(__ROOT__ . "/class/group.class.php");

    $grupos = genGroupUser($usuario->get("id_user"),true    );
    $grupos2 = genGruposdelegacionUser($usuario->get("id_user"));

    $page->addVar( 'edicion', 'groupsismember', implode(",",$grupos ) . "," .implode(",",$grupos2 )  );

    $out = "";

    foreach( $grupos as $idgrupo) {
        if ($idgrupo) {            
            $nombreGrupo = getNombreGrupoFromId($idgrupo);

            $out .= "<option value='".$idgrupo."' class='removeOnClick'>".html($nombreGrupo)."</option>";
        } 
    }
    $page->addVar( 'edicion', 'lista_groupsismember',$out );



    $grupos = $grupos2;

    $page->addVar( 'edicion', 'groupsismember2', implode(",",$grupos ) );

    $out = "";
    foreach( $grupos as $idgrupo) {
        if ($idgrupo) {
            $nombreGrupo = getNombreGrupoFromId($idgrupo);
            $out .= "<option value='".$idgrupo."' class='removeOnClick'>".html($nombreGrupo)."</option>";
        }
    }
    $page->addVar( 'edicion', 'lista_groupsismember2',$out );


    $page->addArrayFromCursor( 'edicion',$usuario, array("id_user","name","user_login","pass_login","s_name1","s_name2","phone","email")  );
    
     $id_rama = $usuario->get("id_nodo");

    $id = CleanID($_POST["id"]);
    if ($id_rama) {
        $modo = "<input type='hidden' name='modo' value='carga' >";
        $dato = "<input type='hidden' name='id_rama' value='$id_rama' >";
        $ui_id_user = "<input type='hidden' name='id_user' value='$id' >";
        $submit = "<input type='submit' value='Editar'>";

        //$data = $permisos->auto_get_filtro($id_rama);

        $html = "Tiene reglas de datos: <!-- -->"
                . "<div style='border:1px solid #bbb;display:block' data-idrama='$id_rama,$data'>" . arbol::autodescribir($id_rama) . "</div>"
                . $multidesc
                . " <form action='modpermisos.php' method='post'>" . $modo . $dato . $ui_id_user . $nombreusuario . $submit  . "</form>";
    } else {



        $modo = "<input type='hidden' name='modo' value='creararbol' >";
        $ui_id_user = "<input type='hidden' name='id_user' value='$id' >";
        if ($metodo != "Alta"){
          $permisos20="";  
          $submit = "<input type='submit' value='Crear permisos'>";
          $html = "No tiene reglas de datos <form action='modpermisos.php' method='post'>" . $modo . $ui_id_user . $nombreusuario . $submit . "</form>";
        } else{
          $html = "<form action='modpermisos.php' method='post'>" . $modo . $ui_id_user . $nombreusuario . "</form>";
          $permisos20="oculto";
        }
            
        
    }

    $page->addVar('edicion', 'tiene', $html);
    $page->addVar('edicion', 'permisos20', $permisos20);

    
}

if ($mostrarListado) {
    $page->configMenu("listar");

    $page->setAttribute( 'listado', 'src', 'listado_usuarios.htm' );

    $maxfilas = $_SESSION[ $template["modname"] . "_list_size"];

    $min = intval($_SESSION["offset_usuarios"]);

    $list = array();

    $sql = "SELECT * FROM users WHERE deleted='0' $extracondicion ORDER BY s_name2, s_name1 ASC LIMIT $min,$maxfilas";

    $res = query($sql);

    $numFilas =0;
    while($row = Row($res) ) {

        if($row["name"]!="binow") {
            $estiloApropiado = ($numFilas %2)?"filaImpar":"filaPar";
            $numFilas++;

            $fila = array("modname"=>$template["modname"], "id"=>$row["id_user"], "name"=>$row["name"] ,"s_name1"=>$row["s_name1"] , "s_name2"=>$row["s_name2"], "s_email"=>$row["email"], "s_phone"=>$row["phone"], "s_user_login"=>$row["user_login"] );

            $list[] = $fila;
        }else {
            $numFilas++;
        }
    }

    $page->addRows('list_entry', $list );
    $page->configNavegador( $min, $maxfilas,$numFilas);
}


$page->Volcar();

