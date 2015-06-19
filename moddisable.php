<?php

/**
 * El usuario no esta autorizado
 * @package binow
 */



if (!is_object($page)){
    include("inc/paginabasica.inc.php");
}

$page->addVar('headers', 'titulopagina', '' );

$page->setAttribute( 'informacion', 'src', 'info_sinderechos.htm' );


/*
 * Esta logueado, pero no tiene permiso
 * Sugerir volver
 */

if(getSesionDato("id_user")){
    $page->addVar('informacion','sugerirvolver',"<p>Puede <a href='javascript:history.go(-1)'>volver a la página anterior</a></p>");

    $page->addVar('informacion','conotro',' con otro usuario');
}


$page->addVar('informacion', 'pathfallido', $auth["path"]);

$page->addVar("page","nologin","<!--");
$page->addVar("page","nologin2","-->");
$page->addVar("cabeza","nologin","<!--");
$page->addVar("cabeza","nologin2","-->");



$page->Volcar();

die();
