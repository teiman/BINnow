<?php

/**
 * Reglas
 *
 * @package binow
 */

include_once("labels.class.php");
include_once("location.class.php");
include_once("contacts.class.php");
include_once("users.class.php");

function registra_gir($texto){
    global $serial_registra;
    $txt = date("Y-m-d H:i:s") . " ". $serial_registra ." " . $texto ;
    error_log($txt . "\n",3,"/var/www/registros/gir".date("Ymd").".log");
       
//    @ob_flush();
//    @flush();
}

function insertAsignacionesGir($id_eac, $id_usuario_logeado, $automatico_manual, $descripcion, $id_comm){
    
    $sql = "Insert Into asignaciones_gir (id_eac, id_usuario_logeado, automatico_manual, descripcion, id_comm, fecha) Values ( $id_eac, $id_usuario_logeado, '$automatico_manual', '$descripcion', $id_comm, NOW())";
    
    query($sql);
    
    
}

function cargaDatosAsignacion($id_eac, $id_usuario_logeado, $descripcion, $id_comm){
            $id_eac_a = $id_eac;
            $id_usuario_logeado_a = $id_usuario_logeado;
                         
            if($id_usuario_logeado_a){
                $automatico_manual_a = "Manual";
            }
            else {
                $automatico_manual_a = "Automatico";
                $id_usuario_logeado_a = 0;
            }
            $descripcion_a = $descripcion;
            $id_comm_a = $id_comm;
            insertAsignacionesGir($id_eac_a, $id_usuario_logeado_a, $automatico_manual_a, $descripcion_a, $id_comm_a);
       
}


$global_rules_eac = false;
$global_rules_eac_sel = false;

$combocondiciones = array(
        "eac_date_in_from"=>_("Desde fecha"),
        "eac_from"=>_("Origen comunicación"),
        "eac_to"=>_("Destino comunicación"),
        "eac_title"=>_("Titulo"),
        "eac_content"=>_("Contenido"),
        "eac_com_dir_out"=>_("Saliente"),
        "eac_com_dir_in"=>_("Entrante"),
        "eac_contact"=>_("Contacto"),
        "eac_label_cat"=>_("Idioma comunicación catalán")
);

$comboefectos = array(
        "cambiadelegacion"=>_("Cambia delegacion"),
        "cambiacliente"=>_("Cambia cliente"),
        "aplicalabel"=>_("Etiquetar"),
        "aplicatask"=>_("Cambia canal"),
        "enviaemail"=>_("Envia email"),
        "enviafax"=>_("Envia fax"),
        "asignausuario"=>_("Asigna a usuario")
);

    function getTriggerData($id_eac){ //Solo devuelve un tirgger!, podria haber mas de uno
            static $table= array();

            if ($table[$id_eac]) return $table[$id_eac];

           $row = queryrow("SELECT * FROM eac_data WHERE istrigger=1 AND (eac_id=$id_eac) ORDER BY eac_id ASC LIMIT 1");

           $table[$id_eac] = $row;

           return $row;
    }
    


function old_gir_AsignacionParcialOrigen($id_cliente,$origen) {
    $id_cliente = CleanID($id_cliente);
    $s_origen = sql($origen);
    $id_delegacion = $_SESSION["delegacion_activa"];

    $sql = "SELECT count(id_asignacion_cliente) as cuantos FROM asignaciones_cliente WHERE enviado_desde='$s_origen'";
    $row = queryrow($sql);
    if($row["cuantos"]<1) {
        $sql = "INSERT INTO asignaciones_cliente (id_cliente,id_delegacion,enviado_desde)".
                " VALUE ('$id_cliente','$id_delegacion','$s_origen')";
        query($sql);

        gir_ActualizacionEnCascada($id_cliente,$origen);
        return;
    }

    $sql = "UPDATE asignaciones_cliente SET id_cliente='$id_cliente' WHERE enviado_desde='$s_origen'";
    query($sql);

    gir_ActualizacionEnCascada($id_cliente,$origen);
}





/*
 * Entiende de reglas aplicadas a una comunicacion.
*/
class RuleMaker extends Cursor {

    var $comm;



    /*
     * Selecciona una regla
    */
    function setCom( $communication) {
        $this->comm = $communication;
    }


    /*
     * Dispara los efectos que corresponde a la regla activada
    */
    function AplicarRegla($datosRegla) {

        //1) Se añade esta etiqueta a la comunicacion

        $label = new Etiqueta();

        if( $label->Load($datosRegla["id_label"]) ) {
            $label->createLink($this->comm->get("id_comm"));
        }

        if ( $datosRegla["id_contact"]) {
            $id = $datosRegla["id_contact"];
            $this->comm->set("id_contact",$id);

            //actualizamos este puntualmente
            $id_comm = $this->comm->get("id_comm");
            $sql = "UPDATE communications SET id_contact='$id' WHERE id_comm='$id_comm'";
            query($sql);
        }
    }

    /*
     * Corre todas las reglas para la comunicacion seleccionada
    */
    function RunRules() {
        /*
		$sql = "SELECT * FROM eac WHERE ".
			"eac_from LIKE '%$from_s%' OR ".
			" eac_to LIKE '%$to_s%' OR ".
			" eac_title LIKE '%$title_s%'   ".
			" eac_content LIKE '%$content_s%'" ;*/

        $sql = "SELECT * FROM eac ORDER BY id_eac DESC";

        $res = query($sql);

        while($row = Row($res)) {
            $cumpleReglas			= 0;
            $fallosCoincidencia		= 0;

            if ( strstr($this->comm->get("from_to"),$row["eac_from"] ) ) {
                $cumpleReglas++;
            } else if ( $row["eac_from"]) {
                $fallosCoincidencia++;
                continue; //esta regla ya no va a coincidir porque falla una de las condiciones
            }

            if ( strstr($this->comm->get("from_to"),$row["eac_to"] ) ) {
                $cumpleReglas++;
            } else if ( $row["eac_to"] ) {
                $fallosCoincidencia++;
                continue;
            }

            if ( strstr($this->comm->get("title"),$row["eac_title"] ) ) {
                $cumpleReglas++;
            } else if ($row["eac_title"]) {
                $fallosCoincidencia++;
                continue;
            }

            if ($cumpleReglas and !$fallosCoincidencia ) {
                $this->AplicarRegla($row);
            }
        }
    }
}





class FlexMaker extends Cursor {

    var $comm;


    /*
     * Selecciona una regla
    */
    function setCom( $communication) {
        $this->comm = $communication;
    }

        /*
     * Corre todas las reglas para la comunicacion seleccionada
    */
    function RunRulesDebug() {

        $cr = "<br>\n";

        $rules = array();

        $sql = "SELECT * FROM eac_data WHERE istrigger=0 ORDER BY eac_id DESC";

        $res = query($sql);

        while($row = Row($res)) {
            $cumpleReglas		= 0;
            $fallosCoincidencia	= 0;


            $id_eac = $row["eac_id"];
            $row["id_eac"] = $id_eac;//hack.
            $idioma = $row["data"];
            
            if(isset($rules[$id_eac])){
                if ($rules[$id_eac]=="falla") {

                    echo "salta:$id_eac" .$cr;
                    continue;
                }
            }

            $rule = $row["rule"];

            switch($rule) {
                case "eac_to":
                case "eac_from":
                    if ( $this->comm->get("from_to")!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("from_to")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("from_to")."=". $row["data"].$cr;
                    }
                    
                    if ( strpos($this->comm->get("from_to"),$row["data"] )=== false) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                case "eac_title":
                    if ( $this->comm->get("title")!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("title")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("title")."==". $row["data"].$cr;
                    }
                    break;
                case "eac_label_cat":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 6";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("title")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("title")."==". $row["data"].$cr;
                    }
                    break;
                    case "eac_label_dl":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 90";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("title")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("title")."==". $row["data"].$cr;
                    }
                    break;
                    
                case "eac_label_en":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 7";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("title")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("title")."==". $row["data"].$cr;
                    }
                    break;
                    
                case "eac_label_es":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 5";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                        echo "fallando:".$this->comm->get("title")."!=". $row["data"].$cr;
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                        echo "acierta:".$this->comm->get("title")."==". $row["data"].$cr;
                    }
                    break;
                default:
                //not implemented?
                    $rules[$id_eac] = "falla";
                    echo "ERROR: $rule no inventada". $cr;
                    break;
            }

        }

        foreach( $rules as $id_eac=>$status) {
            $comp2 =  $status == "1" or $status==true;

            if ($comp2) {
                $res = query("SELECT * FROM eac_data WHERE istrigger=1 AND (eac_id=$id_eac) ORDER BY eac_id DESC");

                while($row = Row($res)) {

    //			id_filter 	istrigger 	data 	rule				eac_id 	hinttext
    //			162			1			2		cambiadelegacion 	13
    //			161		  	0					eac_contact			13

                    $rule = $row["rule"];

                    switch($rule) {
                        case "cambiadelegacion":
                            //$this->triggerCambiaDelegacion($row);

                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break;
                        case "cambiacliente":
                        case "cambiacontacto":
                            //$this->triggerCambiaContacto($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break;
                        case "aplicalabel":
                            //$this->triggerAplicaLabel($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break;
                        case "aplicatask":
                            //$this->triggerAplicaTask($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break;
                       case "asignausuariocat":
                            $this->triggerAsignaUsuario($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break; 
                       case "asignausuariodl":
                            $this->triggerAsignaUsuario($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break; 
                       case "asignausuarioen":
                            $this->triggerAsignaUsuario($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break; 
                       case "asignausuarioes":
                            $this->triggerAsignaUsuario($row);
                            echo "aplicando regla:" . $rule . ", row:".var_export($row,true) . $cr;
                            break; 
                       
                        default:
                            echo "no conoce regla $rule ,row:".var_export($row,true) . $cr;
                            break;
                    }
                } //while row
            }//comp2
        }//foreach rules
    } // runrules



    function genRules(){
        global $global_rules_eac;

        //error_log(__LINE__ . ": carga rules...");
        
        if(!$global_rules_eac ){
            $global_rules_eac = array();
            $sql = "SELECT * FROM eac_data WHERE istrigger=0 ORDER BY eac_id ASC";

            $res = query($sql);

            while($row = Row($res)) {
                array_push($global_rules_eac,$row);
            }
        }

        //return $global_rules_eac ;
    }

    function genRulesSelected($id_eac){
        global $global_rules_eac_sel;

        //error_log(__LINE__ . ": carga rules...");
        
        if(!$global_rules_eac_sel ){
            $global_rules_eac_sel = array();
            $sql = "SELECT * FROM eac_data WHERE istrigger=0 and eac_id='$id_eac' ORDER BY eac_id ASC";

            $res = query($sql);

            while($row = Row($res)) {
                array_push($global_rules_eac_sel,$row);
            }
        }

        //return $global_rules_eac ;
    }


    /*
     * Corre todas las reglas para la comunicacion seleccionada
    */
    function RunRules_Simple_Selected($id_eac) { // solo soporta 1 trigger
        global $global_rules_eac_sel;

        $rules = array();

        $this->genRulesSelected($id_eac);

        $comm_from_to = $this->comm->get("from_to");

        foreach($global_rules_eac_sel as $row) {
            $cumpleReglas		= 0;
            $fallosCoincidencia	= 0;


            $id_eac = $row["eac_id"];
            $row["id_eac"] = $id_eac;//hack.

            if(isset($rules[$id_eac])){
                if ($rules[$id_eac]=="falla") {
                    continue;
                }
            }

            $rule = $row["rule"];

            switch($rule) {
                case "eac_to":
                case "eac_from":
                    if ( $comm_from_to!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    if ( strpos($this->comm->get("from_to"),$row["data"] )=== false) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    
                    break;
                case "eac_title":
                    if ( $this->comm->get("title")!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                default:
                    $rules[$id_eac] = "falla";
                //not implemented?
                    break;
            }

        }

        foreach( $rules as $id_eac_value=>$status) {
            $comp2 =  $status == "1" or $status==true;

            if ($comp2) {
                $row = getTriggerData($id_eac_value);
                $rule = $row["rule"];

                switch($rule) {
                    case "cambiadelegacion":
                        $this->triggerCambiaDelegacion($row);
                        break;
                    case "cambiacliente":
                    case "cambiacontacto":
                        $this->triggerCambiaContacto($row);
                        break;
                    case "aplicalabel":
                        $this->triggerAplicaLabel($row);
                        break;
                    case "aplicatask":
                        $this->triggerAplicaTask($row);
                        break;
                }
            }//comp2
        }//foreach rules
    } // runrules

    
    
    
    
    /*
     * Corre todas las reglas para la comunicacion seleccionada
    */
    function RunRules_Simple() { // solo soporta 1 trigger
        global $global_rules_eac;

        $rules = array();

        $this->genRules();

        $comm_from_to = $this->comm->get("from_to");

        foreach($global_rules_eac as $row) {
            $cumpleReglas		= 0;
            $fallosCoincidencia	= 0;


            $id_eac = $row["eac_id"];
            $row["id_eac"] = $id_eac;//hack.

            if(isset($rules[$id_eac])){
                if ($rules[$id_eac]=="falla") {
                    continue;
                }
            }

            $rule = $row["rule"];

            switch($rule) {
                case "eac_to":
                case "eac_from":
                    if ( $comm_from_to!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    
                    if ( strpos($this->comm->get("from_to"),$row["data"] )=== false) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                case "eac_title":
                    if ( $this->comm->get("title")!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                default:
                    $rules[$id_eac] = "falla";
                //not implemented?
                    break;
            }

        }

        foreach( $rules as $id_eac=>$status) {
            $comp2 =  $status == "1" or $status==true;

            if ($comp2) {
                $row = getTriggerData($id_eac);
                $rule = $row["rule"];

                switch($rule) {
                    case "cambiadelegacion":
                        $this->triggerCambiaDelegacion($row);
                        break;
                    case "cambiacliente":
                    case "cambiacontacto":
                        $this->triggerCambiaContacto($row);
                        break;
                    case "aplicalabel":
                        $this->triggerAplicaLabel($row);
                        break;
                    case "aplicatask":
                        $this->triggerAplicaTask($row);
                        break;
                }
            }//comp2
        }//foreach rules
    } // runrules
    
    /*
     * Corre todas las reglas para la comunicacion seleccionada
    */
    function RunRules() {
        global $global_rules_eac;

        $rules = array();

        $this->genRules();

        $comm_from_to = $this->comm->get("from_to");

        foreach($global_rules_eac as $row) {
            $cumpleReglas		= 0;
            $fallosCoincidencia	= 0;


            $id_eac = $row["eac_id"];
            $row["id_eac"] = $id_eac;//hack.

            if(isset($rules[$id_eac])){
                if ($rules[$id_eac]=="falla") {
                    continue;
                }
            }

            $rule = $row["rule"];

            switch($rule) {
                case "eac_to":
                case "eac_from":
                    if ( $comm_from_to!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    
                    if ( strpos($this->comm->get("from_to"),$row["data"] )=== false) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                case "eac_title":
                    if ( $this->comm->get("title")!=$row["data"] ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                case "eac_label_cat":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 6";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                    
                case "eac_label_dl":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 90";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                    
                case "eac_label_en":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 7";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                    
                case "eac_label_es":
                    $sql = "SELECT * FROM label_coms WHERE id_comm =". $this->comm->get("id_comm"). " And id_label = 5";
                    
                    $rowIdioma = queryrow($sql);
                    
                    if ( !$rowIdioma ) {
                        $rules[$id_eac] = "falla";
                    } else {
                        $rules[$id_eac]	= true;//continuara chequeando, y esta queda marcada para ejecucion
                    }
                    break;
                default:
                    $rules[$id_eac] = "falla";
                //not implemented?
                    break;
            }

        }

        foreach( $rules as $id_eac=>$status) {
            $comp2 =  $status == "1" or $status==true;

            if ($comp2) {
                $res = query("SELECT * FROM eac_data WHERE istrigger=1 AND (eac_id=$id_eac) ORDER BY eac_id ASC");

                while($row = Row($res)) {

    //			id_filter 	istrigger 	data 	rule				eac_id 	hinttext
    //			162			1			2		cambiadelegacion 	13
    //			161		  	0					eac_contact			13

                    $rule = $row["rule"];

                    switch($rule) {
                        case "cambiadelegacion":
                            $this->triggerCambiaDelegacion($row);
                            break;
                        case "cambiacliente":
                        case "cambiacontacto":
                            $this->triggerCambiaContacto($row);
                            break;
                        case "aplicalabel":
                            $this->triggerAplicaLabel($row);
                            break;
                        case "aplicatask":
                            $this->triggerAplicaTask($row);
                            break;
                        case "asignausuario":
                            $this->triggerAsignaUsuario($row);
                            break;
                        
                    }
                } //while row
            }//comp2
        }//foreach rules
    } // runrules


    function triggerCambiaLocation($dataRule){
        $id = $dataRule["data"];

        if($id<=0){
            $id = 1;
            error_log("ERROR: triggerCambiaLocation: quiso poner id_location=0");
        }

        if(!$id) return;//antierrores

        //TODO? detectar un posible id_contacto erroneo aqui?

            $id_comm = $this->comm->get("id_comm");
            $title = $this->comm->get("title");
            registra_gir("Mensaje: ($title) id_comm($id_comm) delegacion($id), eac:".$dataRule["eac_id"]);
           
            // Populo nombre delegación
                  
               
            $delegacion = new Lugar();
            $delegacion->Load($id);
            
            $nombreDelegacion = $delegacion->getNombre();
            
            // Inserto en asignaciones_gir
            $id_user_logueado = getSesionDato("id_usuario_logueado");
            cargaDatosAsignacion($dataRule["eac_id"] , $id_user_logueado , "Cambio delegacion a $nombreDelegacion", $id_comm);
            

        $this->comm->set("id_location",$id);
        $this->comm->Modificacion();
    }


    function triggerCambiaDelegacion($dataRule) {
        //$this->triggerAplicaLabel($dataRule);
        $this->triggerCambiaLocation($dataRule);
    }

    function triggerAplicaLabel($dataRule) {

        return;//desactivado 
        //En data estara un id_label
        if( $label->Load($dataRule["data"]) ) {
            $label->createLink($this->comm->get("id_comm"));
        }
    }

    function triggerCambiaContacto($dataRule) {
        $id_contacto = $dataRule["data"];

        if(!$id_contacto) return;//protege anti errores        
        
        $row = queryrow("SELECT id_contact FROM contacts WHERE id_contact='$id_contacto' AND eliminado=0 LIMIT 1");

        if($row){
            $id = $this->comm->get("id_comm");
            $title = $this->comm->get("title");
            registra_gir("Mensaje: ($title) id_comm($id) contacto($id_contacto), eac:".$dataRule["eac_id"]);

            //Populo nombre contacto
            
            
            $contacto = new Contacto();
            
            $nombreContacto = $contacto->getNombreFromId($id_contacto);
            
            // Inserto en asignaciones_gir
            cargaDatosAsignacion($dataRule["eac_id"], getSesionDato("id_usuario_logueado"), "Cambio contacto a $nombreContacto", $id);
                                                 
            
            $this->comm->set("id_contact",$id_contacto);
            $this->comm->Modificacion();
        }
    }

    function triggerAplicaTask($dataRule) {
        $id_task = $dataRule["id_task"];

        if(!$id_task) $id_task = 14;//si no hay task, le da una

            $id = $this->comm->get("id_comm");
            $title = $this->comm->get("title");
            registra_gir("Mensaje: ($title) id_comm($id) canal($id_task), eac:".$dataRule["eac_id"]);

            // Inserto en asignaciones_gir
            cargaDatosAsignacion($dataRule["eac_id"], getSesionDato("id_usuario_logueado"), "Aplica tarea ($id_task)", $id);
                                                      
            
        $this->comm->set("id_task",$id_task);
        $this->comm->Modificacion();
    }
    
    function triggerAsignaUsuario($dataRule) {
        $id_usuario = $dataRule["data"];
        
        
        
        if(!$id_usuario) return;//protege anti errores        
        
        $row = queryrow("SELECT id_user FROM users WHERE id_user='$id_usuario' AND deleted='0' LIMIT 1");

        
        if($row){
            
            
            
            $id = $this->comm->get("id_comm");
            $title = $this->comm->get("title");
            registra_gir("Mensaje: ($title) id_comm($id) usuario($id_usuario), eac:".$dataRule["eac_id"]);

            //Populo nombre Usuario
                        
            $usuario = new Usuario();
            
            $usuario->Load($id_usuario);
            
            $nombreUsuario = $usuario->getNombreCompletoBreve();
            
            // Inserto en asignaciones_gir
            cargaDatosAsignacion($dataRule["eac_id"], getSesionDato("id_usuario_logueado"), "Cambio usuario a $nombreUsuario", $id);
                                                 
            $sql = "SELECT * FROM colas WHERE id_comm = $id";
            $res = queryrow($sql);
            
            if($res){
                $sql = "UPDATE colas SET id_user = $id_usuario WHERE id_comm = $id";
                query($sql);
            }
            else {
                $sql = "INSERT INTO colas (id_comm, id_user, puntos, pref_location) VALUES ($id, $id_usuario, 1, 1)";
                query($sql);
            }
            
        }
        
        
    }
}




class Regla extends Cursor {


    var $_nameid			= "id_eac";
    var $_nombretabla               = "eac";


    function Usuario() {
        return $this;
    }

    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("eac", "id_eac", $id);
        return $this->getResult();
    }

    function setNombre($nombre) {

    }

    function getNombre() {
        return $this->get("eac");
    }

    function Crea() {
        $this->setNombre(_("Nueva regla"));
    }


    function getContactoName() {

        $id_contact = $this->get("id_contact");

        $sql = "SELECT contact_name as dato FROM contacts WHERE (id_contact='$id_contact')";

        $row = queryrow($sql);
        return $row["dato"];
    }

    function Alta() {
        global $UltimaInsercion;

        $data = $this->export();

        $coma = false;
        $listaKeys = "";
        $listaValues = "";

        foreach ($data as $key=>$value) {
            if ($coma) {
                $listaKeys .= ", ";
                $listaValues .= ", ";
            }

            $value = sql($value);

            $listaKeys .= " `$key`";
            $listaValues .= " '$value'";
            $coma = true;
        }

        $sql = "INSERT INTO eac ( $listaKeys ) VALUES ( $listaValues )";

        $resultado = query($sql);

        if ($resultado) {
            $this->setId($UltimaInsercion);
        }

        return $resultado;
    }


    function Modificacion () {
        return $this->Save();
    }


}



class Filtro extends Cursor {
    var $_nameid			= "id_eac";
    var $_nombretabla               = "eac_filters";


    public function gir_AsignacionParcialOrigen($id_cliente,$origen) {
        include_once("class/contacts.class.php");

        if(strlen($origen)<5) return;//no se admiten reglas muy cortas

        Filtro::gir_eliminarFiltrosIdCliente($origen);

        $regla = new Filtro();
        $nombre = Contacto::getNombreFromId($id_cliente);

        $regla->set("id_user",getSesionDato("id_usuario_logueado"));
        $regla->crearNueva("filtro: $origen, contacto:$nombre");
        $regla->nuevoFiltro($origen,"eac_from");
        $regla->nuevoTrigger($id_cliente,'cambiacliente');

        $id_eac = $regla->get("id_eac");        
        
        Filtro::gir_correarFiltrosPara($origen,$id_eac);
    }

    public function getDataTrigger($id_eac){



    }
    
    public function gir_eliminarFiltrosIdCliente($origen){
        
        if(!$origen) return;
        if(strlen($origen)<5)return;

        $origen_s =sql($origen);
        $sql = "SELECT * FROM eac_data WHERE rule='eac_from' and data like '$origen_s'  ";

        $res = query($sql);
        while($row=Row($res)){
                $id_eac =$row["eac_id"];
                if($id_eac){
                    $data = queryrow("SELECT * FROM eac_data WHERE eac_id='$id_eac' and istrigger=1 and rule='cambiacliente' LIMIT 1");
                    if ($data){
                            //var_export($data);
                            $sql = "DELETE FROM eac_data WHERE eac_id='$id_eac' ";
                            query($sql);
                            $sql = "DELETE FROM eac_filters WHERE id_eac='$id_eac' ";
                            query($sql);
                    }
                }
        }	                
    }

    public function gir_eliminarFiltrosDelegacion($origen){
        
        if(!$origen) return;
        if(strlen($origen)<5)return;
        
        $origen_s =sql($origen);
        $sql = "SELECT * FROM eac_data WHERE rule='eac_from' and data like '$origen_s'  ";




        $res = query($sql);
        while($row=Row($res)){
                $id_eac =$row["eac_id"];
                $data = queryrow("SELECT * FROM eac_data WHERE eac_id='$id_eac' and istrigger=1 and rule='cambiadelegacion' ");
                if ($data){	
                        //var_export($data);
                        $sql = "DELETE FROM eac_data WHERE eac_id='$id_eac' ";
                        query($sql);
                        $sql = "DELETE FROM eac_filters WHERE id_eac='$id_eac' ";
                        query($sql);	
                }	
        }	                
    }

    public function gir_correarFiltrosPara($origen,$id_eac=false){
        $flex = new FlexMaker();
        $comm = new Comunicacion();
        $id_desconocido = getIdUserDesconocido();

        if(!$id_desconocido) return;
        if(!$origen or strlen($origen)<5 ) return;//antierrores

        $origen_s =sql($origen);

        $sql = "SELECT id_comm FROM communications WHERE from_to LIKE '$origen_s'  ";

        $res = query($sql);

        while($row = Row($res)){

            $id = $row["id_comm"];

            if($comm->Load($id)){
                $flex->setCom( $comm );
                if($id_eac)
                    $flex->RunRules_Simple_Selected($id_eac);
                else
                    $flex->RunRules();
                //echo "..$id..";
            }
        }        
    }

    public function gir_AsignacionParcialDelegacion($id_cliente,$origen,$id_delegacion) {

        set_time_limit (0);//run script forever
        ignore_user_abort(TRUE);//run script in background

        Filtro::gir_eliminarFiltrosDelegacion($origen);
        
        $regla = new Filtro();
 
        $regla->set("id_user",getSesionDato("id_usuario_logueado"));
        $regla->crearNueva("filtro: $origen, delegacion:$id_delegacion");
        $regla->nuevoFiltro($origen,"eac_from");
        $regla->nuevoTrigger($id_delegacion,'cambiadelegacion');

        $id_eac = $regla->get("id_eac");  
        
        Filtro::gir_correarFiltrosPara($origen,$id_eac);
    }

    //Filtro::gir_AsignacionParcialDelegacion($id_cliente,$enviado_desde,$id_delegacion);

    function crearNueva($nombre){
        $this->set("name",$nombre);

        $this->Alta();
    }


    function nuevoTrigger($data,$tipo){
        global $UltimaInsercion;

        $tipo_s = sql($tipo);
        $eac_filter_id_s = $this->get("id_eac");
        $data_s = sql($data);

        $sql = "INSERT INTO eac_data (rule,data,eac_id,istrigger,hinttext) values
          ( '$tipo_s','$data_s', '$eac_filter_id_s',1,'' )";
        query($sql);

        return $UltimaInsercion;
    }

    function nuevoFiltro($data,$tipo){
        global $UltimaInsercion;

        $tipo_s = sql($tipo);
        $eac_filter_id_s = $this->get("id_eac");
        $data_s = sql($data);

        $sql = "INSERT INTO eac_data (rule,data,eac_id,istrigger) values
          ( '$tipo_s','$data_s', '$eac_filter_id_s',0 )";
        query($sql);

        return $UltimaInsercion;
    }




    function Usuario() {
        return $this;
    }

    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("eac_filters", "id_eac", $id);
        return $this->getResult();
    }

    function setNombre($nombre) {

    }

    function getNombre() {
        return $this->get("name");
    }

    function Crea() {
        $this->setNombre(_("Nueva regla"));
    }


    function getContactoName() {
        //OBSOLETO
        return "";
    }

    function Alta() {
        global $UltimaInsercion;

        $data = $this->export();

        $coma = false;
        $listaKeys = "";
        $listaValues = "";

        foreach ($data as $key=>$value) {
            if ($coma) {
                $listaKeys .= ", ";
                $listaValues .= ", ";
            }

            $value_s = sql($value);

            $listaKeys .= " `$key`";
            $listaValues .= " '$value_s'";
            $coma = true;
        }

        $sql = "INSERT INTO eac_filters ( $listaKeys ) VALUES ( $listaValues )";

        $resultado = query($sql);

        if ($resultado) {
            $this->setId($UltimaInsercion);
        }

        return $resultado;
    }


    function Modificacion () {
        return $this->Save();
    }
}

