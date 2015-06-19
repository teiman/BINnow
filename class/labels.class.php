<?php

/**
 *  Etiqueta
 *
 * @package binow
 */


class Etiqueta extends Cursor {


	var $metadata;


        var $_nameid			= "id_label";
        var $_nombretabla               = "labels";

	function Usuario() {
		return $this;
	}


	function LoadByName($name,$iduser=false){

		$name_s = sql( trim($name));

                $extra = "";
                if($iduser){
                    $extra = " AND id_user='$iduser' ";
                }

		$sql = "SELECT id_label FROM labels WHERE label='$name_s' $extra LIMIT 1";

                error_log($sql);
		$row = queryrow($sql);

		$id_label = $row["id_label"];



		if ($id_label) {

			error_log("i: name:$name_s=($id_label)");
			return $this->Load($id_label);
		}


		return false;
	}



	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("labels", "id_label", $id);
		return $this->getResult();
	}

  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("label");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva etiqueta"));
	}


	function valida_isobligator(){


	}




	function eliminarComlink($id_comlink){
		$id_comlink_s = CleanID($id_comlink);

		error_log( __LINE__ . "elimina ($id_comlink_s)");

		$sql = "SELECT * FROM label_coms
          INNER JOIN labels ON  label_coms.id_label =labels.id_label
          INNER JOIN    label_types ON labels.id_label_type = label_types.id_label_type
          WHERE id_label_com = '$id_comlink_s' LIMIT 1";

		$row = queryrow($sql);

		//error_log( __LINE__ . "datosprevia:".var_export($row,true));

		$sql = "DELETE FROM label_coms WHERE id_label_com='$id_comlink_s'";
		query($sql);


		//error_log( __LINE__ . "se ha eliminado ($id_comlink_s)");

		if ($row["isobligatory"]){

			//error_log( __LINE__ . "($id_comlink_s) era obligatorio");


			 $id_type = $row["id_label_type"];
			 $id_comm = $row["id_comm"];

			//error_log( __LINE__ . "tipo:$id_type,id_comm:$id_comm");

			$sql = "SELECT count(label_coms.id_comm) as cuantas FROM label_coms
          INNER JOIN labels ON  label_coms.id_label =labels.id_label
          INNER JOIN    label_types ON labels.id_label_type = label_types.id_label_type
          WHERE label_coms.id_comm =$id_comm AND label_types.id_label_type= $id_type";
			$rowcuantos = queryrow($sql);
			if(!($rowcuantos["cuantas"]>0)){

				//error_log( __LINE__ . "era el ultimo de su clase!, renovar=>");

				$id_comm = $row["id_comm"];
				$id_default = $row["id_label_default"];

				if($this->Load($id_default)){
					$this->createLink($id_comm);
				}
			}
		}
	}

	function createLink($id_comm){

		$id_comm_s = CleanID($id_comm);
		$id_label_s = sql($this->get("id_label"));

		if(!$id_label_s or !$id_comm )
			return;

		$sql = "SELECT id_label_com FROM label_coms WHERE id_comm='$id_comm_s' AND id_label='$id_label_s' LIMIT 1";
		$row = queryrow($sql);

		if ($row) {
			error_log("ya existe esta etiqueta");
			return;
		}


		if ( $this->isUnique() ){

			//error_log("es unico");

			$id_type_s = $this->getType();

			//error_log("tipo:$id_type_s");

			$sql = "SELECT label_coms.id_label_com FROM label_coms INNER JOIN labels ON label_coms.id_label = labels.id_label".
			" INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type".
			" WHERE labels.id_label_type='$id_type_s' AND label_coms.id_comm='$id_comm_s' LIMIT 1 ";
			$row = queryrow($sql);

			if ($row){

			//	error_log("hay otro:si ($sql),".var_export($row,true));

				$thisid = $this->get("id_label");
				$id_label_com_s = $row["id_label_com"];
				$sql = "UPDATE label_coms SET id_label='$thisid' WHERE label_coms.id_label_com ='$id_label_com_s'  ";
				query($sql);
			//	error_log("recuperamos para nosotros:sql,".$sql);
				return;
			} else {
			//	error_log("no hay otros:$sql");
			}
		
		} else {
			//error_log("no es unica");
		}

		$sql = "INSERT INTO label_coms (id_label,id_comm)VALUES('$id_label_s','$id_comm_s')";
		query($sql);
	}


	function LoadMetadata(){

		$id_label_s = sql($this->get("id_label"));
		$sql = "SELECT * FROM labels INNER JOIN label_types ON labels.id_label_type = label_types.id_label_type
          WHERE id_label = '$id_label_s' LIMIT 1";
		
		$this->metadata = queryrow($sql);		
	}


	function isUnique(){
		if ( !$this->metadata) $this->LoadMetadata();

		return $this->metadata["isunique"];
	}

	function isOblogatory(){
		if ( !$this->metadata) $this->LoadMetadata();

		return $this->metadata["isobligatory"];
	}

	function getType(){
		if ( !$this->metadata) $this->LoadMetadata();
		
		return $this->metadata["id_label_type"];
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

		$sql = "INSERT INTO labels ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

        if ($resultado){
             $this->setId($UltimaInsercion);
        }

        return $resultado;
	}


	function Modificacion () {
                /*
		$data = $this->export();

		$sql = CreaUpdateSimple($data,"labels","id_label",$this->get("id_label"));

		$res = query($sql);
		if (!$res) {
			$this->Error(__FILE__ . __LINE__ , "W: no actualizo etiqueta");
			return false;
		}
		return true;*/
                return $this->Save();
	}


}



?>