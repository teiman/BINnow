<?php



include_once(__ROOT__ . "/class/permisos.class.php");

function limpiaFiltrosModo($MODOSFILTRO) {

    $data = json_decode($MODOSFILTRO,true);

    $arr = array();

    foreach($data as $tipo=>$modo) {
        $tipo = validaFiltro($tipo);

        if($tipo) {
            $arr[$tipo] = $modo;
        }
    }

    return $arr;
}





/*
 * Hace un join de elementos,pero solo usando los "llenos"
*/
function xjoin($glue,$elements) {
    $data = array();

    foreach($elements as $cosa) {

        if($cosa and strlen($cosa)>0)
            array_push($data,$cosa);
    }

    return join($glue,$data);
}


/*
 * Vuelca en excel.
*/
function DumpExcel($sql,$camposExportar) {
    $doc = array();

    $data = array();
    foreach ($camposExportar as $campo) {
        $data[] 	= code2nombre($campo);
    }

    $doc[] = $data;

    $res = query($sql);

    while( $row=Row($res) ) {
        $doc[] = $row;
    }

    $date = date("Ymd");

    createExcel("datos_{$date}.xls", $doc);
}


/*
 * Combina varios "filtros" en uno solo
 *
*/

function combinaFiltros($filtroParametrizado,$autofiltros) {
    $filtroParametrizado = is_array($filtroParametrizado)?$filtroParametrizado:array();
    $autofiltros = is_array($autofiltros)?$autofiltros:array();
    $vistos = array();

    $newFiltro = array();

    foreach($filtroParametrizado as $key=>$value) {
        $tipo = $value["tipo"];
        if (!isset($vistos[$tipo])) {
            $vistos[$tipo] = true;

            $newFiltro[] = $value;
        }
    }
    foreach($autofiltros as $key=>$value) {
        $tipo = $value["tipo"];
        if (!isset($vistos[$tipo])) {
            $vistos[$tipo] = true;

            $newFiltro[] = $value;
        }
    }


    return $newFiltro;
}


/*
 * Prepara los filtros parametrizados para el formato de templates en js
 *
*/
function gen_filtrojs($filtroParametrizado) {
    $filtrojs = array();

    foreach($filtroParametrizado as $key=>$value) {
        $filtrojs[] = $value["tipo"];
    }

    return $filtrojs;
}


function tieneDato($dato) {
    if ($dato==="0" or $dato===0 or $dato==="000" or $dato==="00" or $dato==="00000") return true;

    return $dato;
}

function tipo2simbols(&$modos,$tipo) {
    $out = array();
    $modo = $modos[$tipo];

    $out["="] = "=";
    $out[">"] = ">";
    $out["<"] = "<";
    $out[">="] = ">=";
    $out["<="] = "<=";
    $out["LIKE"] = "LIKE";
    $out["entre"] = " and ";  //  fecha>=x and fecha<=y   ====>   fecha<x or fecha>y
    $out["orvarios"] = " or ";
    $out["IN"] = " IN ";

    switch($modo) {
        case "igual":
            break;
        case "distinto":
            $out["="] = "!=";
            $out[">"] = "<=";
            $out["<"] = ">=";
            $out[">="] = "<";
            $out["<="] = ">";
            $out["LIKE"] = "NOT LIKE";
            $out["entre"] = " or ";
            $out["orvarios"] = " and ";
            $out["IN"] = " NOT IN ";
            break;
        case "menorque":
            $out["="] = "<";
            break;
        case "mayorque":
            $out["="] = ">";
            break;
    }

    //error_log("tipo($tipo):modo:".$modo);
    //error_log("data,request:".$_REQUEST["modosfiltro"]);

    return $out;
}


function buildWhere($filtroParametrizado,$modos=false,$tabla="D_RESUMEN_DATOS") {

    $hayCustom2011 = false;

    $esGerente = false;

    $res = array();
    $cuantos = 0;

    $andtagWHERE = "";

    if(!$modos) $modos = array();//si no se especifica, se utiliza reglas vacias

    if(count($filtroParametrizado)>0)
        foreach($filtroParametrizado as $key=>$value) {
            $tipo = validaFiltro($value["tipo"]);

            if($tipo) {

                //$code2tipo
                $modof = code2tipo ($tipo);

                $simbol = tipo2simbols($modos,$tipo);

                $igual=    $simbol["="] ;
                $mayorque=    $simbol[">"] ;
                $menorque=    $simbol["<"] ;
                $mayoroigual=    $simbol[">="] ;
                $menosoigual=    $simbol["<="] ;
                $like=    $simbol["LIKE"] ;
                $entre = $simbol["entre"];
                $orvarios = $simbol["orvarios"];
                $inn =$simbol["IN"];


                if($tipo=="unidad_de_negocio" and $esGerente and $hayCustom2011) {
                    //Posible expansion
                }
                else
                    switch($modof) {
                        case "fecha":
                            $valor1_s = sql($value["param1"]);
                            $valor2_s = sql($value["param2"]);

                            if(tieneDato($valor1_s) or tieneDato($valor2_s)) {
                                $parteWhere .=  " $andtagWHERE ($tipo $mayoroigual  '$valor1_s' $entre $tipo $menosoigual '$valor2_s') ";

                                $cuantos++;
                                $andtagWHERE= " and ";
                            }
                            break;

                        case "entredosvalores":
                        case "entredosfechas":
                            $valor1_s = sql($value["param1"]);
                            $valor2_s = sql($value["param2"]);

                            if(tieneDato($valor1_s) or tieneDato($valor2_s)) {
                                $parteWhere .=  " $andtagWHERE ($tipo $mayoroigual '$valor1_s' $entre $tipo $menosoigual '$valor2_s') ";

                                $cuantos++;
                                $andtagWHERE= " and ";
                            }

                            break;
                        case "cantidad":
                            $valor1_s = sql($value["param1"]);
                            $valor2_s = sql($value["param2"]);

                            if(tieneDato($valor1_s) or tieneDato($valor2_s)) {
                                $parteWhere .=  " $andtagWHERE ($tipo $mayoroigual '$valor1_s' $entre $tipo $menosoigual '$valor2_s') ";

                                $cuantos++;
                                $andtagWHERE= " and ";
                            }

                            break;
                        case "?":
                        case "texto":
                            $valor1_s = sql(utf8iso($value["param1"]));

                            if(tieneDato($valor1_s)) {
                                $parteWhere .=  " $andtagWHERE ( LCASE($tipo) $like LCASE('%$valor1_s%') ) ";

                                $cuantos++;
                                $andtagWHERE= " and ";
                            }
                            break;
                        default:

                            $posibles = array();
                            for($t=0;$t<1000;$t++) {
                                if(isset($value["param" . $t])) {
                                    $val = $value["param" . $t];

                                    if(tieneDato($val))
                                        $posibles[] = $val;
                                }
                            }

                            if(count($posibles)>0) {
                                $parteWhere .=  " $andtagWHERE  (";
                                $ortag = "";


                                foreach($posibles as $key=>$value) {
                                    $value_s = sql(utf8iso($value));
                                    $comparador = comparacionCampo($tipo,$value_s,$igual,$like);
                                    $parteWhere .=  " $ortag $comparador ";
                                    $ortag = " $orvarios ";
                                }

                                $parteWhere .=  " )  ";

                                $cuantos++;
                                $andtagWHERE= " and ";
                            }
                            break;
                    }
            }
        }

    $permisos = new sql_permisos();

    $extra_permisos = $permisos->get_permisos_logueado("D_RESUMEN_DATOS");

    if ($extra_permisos) {
        $parteWhere .= " $andtagWHERE ($extra_permisos)  ";
        $andtagWHERE = " and ";
    }

    //echo "<!-- extra:'$extra_permisos' -->";
    //die("hola:$extra_permisos");


    $res["parteWhere"] = $parteWhere;
    $res["cuantos"] = $cuantos;

    return $res;
}


function  preparaSubtotalYOrders($subtotal,$tabla="D_RESUMEN_DATOS") {
    $res = array();
    $subtotalsql = "";
    $subtotaljs = array();

    $subtotalGroupby = "";

    if(count($subtotal)>0)
        foreach($subtotal as $key=>$valueid) {
            $value = validaFiltro($tabla."_" . $valueid);
            if($value) {
                $subtotaljs[] = array("tipo"=>$tabla."_" . $valueid);

                $subtotalsql =  $subtotalsql . " $andWHERESUBTOTAL ($valueid)  ";
                $orderbysubtotal = $orderbysubtotal. " $andORDERBYSUBTOTAL $valueid asc  ";

                $subtotalGroupby = $subtotalGroupby . " $and  $valueid  ";

                //error_log("preparaSubtotalYOrders:$subtotalGroupby");

                $and = " ,";
                $andWHERESUBTOTAL =  " ,";
                $andORDERBYSUBTOTAL =  " ,";
            }
        }

    $res["subtotalGroupby"] = $subtotalGroupby;
    $res["subtotalsql"] = $subtotalsql;
    $res["orderbysubtotal"] = $orderbysubtotal;
    $res["subtotaljs"] = $subtotaljs; //campos de subtotales limpiados para js

    return $res;
}

function prepara2js($agrupar) {

    $res = array();
    $parteGroup = "";

    $agruparjs = array();

    foreach($agrupar as $key=>$valueid) {
        $value = validaFiltro($valueid);

        if($value) {
            $value_s = sql($value);
            $parteGroup .=  " $andtagGroup $value_s ";
            $andtagGroup= " , ";

            $agruparjs[] = array("tipo"=>$valueid);
        }

    }

    $res["agruparjs"] = $agruparjs;
    $res["parteGroup"] = $parteGroup;

    return $res;
}


function limpiaSubtotal($SUBTOTALES) {

    $subtotal_raw = explode(",",$SUBTOTALES);

    if(count($subtotal_raw)>0)
        foreach($subtotal_raw as $columna) {
            $v = validaFiltro($columna);
            if($v)   $subtotal[$t++] = $v;
        }

    return $subtotal;
}



function limpiaFiltroParametrizado($filtrojs) {
    $filtroParametrizado = array();

    $t=0;


    if(count($filtrojs)>0)
        foreach($filtrojs as $key=>$value) {
            //param_filtro_D_RESUMEN_DATOS_nombre_del_gestor"
            $tipo = $value["tipo"];
            $newdato = array("tipo"=>$tipo);
            $datos = 0;

            //1000 parametros
            for($n=0;$n<1000;$n++) {
                $name = "param_filtro_". $tipo."_$n";
                if(isset($_REQUEST[$name])) {
                    $dato =  $_REQUEST["param_filtro_". $tipo . "_$n"];

                    $newdato["param$n"] = $dato;
                    if($dato)
                        $datos++;
                }
            }

            //solo un parametro del mismo nombre
            if(isset($_REQUEST["param_filtro_". $tipo])) {
                $dato = $_REQUEST["param_filtro_". $tipo ];
                $newdato["param1"] = $dato;
                $datos++;
            }

            //alias "d" y  "h" como param1 y param2.
            if(isset($_REQUEST["param_filtro_". $tipo."_d"])) {
                $newdato["param1"] = CleanFechaES($_REQUEST["param_filtro_". $tipo . "_d"]);
                $datos++;
            }
            if(isset($_REQUEST["param_filtro_". $tipo."_h"])) {
                $newdato["param2"] = CleanFechaES($_REQUEST["param_filtro_". $tipo . "_h"]);
                $datos++;
            }


            //if($datos>0)
            $filtroParametrizado[] = $newdato;
        }

    return $filtroParametrizado;
}



function limpiarFiltros($FILTROS,$tabla="D_RESUMEN_DATOS") {
    $filtrojs = array();
    if($FILTROS) {
        $filtro_raw = explode(",",$FILTROS);

        if(count($filtro_raw)>0)
            foreach($filtro_raw as $columna) {
                $v = validaFiltro($columna);
                if($v) {
                    $filtro[] = $v;
                    $filtrojs[] = array("tipo"=> $tabla."_"  .$v);
                }
            }
    }

    return $filtrojs;
}


function limpiarAgrupamientos($AGRUPAMIENTOS) {
    $agrupar = explode(",",$AGRUPAMIENTOS);

    return $agrupar;
}





function limpiarColumnas($COLUMNAS) {
    $columnas_raw = explode(",",$COLUMNAS);

    //error_log("limpiarColumnas:COLUMNAS:".$COLUMNAS.",data:".var_export($COLUMNAS,true));
    $t=0;

    if(count($columnas_raw)>0)
        foreach($columnas_raw as $columna) {
            $v = validaFiltro($columna);
            if($v)   $columnas[$t++] = $v;
        }

    return $columnas;
}


function esColumnaAgrupar($value,$tabla="D_RESUMEN_DATOS") {//Si esta columna se utiliza para agrupar
    global $agruparjs;
    foreach($agruparjs as $key=>$item) {
        if($item["tipo"]==$tabla ."_".$value)
            return true;
    }

    return false;
}

function esColumnaAgruparEscogida($value) {
    global $eligidoagrupar;

    if(!$eligidoagrupar) return true;
    if(count($eligidoagrupar)==1 and $eligidoagrupar[0] == "") return true;

    if(in_array($value, $eligidoagrupar)) {
        return true;
    }

    return false;
}


function esColumnaVirtual($value,$tabla="D_RESUMEN_DATOS") {
    global $virtual2formula;

    if(isset($virtual2formula[$value])) {
        return true;
    }

    if(isset($virtual2formula[$tabla . "_" . $value])) {
        return true;
    }

    //error_log("valor:$value, NO VIRTUAL");

    return false;
}

function getValueColumnaVirtual($value,$tabla="D_RESUMEN_DATOS") {
    global $virtual2formula;

    if(isset($virtual2formula[$value])) {
        return $virtual2formula[$value];
    }

    //error_log("gVCV:$value, no encontrando..., rebusca:". $virtual2formula[$tabla. "_" . $value] );
    return $virtual2formula[$tabla. "_" . $value];
}


function esVarchar($tipo) { //Si esta columna es texto, deberia usarse LIKE in wheres
    global $code2mysqltipo;

    $modo = $code2mysqltipo[$tipo];

    if($modo=="texto_varchar") {
        return true;
    }

    return false;
}

function comparacionCampo($tipo,$value_s,$equal="=",$like="LIKE") {

    $requiereLike = esVarchar($tipo);

    if($requiereLike) {
        return " trim($tipo) $like trim('$value_s') ";
    } else {
        return " $tipo $equal '$value_s' ";
    }

}





function esColumnaTexto($value) { //Si esta columna debe ser excluida de sumas/subtotales
    global $code2tipo;

    $tipo = $code2tipo[$value];

    if($tipo=="numero") {
        return false;
    }

    if($tipo=="moneda") {
        return false;
    }

    if($tipo=="cantidad") {
        //error_log("v:$value, es de tipo  ($tipo)");
        return false;
    }


    if(0) {
        //2011-04-17: añadido "tipo==texto"
        //2011-04-17: corregido tipo="codigo" => tipo=="codigo"
        if( $tipo=="cod" or $tipo=="nombre" or $tipo=="codigo" or $tipo=="texto"  ) {
            return true;
        }
    } else {
        if( $tipo=="cod" or $tipo=="nombre"  or $tipo=="texto" or $tipo="codigo" ) {
            return true;
        }
    }

    if($tipo=="json" or $tipo=="porcentaje") {
        return true;
    }

    return false;
    //return esColumnaAgrupar($value);//por ejemplo, porque se utiliza como valor de agrupacion
}



function getOperadorContexto($average,$newid) {

    foreach($average as $key=>$value) {
        if($newid==$value)
            return "AVG";
    }

    return "SUM";
}

function limpiaAverage($AVERAGE) {
    $final = array();
    $x_raw = explode(",",$AVERAGE);

    if(count($x_raw)>0)
        foreach($x_raw as $columna) {
            $v = validaFiltro($columna);
            if($v)   $final[$t++] = $v;
        }

    return $final;
}


function generaFormateadores($usa_estas_columnas=false) {
    global $columnas;

    $columnas_usar = $columnas;

    if($usa_estas_columnas)
        $columnas_usar = $usa_estas_columnas;


    $formateadores = array();

    foreach($columnas_usar as $columna) {
        $tipo = code2tipo($columna);

        switch($tipo) {
            case "fecha":
                $f = function($dato,$prepad="",$postpad="") {
                            return "<td>$prepad". CleanFechaFromDB($dato) ."$postpad</td>";
                        };
                break;
            case "cantidad":
                $f = function($dato,$prepad="",$postpad="") {
                            //return "<td align='right'>$prepad". intval($dato,10)."$postpad</td>";
                            return "<td align='right'>$prepad". FormatUnidades($dato)."$postpad</td>";
                        };
                break;

            case "moneda":
                $f = function($dato,$prepad="",$postpad="") {
                            return "<td align='right'>$prepad". FormatMoney($dato) ."$postpad</td>";
                        };

                break;
            default:
                if($columna == "porcentaje_devolucion_pkin") {
                    $f = function($dato,$prepad="",$postpad="") {

                                if($dato=="-0.000000" and false) {
                                    $dato = "0.00";
                                } else
                                    $dato = number_format($dato, 2, ',', ".");

                                return "<td align='right'>$prepad". html8859(trim($dato)) ." % $postpad</td>";
                            };
                } else {
                    $f = function($dato,$prepad="",$postpad="") {
                                return "<td>$prepad". html8859(trim($dato)) ."$postpad</td>";
                            };
                }

                //error_log("columna:$columna,tipo:$tipo");
                break;
        }

        $formateadores[] = $f;
    }

    $generica = function($dato,$prepad="",$postpad="") {
                return "<td>$prepad". html8859(trim($dato)) ."$postpad</td>";
            };
    //evita bugs dificiles de analizar
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;
    $formateadores[] = $generica;

    $formateadores["simple"] = function($dato,$prepad="",$postpad="") {
                return "<td>$prepad". html8859(trim($dato)) ."$postpad</td>";
            };

    return $formateadores;
}


//convierte un nombre de campo tal cual lo usa el interface, en un campo de la base de datos D_VENTAS_RESUMEN
function validaFiltro($dato) {
    global $claves_trans;
    if(isset($claves_trans[$dato]))
        return $claves_trans[$dato];

    return false;
}

function html8859($str) {

    $newstr = iconv("ISO-8859-1","UTF-8//IGNORE",$str);

    if(!$newstr) {
        $newstr = $str;
    }

    return 	htmlentities($newstr,ENT_QUOTES,'UTF-8');
}



function uniendo_columnas_select($columnas,$average,$hayAgrupar) {

    if(count($columnas)>0)
        foreach($columnas as $key=>$value) {
            $virtual = esColumnaVirtual($value);

            if ( esColumnaTexto($value) or esColumnaAgrupar($value)) {

                $dato = $value;
                if($virtual) {
                    $dato =  getValueColumnaVirtual($value);
                }

                $parteSelectSubtotal2 .=  " $andSelect2 $dato ";
            } else {

                $se_llamara = $value;
                $dato = $value;
                if($virtual) {
                    $dato =  getValueColumnaVirtual($value);
                }

                if($hayAgrupar>0) {
                    $operador = getOperadorContexto($average,$value);
                    if(!$virtual)
                        $parteSelectSubtotal2 .=  " $andSelect2 $operador($dato) as $se_llamara ";
                    else
                        $parteSelectSubtotal2 .=  " $andSelect2 ($dato) as $se_llamara ";
                } else {
                    if(!$virtual)
                        $parteSelectSubtotal2 .=  " $andSelect2 $dato ";
                    else
                        $parteSelectSubtotal2 .=  " $andSelect2 $dato as $se_llamara ";
                }
            }
            $andSelect2 = ",";
        } else {
        $parteSelectSubtotal2 = " '' ";
    }

    return $parteSelectSubtotal2;
}


function es_columna_acumulador($columna) {
    global $acumuladores;

    if(!is_array($acumuladores)) {
        return false;
    }

    if(!$columna)
        return false;

    $ret = array_search($columna,$acumuladores);

    $ret = ($ret===0 or $ret>0);

    return $ret;
}
