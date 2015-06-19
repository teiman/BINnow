<?php


die("ALPHA CODE CALLED");

class Cursor {

	// DATOS de DATOS

	protected $_datosFila; //Datos de fila
	protected $_idFila; //id de fila
	protected $_nameid; // nombre del id en la tabla
	protected $_nombretabla; //Nombre de la tabla.
	protected $_result; //Resultado de la ultima operacion.
	protected $_error_msg; //mensaje de error en formato nice
        protected $_touch;

	// DATOS de PAGINADOR

	var $_resPagina;

	public function LoadTable($tabla, $nameid, $id) {
		// Cada tabla se carga con un identificador y el nombre de tabla
		//if (!$id)	return;

		$id = CleanID($id);

		$this->_idFila			= $id;
		$this->_nameid			= $nameid;
		$this->_nombretabla		= $tabla;
                $this->_touch = array();

		$sql = "SELECT * FROM ".$tabla." WHERE (".$nameid."='".$id."') ";
		$myresult = query($sql);

		if ($myresult) {
			$this->_datosFila = Row($myresult);

			if (!$this->_datosFila)
				$myresult = false;
			else {
				foreach ($this->_datosFila as $key => $value) {
					if (!isset ($value)) {
						$this->_datosFila[$key] = 0;
					}
				}
			}
		}

		$this->_result = $myresult;

		return $myresult;
	}

	public function get($campo) {
		$valor = false;
		if (!isset ($this->_datosFila)) {
			//$this->Error(__LINE__. __FILE__, "Fatal: no hay data en data leyendo $campo!");
			$this->_datosFila = array();
			return false;
		}
		if (isset($this->_datosFila[$campo])) {
                        $this->_touch[$campo] = true;
			return $this->_datosFila[$campo];
                }
		return false;
	}

	// gets
	public function getId(){
		return intval($this->_idFila);
	}

	// import/export
	public function export() {
		return $this->_datosFila;
	}


	public function import($mifila) {
		if (!is_array($mifila))
			$mifila = array ();
		if (!is_array($this->_datosFila))
			$this->_datosFila = array ();

		if (!isset($mifila))
			$this->Error(__FILE__.__LINE__, "E: No se acepta importar datos vacios.");

		$this->_datosFila = array_merge($this->_datosFila, $mifila);
	}

	public function set($key, $valor, $force = true) {
                if (is_array($valor))
                    $valor = $valor[$key];

		if ($this->isKey($key))
			$this->_set($key, $valor);
		else {
			if ($force) {
				$this->_set($key, $valor);
				return;
			}
		}
	}

	private function _set($campo, $valor) {
		if (!$this->_datosFila)
			$this->_datosFila  = array(); //sino un set force sin cargar el objeto falla

                $this->_touch[$campo] = true;

		$this->_datosFila[$campo] = $valor;
	}

	public function isKey($key) {
		if (!isset ($this->_datosFila))
			return false;

		if (!isset($key)) {
			$this->Error(__FILE__.__LINE__," No existe este campo $key");
			return false;
		}

		if (isset ($this->_datosFila[$key]))
			return true;

		return false;
	}

	public function Save($hackCheckBox = false,$desdeDonde=false) {
		//Si no hay nada que salvar, echo! :I
                $numCambios = 0;

		if (!$this->_datosFila)
			return false;

		$id = $this->_idFila;

		if (!$id){
                    throw new Exception("Guardando tabla sin idfila");
                    return false;
                }

		$coma = false;
		foreach ($this->_datosFila as $key => $value) {
			//TODO: optimizar este codigo
			//TODO: array_join(... por ejemplo
			if ( intval($key) == 0 and $key != "0" and $key != $this->_nameid) {

				if ($hackCheckBox and $value == "on")
					$value = 1;

                                if ($this->_touch[$key]){

                                    if ($coma) {
                                        $str .= ",";
                                        $coma = false;
                                    }

                                    $value = mysql_escape_string($value);

                                    $str .= " `$key` = '".$value."'";
                                    $coma = true;
                                    $this->_touch[$key] = false;
                                    $numCambios++;

                                }
			}
		}

		$sql = "UPDATE ".$this->_nombretabla." SET ". $str." WHERE ".$this->_nameid." = '$id'";

                if ($numCambios>0){
                    $this->result = query($sql,$desdeDonde);
                    if (!$this->result) {
                        $this->Error(__LINE__, "E: no pudo salvar");
                        return false;
                    }
                }

		return true;
	}


       public function __isset($key) {
            //return isset($this->data[$name]);


            if (!isset ($this->_datosFila))
                    return false;

            if (!isset($key)) {
                    //$this->Error(__FILE__.__LINE__," No existe este campo $key");
                    return false;
            }

            if (isset ($this->_datosFila[$key]))
                    return true;

            return false;
        }


    public function __set($campo, $valor) {

        echo "__set en $campo con $valor.";

        if (!$this->_datosFila)
                $this->_datosFila  = array(); //sino un set force sin cargar el objeto falla

        $this->_touch[$campo] = true;

        $this->_datosFila[$campo] = $valor;
    }

    public function __get($campo) {
        $valor = false;
        if (!isset ($this->_datosFila)) {
                $this->_datosFila = array();
                return false;
        }
        if (array_key_exists($campo,$this->_datosFila)) {
                $this->_touch[$campo] = true;
                return $this->_datosFila[$campo];
        }
        return false;

    }

}

















?>