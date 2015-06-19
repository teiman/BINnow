<?php



class Usuario extends Cursor {

        var $_nameid		= "id_user";
        var $_nombretabla       = "users";

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("users", "id_user", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("client_name");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo Usuario"));
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

		$sql = "INSERT INTO users ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

                if ($resultado){
                     $this->setId($UltimaInsercion);
                }

                return $resultado;
	}


	function Modificacion () {
                
		
                $this->Save();
	}


}

