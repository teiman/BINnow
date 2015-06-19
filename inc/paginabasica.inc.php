<?php

class paginaBasica extends Pagina {

    function setTitulo($nombre) {
        $this->addVar('headers', 'titulopagina', $titulo);
    }

}

;

$lang = "es";

$page = new paginaBasica();

$page->Inicia($template["modname"]);
$page->addVar('menu', 'labelbasica', getParametro('labelbasica_es'));
