<?php

/**
 * Profile
 *
 * @package binow
 */


/*
 * Extrae el nombre desde su id
 *
 */
function getNombreProfileFromId( $id_profile ){
	$sql = "SELECT * FROM profiles WHERE id_profiles ='$id_profile' LIMIT 1";
	$row = queryrow($sql);

	return $row["name"];
}




/*
 *
 */
class Profile extends Cursor {

        var $_nameid			= "id_profile";
        var $_nombretabla               = "profiles";

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("profiles", "id_profile", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("name");
  	}

  	function Crea(){
		$this->setNombre(_("Nuevo perfil"));
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

		$sql = "INSERT INTO profiles ( $listaKeys ) VALUES ( $listaValues )";


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

		$sql = CreaUpdateSimple($data,"profiles","id_profile",$this->get("id_profile"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo profile");
			return false;
		}
		return true;*/
                return $this->Save();
	}


}










?>