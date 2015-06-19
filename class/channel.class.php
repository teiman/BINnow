<?php
/**
 * Clase de gestion de canales
 *
 *
 * @package binow
 */


class Canal extends Cursor {

    var $_nameid			= "id_channel";
    var $_nombretabla               = "channels";

    /*
     * Devuelve el id de buzon que usa ese email
    */
    public function getChannelFromEmail( $email ) {
        $email_s = sql($email);

        //$id = getParametro("core.id_canal_defecto"); <- task

        if(!$id) $id = 1;

        $sql = "SELECT id_channel as id FROM channels WHERE channel LIKE '$email_s' ";
        $row = queryrow($sql);

        if($row) $id=$row["id"];

        return $id;
    }



    function Usuario() {
        return $this;
    }

    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("channels", "id_channel", $id);
        return $this->getResult();
    }

    function setNombre($nombre) {

    }

    function getNombre() {
        return $this->get("channel");
    }

    function Crea() {
        $this->setNombre(_("Nuevo canal"));
    }

    function Alta() {
        global $UltimaInsercion;

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

        $sql = "INSERT INTO channels ( $listaKeys ) VALUES ( $listaValues )";

        $resultado = query($sql);

        if ($resultado) {
            $this->setId($UltimaInsercion);
        }

        return $resultado;
    }


    function Modificacion () {
        /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"channels","id_channel",$this->get("id_channel"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;*/
        return $this->Save();
    }

}



?>
