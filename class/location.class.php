<?php

/**
 * Lugar
 *
 * @package binow
 */


class Lugar extends Cursor {
        var $_nameid			= "id_location";
        var $_nombretabla               = "locations";

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("locations", "id_location", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("name");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo lugar"));
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

		$sql = "INSERT INTO locations ( $listaKeys ) VALUES ( $listaValues )";


	//	echo "<xmp>" . $sql . "</xmp>";

		$resultado = query($sql);

        if ($resultado){
            $this->setId($UltimaInsercion);
        }

        return $resultado;
	}


	function Modificacion () {
                /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"locations","id_location",$this->get("id_location"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo lugar");
			return false;
		}
		return true;*/
                return $this->Save();
	}


}










?>
