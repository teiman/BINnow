<?php

/**
 * Clase auxiliar de gateway de correo
 *
 * @package binow
 */

include_once("comunicacion.class.php");

class Correo extends Cursor {
    var $comm;


    var $_nameid			= "email_id_comm";
    var $_nombretabla               = "emails";

    function Usuario() {
        return $this;
    }

    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("emails", "email_id_comm", $id);
        return $this->getResult();
    }

    function setNombre($nombre) {

    }

    function getNombre() {
        return $this->get("email_subject");
    }

    function Crea() {
        $this->setNombre(_("Nuevo correo"));
    }


    function Enviar() {
        //TODO: si se quiere usar esta funcion, es deseable que utilice una clase de envio de correo
        // simply using mail will not 'cut it'
        $to = $this->get("from_to");
        $subject = $this->get("email_subject");
        $message = $this->get("email_body");

        return mail($to,$subject,$message);
    }


    function gir_simpleTask($data,$id_task_defecto=false){

            $titulo = $data["asunto"];
            $viejo = $titulo;

            $titulo = str_ireplace("INCIDENCIA","",$titulo);
            $titulo = str_replace("problema","",$titulo);
            $titulo = str_ireplace("reclamacion","",$titulo);
            $titulo = str_ireplace("reclamación","",$titulo);

            if($titulo != $viejo){
                return getParametro("binow.id_canal_incidencias");
            }

            return $id_task_defecto;
    }

    function AltaComunicacion($data,$inout="in") {

        $comunicacion = new Comunicacion();


        $comunicacion->set("date_cap",$this->get("email_time_system"));
        $comunicacion->set("title",$this->get("email_subject"));
        $comunicacion->set("in_out",$inout);
        $comunicacion->set("id_channel",$data["id_channel"]);

        $id_contactodesconocido = getIdContactoDesconocido();
        $comunicacion->set("id_contact",$id_contactodesconocido);

        //TODO: ¿que pasa con el campo "status"?

        if ($this->get("email_in_out") == "in")
            $comunicacion->set("from_to",$this->get("email_sender"));
        else
            $comunicacion->set("from_to",$this->get("email_receiver"));

        if(isset($data["id_task"])){
            $id_task = $data["id_task"];
        } else
            $id_task = false;

        if(!$id_task) {
            $id_task = $comunicacion->getTaskPorDefecto();
        }

        $id_task = $this->gir_simpleTask($data,$id_task);
        $comunicacion->set("id_task",$id_task);

        if(!isset($data["codcom"])){
            if($id_task != getParametro("binow.id_canal_incidencias")){
                $codigo = $comunicacion->autoCodigo($id_task);
                $comunicacion->set("codcom",$codigo);
            } else {
                $comunicacion->set("codcom","");
            }
        }else {
            $comunicacion->set("codcom",$data["codcom"]);
        }

        if (!$comunicacion->Alta()) {
            return false;
        }

        $id = $comunicacion->get("id_comm");

        $this->comm = $comunicacion;

        $this->set("email_id_comm",$id);
        return $this->Alta($id);
    }


    function ProcesaAdjuntos($adjuntos=false) {
        if (!$adjuntos)
            return;

        $id = $this->get("email_id_comm");

        foreach($adjuntos as $adjunto) {

            $filename_s = sql($adjunto["filename"]);
            $descripcion_s = sql($adjunto["description"]);

            $sql = "INSERT gw_email_subfiles ( path_subfile,description, email_id_comm) VALUES ( '$filename_s','$descripcion_s','$id') ";
            query($sql);
        }
    }

    function Alta($id) {
        global $UltimaInsercion;

        $this->set("email_id_comm",$id,FORCE);

        $data = $this->export();

        $coma = false;
        $listaKeys = "";
        $listaValues = "";

        foreach ($data as $key=>$value) {
            if ($coma) {
                $listaKeys .= ", ";
                $listaValues .= ", ";
            }

            $value = sql($value);

            $listaKeys .= " `$key`";
            $listaValues .= " '$value'";
            $coma = true;
        }

        $sql = "INSERT INTO emails ( $listaKeys ) VALUES ( $listaValues )";


        $resultado = query($sql);

        if ($resultado) {
            $this->setId($UltimaInsercion);
        }

        return $resultado;
    }


    function Modificacion () {
        return $this->Save();
    }

}

