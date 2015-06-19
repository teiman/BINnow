<?php

/**
 * Grupo
 *
 * @package binow
 */



/*
 *
 */
class Activador extends Cursor {
    var $_nameid             = "id_activador";
    var $_nombretabla        = "activadores";

    function Usuario() {
        return $this;
    }

    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("activadores", "id_activador", $id);
        return $this->getResult();
    }

    function setNombre($nombre) {

    }

    function getNombre() {
        return $this->get("nombre");
    }

    function Crea(){
        $this->setNombre(_("Nuevo activador"));
    }

    function Alta(){
        global $UltimaInsercion;

        $data = $this->export();

        $coma = false;
        $listaKeys = "";
        $listaValues = "";

        foreach ($data as $key=>$value){
            if ($coma) {
                $listaKeys .= ", ";
                $listaValues .= ", ";
            }

            $value = sql($value);

            $listaKeys .= " `$key`";
            $listaValues .= " '$value'";
            $coma = true;
        }

        $sql = "INSERT INTO activadores ( $listaKeys ) VALUES ( $listaValues )";


        $resultado = query($sql);

        if ($resultado){
            $this->setId($UltimaInsercion);
        }

        return $resultado;
    }


    function Modificacion () {
        return $this->Save();
    }


}

