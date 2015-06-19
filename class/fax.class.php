<?php

/**
 * Clase auxiliar de gateway de correo
 *
 * @package binow
 */

class Fax extends Cursor {

        var $_nameid			= "fax_id_comm";
        var $_nombretabla               = "faxes";


	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("faxes", "fax_id_comm", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("fax_sender");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo correo"));
	}


	function Alta($file_zfax, $pdf_system,$fax_sender,$fax_receiver,$msgid,$fechahora,$id_channel){

		$this->set("fax_path_system", $pdf_system);
		$this->set("fax_path_provider", $file_zfax);

		$this->set("fax_time_provider", $fechahora);//TODO: convertir a unixtime
		$this->set("fax_time_system",date("Y-m-d H:i:s"));


		$comunicacion = new Comunicacion();

		$comunicacion->set("date_cap",$this->get("fax_time_system"));
		$comunicacion->set("title", "FAX: ". $this->get("fax_sender"));
		$comunicacion->set("in_out","in");
		$comunicacion->set("id_channel",$id_channel);


		$id_contactodesconocido = getIdContactoDesconocido();
		$comunicacion->set("id_contact",$id_contactodesconocido);

		//TODO: ¿que pasa con el campo "status"?

		if ($this->get("fax_in_out") == "in")
			$comunicacion->set("from_to",$this->get("fax_sender"));
		else
			$comunicacion->set("from_to",$this->get("fax_receiver"));

		$comunicacion->Alta();

		$id_comm = $comunicacion->get("id_comm");

		$this->set("fax_id_comm",$id_comm,FORCE);
		$this->AltaFax();

		hfax_pdf_marcarconocido($msgid, $id_comm);
	}



	function AltaFax(){
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

		$sql = "INSERT INTO faxes ( $listaKeys ) VALUES ( $listaValues )";


		//echo "<xmp>" . $sql . "</xmp>";

		$resultado = query($sql);

        if ($resultado){
            $this->setId($UltimaInsercion);
        }

        return $resultado;
	}


	function Modificacion () {
                /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"emails","email_id_comm",$this->get("email_id_comm"));

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