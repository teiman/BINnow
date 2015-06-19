<?php
class Etiqueta extends Cursor {


	var $metadata;


        var $_nameid			= "id_sample";
        var $_nombretabla               = "samples";

	function Usuario() {
		return $this;
	}


	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("samples", "id_sample", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("sample_name");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo envio"));
	}




	function Alta(){
        global $UltimaInsercion;

		$data = $this->export();

		$listaKeys		= "";
		$listaValues	= "";
		$coma			= "";

		foreach ($data as $key=>$value){
            $value = sql($value);

			$listaKeys		.= " $coma `$key`";
			$listaValues	.= " $coma '$value'";

			$coma = ",";
		}

		$sql = "INSERT INTO samples ( $listaKeys ) VALUES ( $listaValues )";

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
?>
