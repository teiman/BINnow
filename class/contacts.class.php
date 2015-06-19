<?php
/**
 * Clase de gestion de contactos
 *
 * @package binow
 */



/**
 * Representa un buzon
 *
 * @package binow
 */

class Contacto extends Cursor {
        var $_nameid			= "id_contact";
        var $_nombretabla               = "contacts";


        public function getIdFromCode($codigo){
            static $tabla = array();

            if(isset($tabla[$codigo])){
                return $tabla[$codigo];
            }

            $codigo_s = sql($codigo);
            $sql = "SELECT id_contact FROM contacts WHERE contact_code LIKE '$codigo_s' ";
            $row = queryrow($sql);

            $id_contact = $row["id_contact"];

            if(!$id_contact or !$row){
                $id_contact = getIdContactoDesconocido();
            }

            $tabla[$codigo] = $id_contact;

            return $id_contact;
        }


        public function getNombreFromId($id_contact){
            static $tabla = array();

            if(isset($tabla[$id_contact])){
                return $tabla[$id_contact];
            }

            $id_contact_s = sql($id_contact);
            $sql = "SELECT contact_name FROM contacts WHERE id_contact='$id_contact_s' ";
            $row = queryrow($sql);

            $tabla[$id_contact] = $row["contact_name"];

            return $row["contact_name"];
        }


	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("contacts", "id_contact", $id);
		return $this->getResult();
	}


  	function getNombre() {
		return $this->get("contact_name");
  	}

  	function settNombre($name) {
		return $this->set("contact_name",$name);
  	}


  	function Crea(){
		$this->setNombre(_("Nuevo contacto"));
	}

	function Alta(){
                global $UltimaInsercion;

                if(!$this->get("priority")){
                    $this->set("priority","Baja");
                }

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

		$sql = "INSERT INTO contacts ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

                if ($resultado){
                     $this->setId($UltimaInsercion);
                }

                return $resultado;
	}


        function buscarCodigo($codigo){

            if($codigo == $this->get("contact_code")){
                return $this->get("id_contact");
            }

            $code_s = sql($codigo);
            $sql = "SELECT id_contact FROM contacts WHERE contact_code LIKE '$code_s' ";

            $row = queryrow($sql);

            if($row){
                return $row["id_contact"];
            }

            return false;
        }


	function Modificacion () {
                /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"contacts","id_contact",$this->get("id_contact"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo Usuario");
			return false;
		}
		return true;*/
                $this->Save();
	}

}



class Cliente extends Contacto {

    
}


