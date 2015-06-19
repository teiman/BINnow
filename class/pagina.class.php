<?php

/**
 * Template de pagina
 *
 * @package binow
 */

include_once("class/patErrorManager.php");
include_once("class/patTemplate.php");


/*
 * Pagina
 *
 * Template de una pagina de ecomm
 * 
 */
class Pagina extends patTemplate {

	function IniciaTranslate(){
		global $lang;
		global $templatesDir;
		$pagina = 'cadenasdesistema.htm';

		$this->setOption( 'translationFolder', "translations" );
		$this->setOption( 'translationAutoCreate', true );
		$this->setOption( 'lang',  $lang );
		$this->addGlobalVar( 'page_encoding', "utf-8" );

		$this->setRoot( $templatesDir );

		//NOTA: activa el cache
		if (0){
		$this->useTemplateCache( 'File', array(
                                            'cacheFolder' => './templates/cache',
                                            'lifetime'    => 60*60,
                                            'filemode'    => 0644
                                        )
                        );
		}

  
		$this->readTemplatesFromInput($pagina);
	}


	function Inicia($modname,$pagina=false){
		global $page,$lang;//?? porque esto en lugar de $this
                global $templatesDir;
                global $usar_apc_cache;
                                                   
		if (!$pagina)
			$pagina = 'basica.html';

		$page->setOption( 'translationFolder', "translations" );
		$page->setOption( 'translationAutoCreate', true );
		$page->setOption( 'lang',  $lang );
		$page->addGlobalVar( 'page_encoding', "utf-8" );
		$page->setRoot( $templatesDir );

                //error_log("tDir:". $templatesDir);

                if($usar_apc_cache)
                    if(ini_get("apc.enabled")){
                        $this->useTemplateCache( 'apc',array());
                    }


		$loaded = $page->readTemplatesFromInput($pagina);


                if (!$loaded){
                    error_log("ERROR: template '$pagina' no se puede parsear");
                    //TODO: posiblemente abortar el resto de la carga aqui
                } else {
                    //error_log("INFO: template '$pagina' cargo de forma correcta");                   
                }

		$page->addVar('page', 'modname', $modname );
		$page->addVar('headers','versioncss',rand());
		$page->addVar('headers','modname',$modname);


		if ($pagina=="basica.htm"|| $pagina=="central.htm"  || $pagina=="mapa.htm" || $pagina=="mapa.html"
				){
			//TODO: ugly excepcion made here!
			$page->addVar("cabeza","nombreusuario",getSesionDato("user_nombreapellido") );
			$page->addVar("cabeza","id_user",getSesionDato("id_user") );
		}


	}


	function _($text){	
                if(1){ //desactivamos el sistema de traducciones
                    return $text; //
                }

                //--------------
		global $lang;
			
		//if ($lang=="es") $lang = "";		
		
		$folder = "translations";
		//$input = $this->_reader->getCurrentInput();		
		//$input = $this->_reader->_currentInput;						
		
		//$name = $folder . "/" . $input . "-".$lang.".ini";
		$name = "traducciones_" . $lang;
		
		$code = md5($text);
		
		$dato = $_SESSION[$name][$code];
		
		if ($dato)	{ //Nota, que no haya traduccion no es suficiente, puede que este ya en el fichero

			if (1){
				//TODO: desactivar esto en produccion
				$pagina = "templates" . "/" .'cadenasdesistema.txt';

				//Si no estaba, lo añadimos
				$template = file_get_contents($pagina);

				$existe = strstr($template,">".$text."<");

				if($template and !$existe){
					$template = str_replace("</patTemplate:tmpl>","Auto: <patTemplate:Translate>".$text."</patTemplate:Transl</patTemplate:tmpl>",$template);
					file_put_contents($pagina,$template);
				} else {
					if (!$template){
						$dir = getcwd();
						die("no puedo abrir ($pagina|$dir)");
					}
				}
			}
			
			return $dato;
		}
		
		return $text;
	}


	function configMenu($option){
		global $template;

		//TODO: esta funcion y su utilidad es compleja innecesariamente
		// ..es candidata para una reescritura o re-enfocamiento


		if ($option!="check1" and $option!="check2"){
			$this->addVar('page', 'menu_0_txt', $this->_("Listar") );
			$this->addVar('page', 'menu_0_url', $template["modname"] . ".php" );
			$this->addVar('page', 'menu_1_url', $template["modname"] . ".php?modo=alta" );
			$this->addVar('page', 'menu_2_url', "#" );
			$this->addVar('page', 'menu_1_name', $this->_("Alta") );
			$this->addVar('page', 'menu_0_name', $this->_("Listar") );

		} else {
			$this->addVar('page', 'menu_0_txt', $this->_("Chequeo rapido") );
			$this->addVar('page', 'menu_0_url', $template["modname"] . ".php" );
			$this->addVar('page', 'menu_1_url', $template["modname"] . ".php?modo=profundo" );
			$this->addVar('page', 'menu_2_url', "#" );
			$this->addVar('page', 'menu_0_name', $this->_("Chequeo rapido") );
			$this->addVar('page', 'menu_1_name', $this->_("Chequeo profundo") );

		}

		
		$this->addVar("edicion", "cssbtnremove", "oculto");//lo quitamos de todos sitios

		switch($option){
				case "check2":
					$this->addVar('page', 'current1', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "check1":
					$this->addVar('page', 'current0', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );				
					break;

				case "sololistar":
					$this->addVar('page', 'current0',"current" );
					$this->addVar('page', 'menu_1_css', "oculto" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "listar":
					$this->addVar('page', 'current0', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );
					break;
				case "guardaralta":
					$this->addVar('page', 'current1', "current" );
					$this->addVar('page', 'menu_2_css', "oculto" );

					break;
				case "guardarcambios":
					$this->addVar('page', 'current2',"current" );
					break;
				default:
					$this->addVar('page', 'menu_2_css', "oculto" );
					

					break;
			}


	}




	function configNavegador( $min, $maxfilas,$numFilas ){
		global $template;


		$siguienteDisabled = "";
		$anteriorDisabled = "";
		
		$numActivos = 0;
		$pagSiguiente = 0;
		$pagAnterior = 0;

		if ( $min >= $maxfilas ) {
			$pagAnterior = $min - $maxfilas;
			$numActivos++;
		}  else {
			$anteriorDisabled = "disabled='disabled'";
		}

		if  ($numFilas < $maxfilas) {
			$pagSiguiente = $min;
			$siguienteDisabled = "disabled='disabled'";
		} else {
			$numActivos++;
			$pagSiguiente = $min + $maxfilas;
		}


		if ( 0){
			if (!$numActivos) {
				//echo "SAle, porque no hay botones que activar";
				return;//no hay botones activos, asi que ocultamos el navegador, que no es necesario.
			}

			$this->setAttribute( 'navegador', 'src', 'navegador.htm' );


			$this->addVar( 'navegador', 'modname', $template["modname"] );

			$this->addVar( 'navegador', 'paganterior', $pagAnterior );
			$this->addVar( 'navegador', 'pagsiguiente', $pagSiguiente );

			$this->addVar( 'navegador', 'antdisabledhtml', $anteriorDisabled );
			$this->addVar( 'navegador', 'sigdisabledhtml', $siguienteDisabled );
		}else{
			$this->addVar( 'mininavegador', 'paganterior', $pagAnterior );
			$this->addVar( 'mininavegador', 'pagsiguiente', $pagSiguiente );

			if ($min<=0){
				$this->addVar( 'mininavegador', 'firstdisabledhtml', 'imagebotondesactivado');
			}

			if ($siguienteDisabled){
				$this->addVar( 'mininavegador', 'lastdisabledhtml', 'imagebotondesactivado');
			}

			if ($anteriorDisabled){
				$this->addVar( 'mininavegador', 'antdisabledhtml', 'imagebotondesactivado');
			}
			if ($siguienteDisabled){
				$this->addVar( 'mininavegador', 'sigdisabledhtml', 'imagebotondesactivado');
			}
		}
	}

	function Volcar(){
		$this->displayParsedTemplate();
	}



	function addArrayFromCursor( $subtemplate,&$cursor, $multiple ){

		if (!$multiple) return;

		if (!$cursor) return;//TODO: emitir un error

		foreach($multiple as $key){
			$this->addVar( $subtemplate, $key, $cursor->get($key)  );
		}

	}

	//TODO: mover esto a su propia clase, pues no se utiliza ampliamente, y es demasiado especifico

	function getIcon($gifname){
		return "<img src='icons/".$gifname."' class='icon'  align='absmiddle'  />";
	}

	function getIconOk(){
		return $this->getIcon("ok1.gif");
	}

	function getIconError(){
		return $this->getIcon("error.png");
	}

	function getIconResult($result){
		if ($result)
			return $this->getIconOk();

		return $this->getIconError();
	}


        function moduloNecesitaRegen($nombreModulo,$id_user=false,$masviejoque=60,$listaid=false,$filtromodo=false){

            $nombreModulo_s = sql($nombreModulo);

            $masviejoque =intval($masviejoque,10);

            if($id_user){
                $id_user_s = sql($id_user);
                $extra .= " AND id_user_owner='$id_user_s' ";
            }

            if($listaid!=-1){
                $listaid_s = sql($listaid);
                $filtromodo_s = sql($filtromodo);

                $extra .= " AND listaid='$listaid_s' AND filtromodo='$filtromodo_s' ";
            }



            $sql = "SELECT nombrebloque FROM data_bloques WHERE nombrebloque='$nombreModulo_s' AND $extra AND ultima_modificacion < DATE_SUB(  NOW() , INTERVAL $masviejoque MINUTE ) ";

            $row = queryrow($sql);

            return $row;//falta si no hay bloque, o es mas viejo que  $masviejoque minutos
        }


        function cargadatosModulo($nombreModulo,$datos,$meta=false,$id_user=false){

            $nombreModulo_s = sql($nombreModulo);

            $extra = "";

            $bloque = array("nombrebloque"=>$nombreModulo, "datos"=>$datos);//se empaqueta, de modo que podamos meter metadata de algun tipo

            if($meta){
                foreach($meta as $key=>$value){
                    $bloque[$key] = $value;
                }
            }

            if($id_user){
                $id_user_s = sql($id_user);
                $extra = " AND id_user_owner='$id_user_s' ";
            }

            $datos_s = sql(serialize($bloque));

            $sql = "UPDATE data_bloques SET data='$datos_s' WHERE ultima_modificacion=NOW() and nombrebloque='$nombreModulo_s' $extra ";
            query($sql);
        }

        function getTipo_modulo($nombreModulo){ 
            //TODO: esto no deberia estar aqui
            // la dependencia entre bloques y sus metodos de almacenamiento
            // es manifiestamente evil
            switch($nombreModulo){
                case "modpanelcomv_incidencias":
                    return "longarray";
                case "modpanelcomv_trazabilidad":
                    return "arrayarrays";
                default:
                return "arrayarrays";
            }
        }

        function crearModuloDinamicamente($nombreModulo,$id_user){
            global $UltimaInsercion;
            $nombreModulo_s = sql($nombreModulo);
            $id_user_s = sql($id_user);
            $tipo = $this->getTipo_modulo($nombreModulo);

            $sql = "INSERT data_bloques(nombrebloque,id_user_owner,tipo)VALUES('$nombreModulo_s','$id_user_s','$tipo')";
            query($sql);

            $id = $UltimaInsercion;
            return $id;
        }
        
        function cargaModulo($nombreModulo,$metalocal=false,$id_user=false,$pattemplate='page'){
            if($id_user){
                $id_user = CleanID($id_user);
                $extra = " AND id_user_owner='$id_user' ";
            }

            $nombreModulo_s = sql($nombreModulo);
            $sql = "SELECT * FROM data_bloques WHERE nombrebloque='$nombreModulo_s' $extra ";

            $row = queryrow($sql);

            //TODO: algun tipo de error reporting?
            if(!$row and $id_user){
                $id = $this->crearModuloDinamicamente($nombreModulo,$id_user);
                $row = queryrow("SELECT * FROM data_bloques WHERE id='$id'");
            }

            if(!$row){
                //TODO: horrible error
                return;
            }


            $tipo = $row["tipo"];

            $bloque = unserialize($row["data"]);

            //En "ejecucion" es posible modificar los datos del modulo, todo se puede modificar, pequeños cambios, medianos y grandes.
            if($metalocal){
                foreach($metalocal as $key=>$value){
                    $bloque[$key] = $value;
                }
            }
            
            $lineas = $bloque["datos"];

            switch($tipo){
                case "longarray":
                    if(is_array($lineas))
                    foreach($lineas as $key=>$value){
                        $page->addVar('page',$nombreModulo.'_'.$key,$value);
                    }
                    break;
                case "arrayarrays":
                    if(0) {
                        $c = count($lineas);
                        error_log("t($tipo),nm($nombreModulo),lCount:".$c. ",data:".str_replace("\n"," ",var_export($lineas,true)));
                    }

                    if(isset($bloque['rellenato']) and $bloque['rellenato']>0){
                        $actual = count($lineas);
                        $max = $bloque['rellenato'];
                        //metemos tantas lineas vacias como sea necesario hasta respetar la peticion "rellenar-to"
                        for($t=$actual;$t<$max;$t++){
                            $lineas[] = array();
                        }
                    }

                    $this->addRows($nombreModulo, $lineas );
                    break;
                case "jsondata":
                    $json = json_encode($lineas);
                    $this->addVar($pattemplate,$nombreModulo,$json);
                    //NOTA: json es mas rapido que php. pero se tiene que elegir un formato,
                    // y se ha elegido php, asi que aqui se obliga a hacer dos conversiones
                    // lo que evidentamente resultara mas lento que una. Pero en cualquier caso
                    //  son tiempos despreciables (igual 4 o 5 milisegundos)
                    break;
                case "manualphp":
                default:
                    //??
                    break;                                
            }            
        }
}


/*
 * Se comporta como si fuera una template, pero realmente esta vacia
 * se usa para simular el sistema de traducciones cuando esta desactivado.
 */

class FakePagina {
        function IniciaTranslate(){}
        
    	function _($text){
                    return $text; //
        }
}



//$page->addvar....   $campofiltrando . "_orden",   .""

class Solapa extends Pagina {

    
    function autoReadonly($data){

        //Si la explotacion la tiene prohibida
        
        $explotacionNunca = estaHabilitado("explotacion/impedir",false); //solo ver, no explotar
        if($explotacionNunca){
            $this->addVar('page','cssreasignar','oculto');//CCSREASIGNAR
            $this->addVar('page','cssasignardelegacion','oculto');//CSSASIGNARDELEGACION
            $this->addVar('page','cssasignarclienteperm','oculto');//CSSASIGNARCLIENTEPERM

            $this->addVar('page','ifreadonly'," readonly='readonly' ");//IFREADONLY

            $this->addVar('page','ifdisabled'," disabled='disabled' ");
            return;
        }

        ///Si puede en algunos casos

        if(!tieneFiltrosDelegacion()){
            return;
        }

        $id_location = $data["id_delegacion"];

        $explotacion = estaHabilitado("explotacion/$id_location",false); //si tiene explotacion, puede modificar,
        if(!$explotacion){
            $this->addVar('page','cssreasignar','oculto');//CCSREASIGNAR
            $this->addVar('page','cssasignardelegacion','oculto');//CSSASIGNARDELEGACION
            $this->addVar('page','cssasignarclienteperm','oculto');//CSSASIGNARCLIENTEPERM

            $this->addVar('page','ifreadonly'," readonly='readonly' ");//IFREADONLY

            $this->addVar('page','ifdisabled'," disabled='disabled' ");
        }
    }

    function soloLecturaIncidencia($data){
        $cerrada = getParametro("binow.incidencia_cerrada");
        $eliminada = getParametro("binow.incidencias.id_status.eliminada");

        return;
    }


    function esIncidencias($data){
        if($data["id_task"]== getParametro("binow.id_canal_incidencias")){
            return true;
        }
        return false;
    }

    function enEstudioRW($data){
    }


    function comboContextualLogistica($data){

    }

    function comboContextualDelegacion($data){

        function getEstadoFromId($valor){
            $valor_s = sql($valor);
            $sql = "SELECT * FROM `status` WHERE id_status='$valor_s' ";

            $row = queryrow($sql);

            return $row["status"];
        }

        $recibida = getParametro("binow.id_status_recibido");
        $abierta = getParametro("binow.incidencias.id_status.abierto");
        $enestudio = getParametro("binow.id_status_enestudio");
        $pendiente_gestor = getParametro("binow.id_status_pendientegestor");
        $pendiente_logistica = getParametro("binow.id_status_pendientelogistica");
        $cerrada = getParametro("binow.incidencia_cerrada");
        $eliminada = getParametro("binow.incidencias.id_status.eliminada");

        $permite = array();

        $permite[] = $data["id_status"];

        $status = $data["id_status"];
        switch($status){
            case $recibida:
                $permite[] = $abierta;
                $permite[] = $cerrada;
                $permite[] = $eliminada;
                break;
            case $abierta:
                $permite[] = $cerrada;
                $permite[] = $eliminada;
                //$permite[] = $pendiente_logistica;
                break;
            case $pendiente_gestor:
            case $pendiente_logistica:
            case $cerrada:
            case $eliminada:
            case $enestudio:
                $permite[] = $cerrada;
                $permite[] = $eliminada;
                break;
        }

        /*
        USUARIO DELEGACION:
        *Si el est.de la inciden.: RECIBIDA: lo puede cambiar a abierta, cerrada y eliminada
        *Si el est.de la inciden.: ABIERTA: lo puede cambiara cerrada y eliminada
        *Si el est.de la inciden.: PENDIENTE GESTOR: lo puede cambiar a cerrada y eliminada
         *
         */


        $out = "";
        foreach($permite as $valor){
            if($valor){
                $sel = $data["id_status"] == $valor?" selected=selected ":"";

                $out .= "<option value='".$valor."' $sel>".html(getEstadoFromId($valor))."</option>";
            }
        }

        return $out;
    }


    function comboContextual($data){

        $combohtml = genCombosStatusCanal($data["id_task"],$data["id_status"]);

        //otros canales
        if($data["id_task"]!= getParametro("binow.id_canal_incidencias")){
            $this->addVar('page', 'combosstatus',$combohtml );
            return;
        }

        // A partir de aqui, --> incidencias
        $filtroLineaLogistica = estaHabilitado("modincidencias/filtroLogistica",false);
        $filtroLineaDelegacion = estaHabilitado("modincidencias/filtroDelegacion",false);
        $x = estaHabilitado("modincidencias/x",false);

        if($filtroLineaDelegacion) {

            error_log("DELEGACION: requiere filtro de delegacion ($filtroLineaLogistica)($filtroLineaDelegacion),x($x)");

            $combohtml = $this->comboContextualDelegacion($data);
        } else
        if ($filtroLineaLogistica){

            //No se da, porque no tiene este combo

            //$this->comboContextualLogistica($data);
            //return;
        }

        //admin
        $this->addVar('page', 'combosstatus',$combohtml );
        
    }


    function botonEnEstudio($data){
        $recibida = getParametro("binow.id_status_recibido");
        $abierta = getParametro("binow.incidencias.id_status.abierto");
        $enestudio = getParametro("binow.id_status_enestudio");
        $pendiente_gestor = getParametro("binow.id_status_pendientegestor");
        $pendiente_logistica = getParametro("binow.id_status_pendientelogistica");
        $cerrada = getParametro("binow.incidencia_cerrada");
        $eliminada = getParametro("binow.incidencias.id_status.eliminada");

        $estado = $data["id_status"];
        $ver = (($estado==$abierta) || ($estado==$pendiente_logistica));

        if(!$ver){
            $this->addVar('page','cssenestudio','oculto');
        }

    }


    /*
     */
     function SiguienteBoton($id_task,$id_status_actual){

            if($id_task == getParametro("binow.id_canal_incidencias") ){

                $id_actual = $id_status_actual;

                $sql = "SELECT * FROM `status` WHERE id_task='$id_task'  ORDER BY peso ASC";

                $res = query($sql);
                $siguiente = false;
                $id_siguiente = false;
                $texto_siguiente = "";

                while($data = Row($res)){

                    if($siguiente){
                        $id_siguiente = $data["id_status"];
                        $texto_siguiente = $data["status"];
                        $siguiente = false;
                    }

                    $id = $data["id_status"];
                    if($id_actual==$id){
                        $siguiente = true;
                    }
                }


                if(!$id_siguiente){
                    $this->addVar('page','ocultarnext','oculto');
                    $this->addVar('page',"id_status_siguiente",$id_siguiente);
                }
                //$this->addVar('page',"siguiente_estado",$texto_siguiente . " ,(id:$id_siguiente), (se:$texto_siguiente)(it:$id_task)(is:$id_status_actual)");
                $this->addVar('page',"siguiente_estado",$texto_siguiente);
            } else {
                $this->addVar('page','ocultarnext','oculto');
                //$this->addVar('page',"siguiente_estado","No estamos en este canal");
            }
     }


}



