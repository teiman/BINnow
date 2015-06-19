<?php


class ProcesoTabla {
  var $tablaOrigen;
  var $tablaDestino;
  var $idProceso;
  var $_msg = array();
  var $_hook = array();
  var $_trace = true;

  function log($msg){
	$msg =  time() .": ". $msg;

        $this->_msg[] = $msg;

        if($this->_trace){
            echo $msg . "\n";
        }
  }

  function trace($modo){
      $this->_trace = $modo;
  }

  function Load($id_proceso){
    $datosproceso = queryrow("SELECT * FROM php_procesos WHERE id_phproceso=$id_proceso");

    $this->idProceso = $id_proceso;
    $this->tablaOrigen = $datosproceso["table_org"];
    $this->tablaDestino = $datosproceso["table_dst"];

    $this->_hook = array();

    $this->CargaApi();
  }

  function CargaApi(){
    $id_phproceso = $this->idProceso;
    $sql = "SELECT * FROM php_procesos_data WHERE id_phproceso=$id_phproceso";
    $res= query($sql);

    $this->log("Cargando api");
    while($row=Row($res)){
        $hook = trim($row["hookname"]);
        $code = $row['phpcode'];
        $this->_hook[$hook] = eval($code);

        $this->log("hook:$hook, func:{$code}");
    }
    $this->log("Api cargada");
  }

  

  function debug_runner($name,$data=false){

    $ret = false;

    $func = $this->_hook[$name];

    if(!is_callable($func)){
        $this->log("ERROR: Se intentando invocar '$name', no existe");
        return;
    }

    $this->log("--$name--");
    try {
       $ret = $func($this,$data);
    } catch(Exception $e){
        $this->log("EXCEPTION: Se produjo una excepcion intentando invocar $name");
    }
    $this->log("--/$name--");

    return $ret;
   }

   function Recorre($data){
        $this->debug_runner("hook_start");
        foreach($data as $key){
                $this->debug_runner("hook_data",$key);
        }
        $this->debug_runner("hook_end");
    }

    function lastmessage(){
        return $this->_msg[count($this->_msg)-1];
    }

    /*
     * Cambia la definicion de un hook, o lo crea si no existe
     */
    function define($hook_name,$definicion){

        $func = eval($definicion);
        $this->_hook[$hook_name] = $func;
        if(!is_callable($func)){
            $this->log("ERROR: se intento definir una funcion mal construida: $definicion");
            return false;
        }

        $this->log("INFO: Nueva definicion: [$hook_name] => $definicion");

        $definicion_s = sql($definicion);
        $hook_name_s = sql($hook_name);
        $id_phproceso = $this->idProceso;
        $sql = "SELECT * FROM php_procesos_data WHERE id_phproceso=$id_phproceso AND hookname='$hook_name_s'";
        $row= queryrow($sql);
        $yaExiste = ($row);

        if ($yaExiste){ //si ya existe, lo actualizaremos en lugar de insertar uno nuevo
            $id_procesos_data = $row["id_procesos_data"];
            $sql = "UPDATE php_procesos_data SET phpcode='$definicion_s' WHERE id_procesos_data='$id_procesos_data'";
            query($sql);
            $this->log("INFO: $hook_name actualizado");
        } else {
            $sql = "insert into php_procesos_data (phpcode,hookname,id_phproceso) values('$definicion_s','$hook_name_s','$id_phproceso')";
            query($sql);
            $this->log("INFO: $hook_name creado");
        }

        return true;
    }



    function installTable($nombre,$ddl){
        @query("DROP TABLE IF EXISTS $nombre");//DROP TABLE IF EXISTS  tabla
        query($ddl);
    }
}


function testexternal(){

}