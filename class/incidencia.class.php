<?php


/*
 *  requiere include("class/comunicacion.class.php");
 * 
*/


class Ficha extends Cursor {
    var $_nameid = "id_comm";
    var $_nombretabla  = "ficha_incidencias";


    function Load($id) {
        $id = CleanID($id);
        $this->setId($id);
        $this->LoadTable("ficha_incidencias", "id_comm", $id);
        return $this->getResult();
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

        $sql = "INSERT INTO ficha_incidencias  ( $listaKeys ) VALUES ( $listaValues )";

        $resultado = query($sql);

        return $resultado;
    }

    function Modificacion () {
        return $this->Save();
    }

}



class Incidencia extends Comunicacion {


    function nuevoAdjunto($fichero,$nombre="",$nombre_original=""){
        global $UltimaInsercion;
        
        include_once("../inc/gestordocumental.php");
        
        $id_comm_s = $this->get("id_comm");
        $path_s = sql( $fichero );
        $nombre_s = sql( $nombre );

        $nombre_original_s = sql($nombre_original);

        $sql = "INSERT ficha_incidencia_ficheros (id_comm,path_fichero,titulo,fichero_original) VALUES ('$id_comm_s','$path_s','$nombre_s','$nombre_original_s')";

        $res = query($sql);
        
        if($res){
            $id_adjunto = $UltimaInsercion;
            
            gestorficherosAdjuntosIncidencias::registrar($id_adjunto);                
        }
        
        return $res;
        
    }

    function getFicha(){
        $ficha = new Ficha();
        
        $ficha->Load($this->get("id_comm"));        
        
        return $ficha;
    }

    function getObservaciones(){

        $id_comm_s = sql($this->get("id_comm"));

        $sql = "SELECT * FROM ficha_incidencias_observ JOIN users ON ficha_incidencias_observ.id_user=users.id_user WHERE id_comm='$id_comm_s' ORDER BY ficha_incidencias_observ.fecha DESC";
        
        $res = query($sql);
        
        while($row = Row($res)){
            //$row["observacion"] .= "Añadido por ". html($row["name"]) . "</i>";

            $text = md5($row["id_user"]);
            $row["color"] = "#F". $text[0]. "E" . $text[1] . "A" . $text[2];
            $data[] = $row;            
        }
        return $data;
    }


    function guardaObservacion($observacion){

        $observacion = trim($observacion);

        if(!$observacion) return;

        //error_log("guarda observa:$observacion");

        $id_comm_s = sql($this->get("id_comm"));
        $id_user_s = intval(getSesionDato("id_usuario_logueado"));
        $observacion_s = sql($observacion);

        $sql = "INSERT INTO ficha_incidencias_observ (id_comm,id_user,observacion,fecha) VALUES "
            . " ('$id_comm_s','$id_user_s','$observacion_s',NOW()) ";

        query($sql);

        error_log("guarda observa.sql:$sql");


        /*
            CREATE TABLE `ficha_incidencias_observ` (
              `id_comm` int(10) unsigned NOT NULL,
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `observacion` text NOT NULL,
              `id_user` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `id_comm` (`id_comm`)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
         */

    }


    function tieneFicha() {
        $id_comm = $this->get("id_comm");
        $sql = "SELECT count(*) as c FROM ficha_incidencias WHERE id_comm='$id_comm' ";
        $row = queryrow($sql);

        //error_log("sql:$sql");
        //error_log("tiene ficha:".($row['c']>0));
        return $row["c"]>0;
    }

    
    
    /*
     * La incidencia es nueva, y algunos campos deben estar prerellenos
     * se crea un array con estos datos como si vinieran de la bd.
     */
    function prepopularIncidencia(){
        $row = array();



/*
 d_comm	int(10)		UNSIGNED	No		auto_increment
	n_de_albaran	varchar(20)	latin1_swedish_ci		No
	n_de_incidencia	varchar(20)	latin1_swedish_ci		No
	id_user_abre	int(11)			No
	fecha_abre	date			No
	cod_cliente	varchar(20)	latin1_swedish_ci		No
	nombre_cliente	varchar(80)	latin1_swedish_ci		No
	telefono_cliente	varchar(30)	latin1_swedish_ci		No
	nombre_contacto	varchar(80)	latin1_swedish_ci		No
	resolucion	text	latin1_swedish_ci		No
	accion_marcar	tinyint(3)		UNSIGNED	No	0
	accion_regularizar	tinyint(3)		UNSIGNED	No	0
	accion_recoger	tinyint(3)
 *
id_contact	int(10)		UNSIGNED	No		auto_increment
	id_contact_other	tinytext	latin1_swedish_ci		No
	contact_name	tinytext	latin1_swedish_ci		No
	contact_code	varchar(30)	latin1_swedish_ci		No
	priority	varchar(10)	latin1_swedish_ci		No
	contact_unknown	enum('0', '1')	latin1_swedish_ci		No	0
	eliminado
 */

        $row["id_comm"] = $this->get("id_comm");


        $id_contact = $this->get("id_contact");
        $id_contact_s = sql($id_contact);

        $sql = "SELECT * FROM contacts WHERE id_contact='$id_contact_s' ";
        $datoscontacto = queryrow($sql);

        $row["nombre_cliente"] = $datoscontacto["contact_name"];
        $row["cod_cliente"] = $datoscontacto["contact_code"];
        $row["telefono_cliente"] = "";//TODO


        return $row;
    }



    function exportIncidencia() {
        $id_comm = $this->get("id_comm");
        $sql = "SELECT * FROM ficha_incidencias WHERE id_comm='$id_comm' ";
        $row = queryrow($sql);

        if (!$row){
            //error_log("exportIncidencia; no existe la ficha, asi que devolvemos datos prepopulados");
            $row = $this->prepopularIncidencia();
        }

        return $row;
    }

    function filtraLineaParaTipo(&$row,$filtroLineaLogistica=false,$filtroLineaDelegacion=false,$sololectura="",$sololecturarr=""){

            /*   $otros["sololecturaficha"] = "sololectura";
            $otros["sololecturarrbotones"] = " readonly='readonly' ";*/

            $filtroLineaAdmin = estaHabilitado("modincidencias/filtroAdmin",false);

            if($filtroLineaLogistica){
                //$row["optarticulo"] = "oculto2";
                //$row["optenviados"] = "oculto2";
                //$row["optrecibidos"] = "oculto2";
                //$row["opttipo"] = "oculto2";
            }
            if($filtroLineaDelegacion){
                $row["optresponsable"] = "oculto2";
                $row["opterror"] = "oculto2";
                $row["calidadrw"] = " readonly='readonly' ";

                $row["calidadother"] = "oculto2";
            }

            $id_status = $this->get("id_status");
            
            if($id_status!= getParametro("binow.id_status_recibido")){
                $row["optborrar"] = "oculto2";
            }

            if($id_status == getParametro("b.incidencia_cerrada")){
                $sololecturaerrcalinc = "sololectura";
                $sololecturaerrcalincr = " readonly='readonly' ";
                $row["tipooculto"] = "oculto";
                $row["sololecturaresponsable"] = "readonly=readonly";
                $row["sololecturaresponsablecss"] = "sololectura";
            }

            if(!$filtroLineaAdmin){
                $row["sololectura2"] = $sololectura;
                $row["sololecturarr2"] = $sololecturarr;
            }

            if(!$filtroLineaAdmin){
                $row["sololecturaerrcalinc2"] = $sololecturaerrcalinc;
                $row["sololecturaerrcalincr2"] = $sololecturaerrcalincr;
            }


            if($id_status == getParametro("binow.id_status_pendientegestor") and $filtroLineaLogistica){
                $row["sololecturaficha"] = "sololectura";
                $row["sololecturarrbotones"] = " readonly='readonly' "; 
                $row["cssocultarbotonguardar"] = "oculto";
                $row["cssocultarbotonexaminar"] = "oculto";

                $row["sololecturaerrcalinc2"] = "sololectura";
                $row["sololecturaerrcalincr2"] = " readonly='readonly' ";

                error_log("ESTO DEBERIA SER READONLY-1");
            } else {
                error_log("RW mode: filtroLineaLogistica:". $filtroLineaLogistica.",id_status:$id_status". ",getParametro(binow.id_status_pendientegestor):".getParametro("binow.id_status_pendientegestor"));
            }

            if($id_status == getParametro("binow.id_status_pendientelogistica") and $filtroLineaDelegacion){
                $row["sololecturaficha"] = "sololectura";
                $row["sololecturarrbotones"] = " readonly='readonly' "; 
                $row["cssocultarbotonguardar"] = "oculto";
                $row["cssocultarbotonexaminar"] = "oculto";

                //$row["sololecturaerrcalinc2"] = "sololectura";
                //$row["sololecturaerrcalincr2"] = " readonly='readonly' ";

                error_log("ESTO DEBERIA SER READONLY-2");
            } else {
                error_log("RW mode: filtroLineaDelegacion:". $filtroLineaDelegacion.",id_status:$id_status". ",getParametro(binow.id_status_pendientelogistica):".getParametro("binow.id_status_pendientelogistica"));
            }

            if($id_status == getParametro("binow.id_status_pendientegestor")  and !$filtroLineaAdmin){
                $row["sololecturaerrcalinc2"] = "sololectura";
                $row["sololecturaerrcalincr2"] = " readonly='readonly' ";
            }


            if($id_status == getParametro("binow.id_status_pendientelogistica")  and $filtroLineaDelegacion){
                $row["sololecturaerrcalinc2"] = "sololectura";
                $row["sololecturaerrcalincr2"] = " readonly='readonly' ";
            }

            if($id_status == getParametro("binow.id_status_pendientegestor")  and $filtroLineaLogistica){
                $row["sololecturaerrcalinc2"] = "sololectura";
                $row["sololecturaerrcalincr2"] = " readonly='readonly' ";


                $row["cssresponsable"] = "sololectura";
                $row["rrresponsable"] = " readonly=readonly ";
            }

            if($id_status == getParametro("binow.id_status_pendientelogistica")  and $filtroLineaLogistica){
                //$row["sololecturaerrcalinc2"] = "sololectura";
                //$row["sololecturaerrcalincr2"] = " readonly='readonly' ";


                $row["sololecturaerrcalinc2"] = "fuerzagris";
                //SOLOLECTURAERRCALINC2

                $row["tipooculto"] = "oculto";
                $row["tipooculto"] = "oculto";


                //{CSSRESPONSABLE}"  {RRRESPONSABLE}

                $row["cssresponsable"] = "sololectura";
                $row["rrresponsable"] = " readonly='readonly' ";
            }


        if($id_status==getParametro("binow.incidencia_cerrada")  and $filtroLineaLogistica){
            $row['cssresponsable'] = "sololectura";
            $row['rrresponsable'] = " readonly='readonly' ";
        }

        if($id_status==getParametro("binow.incidencias.id_status.abierto")  and $filtroLineaDelegacion){
            $row["tipooculto"] = "oculto";         
        }

        if($id_status==getParametro("binow.id_status_enestudio")  and $filtroLineaDelegacion){
            $row["tipooculto"] = "oculto";
        }

        if($id_status==getParametro("binow.incidencia_cerrada")  and $filtroLineaDelegacion){
            $row['cssresponsable'] = "sololectura";
            $row['rrresponsable'] = " readonly='readonly' ";
            $row["tipooculto"] = "oculto";
        }

        if($id_status==getParametro("binow.incidencia_cerrada")  and $filtroLineaAdmin){
            /*$row['cssresponsable'] = "sololectura";
            $row['rrresponsable'] = " readonly='readonly' ";
            $row["tipooculto"] = "oculto";
                $row["sololecturaerrcalinc2"] = "sololectura";
                $row["sololecturaerrcalincr2"] = " readonly='readonly' ";*/
            
        }

    }


    function getLineas($filtroLineaLogistica=false,$filtroLineaDelegacion=false,$sololectura="",$sololecturarr="", $sololecturaerrcalinc="", $sololecturaerrcalincr=""){

        $id_comm_s = sql($this->get("id_comm"));

        $sql = "SELECT * FROM ficha_incidencias_lineas WHERE (id_comm='$id_comm_s') ORDER BY id asc ";

        $res = query($sql);

        $data = array();
        while($row=Row($res)){
            $this->filtraLineaParaTipo($row,$filtroLineaLogistica,$filtroLineaDelegacion,$sololectura,$sololecturarr, $sololecturaerrcalinc, $sololecturaerrcalincr);

            $data[] = $row;
        }
        
        return $data;
    }

    function GuardarLineas($articulos) {
        $id_comm_s = sql($this->get("id_comm"));

        query("DELETE FROM ficha_incidencias_lineas WHERE id_comm='$id_comm_s'");

        foreach($articulos as $item) {
            $articulo_s = sql($item["articulo"]);
            $enviados_s = sql($item["enviados"]);
            $recibidos_s = sql($item["recibidos"]);
            $defectuosos_s = sql($item["defectuosos"]);
            $error_s = sql($item["error"]);
            $calidad_s = sql($item["calidad"]);
            $tipo_s = sql($item["tipoincidencia"]);
            $comentario_s = sql($item["comentario"]);

            $sql = "INSERT ficha_incidencias_lineas"
                    . " (articulo,enviados,recibidos,defectuosos,error,calidad,tipo,id_comm,comentario)  "
                    . " VALUES "
                    . " ('$articulo_s','$enviados_s','$recibidos_s','$defectuosos_s','$error_s','$calidad_s','$tipo_s','$id_comm_s','$comentario_s') ";
            query($sql);
        }

    }




}


