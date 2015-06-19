<?php

/**
 * Tareas
 *
 * @package binow
 */


class Tarea extends Cursor {
        var $_nameid			= "id_task";
        var $_nombretabla               = "tasks";


    /*
     * Devuelve el id de buzon que usa ese email
    */
    public function getTaskFromEmail( $email ) {
        $email_s = sql($email);

        //$id = getParametro("core.id_canal_defecto"); <- task

        //if(!$id) $id = 1;

        $sql = "SELECT id_task as id FROM channels WHERE channel LIKE '$email_s' ";
        $row = queryrow($sql);

        if($row) $id=$row["id"];

        return $id;
    }

	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("tasks", "id_task", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("task");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva tarea"));
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

		$sql = "INSERT INTO tasks ( $listaKeys ) VALUES ( $listaValues )";


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

		$sql = CreaUpdateSimple($data,"tasks","id_task",$this->get("id_task"));

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