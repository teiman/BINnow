<?php
class Etiqueta extends Cursor {


	var $metadata;


        var $_nameid			= "id_shipment";
        var $_nombretabla               = "shipments";

	function Usuario() {
		return $this;
	}


	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("shipments", "id_shipment", $id);
		return $this->getResult();
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

		$sql = "INSERT INTO shipments ( $listaKeys ) VALUES ( $listaValues )";

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
