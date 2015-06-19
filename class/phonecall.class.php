<?php




class phonecall extends Cursor {

        var $_nameid			= "call_id_comm";
        var $_nombretabla               = "phone_calls";

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("phone_calls", "call_id_comm", $id);
		return $this->getResult();
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

		$sql = "INSERT INTO phone_calls  ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
             $this->setId($UltimaInsercion);
        }

        return $resultado;
	}


	function Modificacion () {
                /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"phone_calls","call_id_comm",$this->get("call_id_comm"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualiza comm");
			return false;
		}

		return true;*/
                return $this->Save();
	}

}



class nota_llamada extends Comunicacion {

	var $esRecibida = false;

	var $extradatos = array();

	function setEsRecibida($valor){
		$this->set("in_out", $valor?"in":"out");
		$this->esRecibida = $valor;
	}

	function setQuien($quien){
		$this->extradatos["quien"] = $quien;
	}

	function setAQuien($aquien){
		$this->extradatos["aquien"] = $aquien;
	}

	function setTelefono($telefono){
		$this->extradatos["telefono"] = $telefono;
	}

	function setCodCliente($codcliente){
		$this->extradatos["codcliente"] = $codcliente;
	}

	function setMotivoLlamada($motivo){
		$this->extradatos["motivo"] = $motivo;
		$this->set("title",$motivo);
	}
	
	function setNotas($notas){
		$this->extradatos["notas"] = $notas;
	}

	function AltaLlamada(){
		global $trans;

		if (!$this->get("id_contact")) {
			$id_contactodesconocido = getIdContactoDesconocido();
			$this->set("id_contact",$id_contactodesconocido);
		}


		$this->set("from_to", $this->esRecibida?$this->extradatos["quien"]:$this->extradatos["aquien"]);


		$this->set("date_cap",date("Y-m-d H:i:s"));
		$this->set("id_channel",getParametro("id_channel_notallamada"));


		$this->Alta();

		$phonecall = new phonecall();
		$phonecall->set("call_in_out",$this->get("in_out"));
		$phonecall->set("call_id_comm",$this->get("id_comm"));
		$phonecall->set("call_time_provider",$this->get("date_cap"));
		$phonecall->set("call_time_system",$this->get("date_cap"));
		$phonecall->set("call_information",serialize($this->extradatos));

		$phonecall->set("call_sender",$this->extradatos["quien"]);
		$phonecall->set("call_receiver",$this->extradatos["aquien"]);
		//$phonecall->set("call_time",$this->extradatos["time"]);

		$phonecall->Alta();

		$this->Etiquetar( $this->esRecibida?_("Recibida"):_("Enviada"));
	}


}










?>