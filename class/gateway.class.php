<?php

/**
 * Pasarelas
 *
 * @package binow
 */


include_once("inc/pasarelas.inc.php");

function getSugerenciasFromModulo( $module ){

	$canal = array();

	$module = str_replace(".php","",$module);
	$moduledir = str_replace(".","",$module);

	$integrator =  NormalizarPath(getcwd() . "/gateway/" . $moduledir ."/" ) . "integrator.php";
	
	if ( file_exists($integrator) ){

		include($integrator);
		
		$canal = genCanalList();
		
	} else {
		
	}

	return $canal;
}




class Pasarela extends Cursor {

        var $_nameid			= "id_gateway";
        var $_nombretabla               = "gateway";

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("gateway", "id_gateway", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("module");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva pasarela"));
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

		$sql = "INSERT INTO gateway ( $listaKeys ) VALUES ( $listaValues )";


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

		$sql = CreaUpdateSimple($data,"gateway","id_gateway",$this->get("id_gateway"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo gateway");
			return false;
		}
		return true;*/
                return $this->Save();
	}


}











?>