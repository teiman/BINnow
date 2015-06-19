<?php

/**
 * Clase de gestion de comunicaciones
 *
 *
 * @package binow
 */


if ( !defined('COMMUNICATION_CLASS') ):

define('COMMUNICATION_CLASS',1);


include_once("eac.class.php");
include_once("labels.class.php");


function nuevoLugar($id_comm,$id_location){
        $id_comm_s = sql($id_comm);
        
        $id_location_s = sql($id_location);
    
        $sql = "UPDATE  communications SET id_location_anterior=id_location WHERE id_comm ='$id_comm_s' LIMIT 1";
        query($sql);


        $sql = "UPDATE  communications SET id_location='$id_location_s' WHERE id_comm ='$id_comm_s' LIMIT 1";
        query($sql);

        Comunicacion::Trazar($id_comm_s);
}



/*
 * Marca una comunicacion como leida por el usuario logueado
 */
function markRead($id_comm){
	$id_com_s = CleanID($id_comm);
	$id_user_s = CleanID(getSesionDato("id_user"));

	$sql = "SELECT 1 FROM read_comm  WHERE id_comm='$id_com_s' AND id_user='$id_user_s' ";
	$row = queryrow($sql);

	if (!$row){
		$sql = "INSERT INTO read_comm ( id_comm,id_user, date_read) values ('$id_com_s','$id_user_s',NOW())";
		query($sql);
	}
}

/*
 * Devuelve el id del contacto desconocido
 */
function getIdContactoDesconocido(){
        static $datos = array();
        
        if(isset($datos["id"])){
            return $datos["id"];            
        }

	$sql = "SELECT * FROM contacts WHERE contact_unknown=1 LIMIT 1";
	$row = queryrow($sql);

	$id = $row["id_contact"];
        $datos["id"] = $id;

        return $id;
}


/*
 * Devuelve el id del usuario desconocido
 */
function getIdUserDesconocido(){
    return getParametro("core.id_user_desconocido");
}



/*
 * Extrae el preview_html o similar de una com.
 */
function getPreviewForCom( $id_comm ){
	$id_comm = CleanID($id_comm);

	$sql = "SELECT * FROM emails WHERE email_id_comm='$id_comm' ";
	$row = queryrow($sql);

	return $row["email_preview_html"];
}




class Comunicacion extends Cursor {
        
        var $_nameid			= "id_comm";
        var $_nombretabla               = "communications";


	function Usuario() {
		return $this;
	}

	function Load($id) {
		$id = CleanID($id);
		$this->setId($id);
		$this->LoadTable("communications", "id_comm", $id);
		return $this->getResult();
	}
                             
        function nuevoLugar($id_new_location){
            
            $id_old_location = $this->get("id_location");            
            $this->set("id_location_anterior",$id_old_location);
            
            $this->set("id_location",$id_new_location);
        }
        

        /*
         * Carga un codigo seriado, que es unico para el canal. Este codigo sera utilizado para construir el identificador del pedido/incidencia/..
         *
         */
        public function autoCodigo($id_task){

            $sql = "SELECT nextcode+1 as siguiente FROM tasks WHERE (id_task ='$id_task') ";
            $row = queryrow($sql);

            if(!$row){
                error_log("ERROR: se intento generar un serial para canal que no se encuentra");
                return false;
            }
            $serie = intval($row["siguiente"]);

            $agno = date("Y");
            $numceros = 6;

            

            $sql = "UPDATE tasks SET nextcode = nextcode+1 WHERE (id_task='$id_task')";
            query($sql);

            $letra = "P";
            
            if($id_task==getParametro("binow.id_canal_incidencias")){
                $letra = "IN";
            }

            $numpedido = sprintf("%0".$numceros."d",$serie);

            $code = $letra . $numpedido . "/" .$agno;

            $code_s = sql($code);

            $sql = "SELECT id_comm FROM communications WHERE codcom='$code_s' ";
            $row = queryrow($sql);

            if($row){//si ya existe, buscamos otro codigo siguiente a este que no exista. No queremos usar nunca un codigo que ya existe.
                $code = Comunicacion::autoCodigo($id_task);
            }


            if(0) echo "Se ha generado: code=($code)<br>\n";

            return $code;
        }

	function getMiModulo(){
		$id_channel= $this->get("id_channel");

		$sql = "SELECT module
				FROM `channels`
				INNER JOIN medias ON channels.id_media = medias.id_media
				INNER JOIN gateway ON gateway.id_gateway = medias.id_gateway
				WHERE id_channel ='$id_channel'";
		$row = queryrow($sql);

		//die(var_export($row));
		$mod= $row["module"];

		if(!$mod)
			error_log("e: no se encontro modulo para id($id_channel)sql($sql)");
		//else
		//	error_log("OK: mod es". $mod . "para $id_channel");

		return $mod;
	}


	function getVista(){
	
		$modulo = $this->getMiModulo();

		//if(!$modulo) return "No existe visualizador para este tipo de documento (ch:$id_channel|$modulo)";
		if(!$modulo) {
			error_log("e: no se encontro modulo");
			return false;
		}

		$dir = getPathBaseModule($modulo);

		$mod = getValidModule($dir,"vista.plugin.php");

		if($mod){
			include($mod);
			$id_channel= $this->get("id_channel");
			return genVistaFinal($this);
		} else {
			//return "mod [$mod] no se encuentra";
			return false;
		}
	}


  	function setNombre($nombre) {

  	}

  	function getNombre() {
		return $this->get("title");
  	}

  	function Crea(){
		$this->setNombre(_("Nueva com."));
	}

	function EtiquetasNecesarias(){

		$sql = "SELECT * FROM label_types WHERE isobligatory=1";

		$res = query($sql);

		$label = new Etiqueta();
		$id_comm = $this->get("id_comm");

		while($row = Row($res)){
			$id_label_default = $row["id_label_default"];
			$label->Load($id_label_default);
			$label->createLink($id_comm);
		}
	}

	function RunRulesDebug(){
        /* Reglas avanzadas */
        $flex = new FlexMaker();
		$flex->setCom( $this );
		$flex->RunRulesDebug();
	}



	function RunRules(){     
                /* Reglas sencillas */
                /*
		$maker = new RuleMaker();
		$maker->setCom( $this );
		$maker->RunRules();*/
         
                /* Reglas avanzadas */
                $flex = new FlexMaker();
		$flex->setCom( $this );
		$flex->RunRules();        
	}

    public function getGrupoPorDefecto(){
        return getParametro("core.id_group_default");  ;
    }
    
    function getStatusPorDefecto(){
        $canal = $this->get("id_task");
        $sql = "SELECT id_status as id FROM `status` WHERE id_task='$canal' AND `default`=1 LIMIT 1";
        $row = queryrow($sql);
        
        return $row["id"];
    }
    
    public function getLocationPorDefecto(){
        return getParametro("core.id_location_default");
    }
    
    public function getTaskPorDefecto(){
        return getParametro("core.id_canal_defecto");
    }

    function getChannelPorDefecto(){
        return 1;//TODO: hacer una variable
    }


    function Alta(){
        global $UltimaInsercion;
	

        if (!$this->get("priority"))
            $this->set("priority","normal");
                         
        if (!$this->get("id_group")){
            $this->set("id_group", $this->getGrupoPorDefecto());
        }
        
        if (!$this->get("id_location")){
            $this->set("id_location", $this->getLocationPorDefecto()) ;
        }        

        if (!$this->get("id_task")){
            $this->set("id_task", $this->getTaskPorDefecto()) ;
            
        }        

        if (!$this->get("id_status")){
            //NOTA: depende de id_task, luego tiene que ir despues suyo
            $this->set("id_status", $this->getStatusPorDefecto());
        }

        if (!$this->get("id_channel")){
            $this->set("id_channel", $this->getChannelPorDefecto()) ;
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

		$sql = "INSERT INTO communications  ( $listaKeys ) VALUES ( $listaValues )";

		$resultado = query($sql);

                if ($resultado){
                         $this->setId($UltimaInsercion);
                         //$this->set("id_comm",$UltimaInsercion,FORCE);


			//Vamos a correr EAC para la comunicación.
			$this->RunRules();
			$this->Traza();

			//vamos a crear todas las etiquetas obligatorias
			$this->EtiquetasNecesarias();



                }

                return $resultado;
	}


	function Traza(){
		$id_comm = $this->get("id_comm");
		$id_group = $this->get("id_group");
		$id_status = $this->get("id_status");
        $id_location = $this->get("id_location");
		$id_user = getSesionDato("id_user");//TODO: reconocer usuario sistema?

		$sql = "INSERT INTO trace  ( id_comm,id_user, id_group,id_status,date_change,id_location ) VALUES ".
				"( '$id_comm','$id_user','$id_group', '$id_status', NOW(), '$id_location' )";

		if (!query($sql,'Traza')){
			//die("error: $sql");
		}
	}

    public function Trazar($id_comm){
        $id_comm_s = sql($id_comm);
        $id_user = getSesionDato("id_user");//TODO: reconocer usuario sistema?

        if($id_comm_s>0){
            $sql = "INSERT INTO trace(id_comm,id_user,id_group,id_status,date_change,id_location)
                SELECT id_comm,'$id_user',id_group,id_status,NOW(),id_location FROM communications WHERE id_comm='$id_comm_s'";
            query($sql);
        }
    }


	function Modificacion () {
        if($this->get("id_location")<=0){
            error_log("FATAL: id_location 0");
            $this->set("id_location",1);
        }
        if($this->get("id_channel")<=0){
            error_log("FATAL: id_channel 0");
            $this->set("id_channel",1);
        }

        if($this->get("id_group")<=0){
            error_log("FATAL: id_group 0");
            $this->set("id_group",1);
        }

        if($this->get("id_task")<=0){
            error_log("FATAL: intenta modificar pedido, haciendolo id_task 0");
            $this->set("id_task",14);
        }
        //SELECT * FROM communications WHERE id_location=0 or id_channel=0 or id_group=0 or id_task=0

        return $this->Save();
	}

	function Etiquetar($etiqueta,$tipo=false){
		$etiqueta = trim($etiqueta);

		$label = new Etiqueta();
		if ( $label->LoadByName($etiqueta)) {
			$label->createLink($this->get("id_comm"));
		} else {

			$label->set("label",$etiqueta);
			$label->Alta();

			$label->createLink($this->get("id_comm"));
		}
	}
}

endif;

