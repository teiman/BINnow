<?php

$ARBOL_AND = 1; //default
$NODO_RAIZ = 0;

$macros = array();

if(!isset($_SESSION["permisos_condiciones_macros"])){


    $sql = "SELECT * FROM condiciones_macros ORDER BY descripcion ASC";
    $res = query($sql);

    while ($row = Row($res)) {
        $macros_permisos[] = array("descripcion" => $row["descripcion"], "id_condicion" => $row["id_condicion"]);
    }

    $_SESSION["permisos_condiciones_macros"] = $macros_permisos;

}else{
    $macros_permisos = $_SESSION["permisos_condiciones_macros"];
}




$operadores = array(
    "igual" => array("simbolo" => "=", "sql" => "="),
    "mayorigual" => array("simbolo" => ">=", "sql" => ">="),
    "mayor" => array("simbolo" => ">", "sql" => "in"),
    "menorigual" => array("simbolo" => "<=", "sql" => "<="),
    "menor" => array("simbolo" => "<", "sql" => "<"),
    "diferente" => array("simbolo" => "!=", "sql" => "!="),
    "entrey" => array("simbolo" => "entre", "sql" => "between"),
    "in" => array("simbolo" => "en", "sql" => "in")
);

class nodo {

    public function agnade($dato, $id_padre = 0) {
        $dato_s = sql($dato);
        $id_padre_s = sql($id_padre);

        $sql = "INSERT INTO arbol_permisos ("
                . "  id_padre, tipo, dato "
                . " ) VALUES ( "
                . " '$id_padre_s', 0, '$dato_s' )";

        query($sql);
    }

    public function crearSub($id_padre, $andor = "and") {
        global $UltimaInsercion;

        $id_padre_s = sql($id_padre);

        $tipo = ($andor == "and") ? 1 : 2;

        $sql = "INSERT INTO arbol_permisos ("
                . "  id_padre, tipo, dato "
                . " ) VALUES ( "
                . " '$id_padre_s', $tipo, '$andor' )";

        query($sql);

        return $UltimaInsercion;
    }

    public function hijos($id_nodo) {
        $row = queryrow("SELECT count(*) as cuantos FROM arbol_permisos WHERE id_padre='$id_nodo' ");
        return $row["cuantos"];
    }

    public function cambiaPadre($id_nodo, $id_newpadre) {
        query("UPDATE arbol_permisos SET id_padre='$id_newpadre' WHERE id_nodo='$id_nodo' ");
    }

    public function cambiaTipo($id_nodo, $newtipo) {
        query("UPDATE arbol_permisos SET tipo='$newtipo' WHERE id_nodo='$id_nodo' ");
    }

    public function eliminarHijos($id_nodo) {
        $sql = "SELECT id_nodo FROM arbol_permisos WHERE id_padre='$id_nodo' ";

        $res = query($sql);

        while ($row = Row($res)) {
            $id_nodo = $row["id_nodo"];
            nodo::eliminarHijos($id_nodo);
            $sql = "DELETE FROM arbol_permisos WHERE id_nodo='$id_nodo' ";
            query($sql);
        }
    }

    public function eliminar($id_nodo) {
        $sql = "DELETE FROM arbol_permisos WHERE id_nodo='$id_nodo'";
        query($sql);

        nodo::eliminarHijos($id_nodo);
    }

    public function datos($id_nodo) {
        $row = queryrow("SELECT * FROM arbol_permisos WHERE id_nodo='$id_nodo' ");
        return $row;
    }

    public function update($condicion, $comparador, $param1, $param2, $id_nodo) {
        $condicion_s = sql($condicion);
        $param1_s = sql($param1);
        $param2_s = sql($param2);
        $comparador_s = sql($comparador);

        $set = " condicion_dato1 ='$condicion_s' "
                . ", condicion_param1 ='$param1_s' "
                . ", condicion_param2 ='$param2_s' "
                . ", condicion_operador ='$comparador_s' "
                . ", condicion1_esmacro ='1' "
        ;

        $sql = "UPDATE arbol_permisos SET $set  WHERE id_nodo='$id_nodo'";

        query($sql);
    }

    public function _raw_describe($nodo) {
        global $operadores;

        //echo "<h3>" . var_export($row,true) . "</h3>";


        $id = $nodo["condicion_dato1"];
        $sql = "SELECT descripcion FROM condiciones_macros WHERE id_condicion='$id' ";
        $row = queryrow($sql);
        $descrito_campo = trim($row["descripcion"]);

        $operador = $operadores[$nodo["condicion_operador"]];
        $operador_txt = $operador["simbolo"];
        //param1
        $p1 = $nodo["condicion_param1"];

        $k = $nodo["condicion_operador"];

        switch ($nodo["condicion_operador"]) {
            case "entrey":
                $p2 = $nodo["condicion_param2"];
                $p1_txt = " '$p1' y '$p2' ";
                break;

            case "in":
                $p1_txt = " ($p1) ";
                break;

            default:
                $p1_txt = " '$p1' ";
                break;
        }



        return " <span class='campo'>$descrito_campo</span> $operador_txt $p1_txt ";
    }

}

class arbol extends nodo {

    public function limpiar() {
        query("TRUNCATE arbol_permisos");
        $id_root = nodo::crearSub(0, "and");
        nodo::agnade("ciudad=zargoza", $id_root);
    }

    public function autodescribir($id_padre) {
        $sql = "SELECT * FROM arbol_permisos WHERE id_nodo='$id_padre' ";
        $row = queryrow($sql);

        return arbol::describir($id_padre, $row["tipo"]);
    }

    public function describir($id_padre, $tipo_rama) {
        $out = "";
        $grupo = "";
        $text_tipo = ($tipo_rama == 1) ? " <span class='operadorlogico'>y</span> " : " <span class='operadorlogico'>o</span> ";

        $sql = "SELECT * FROM arbol_permisos WHERE id_padre='$id_padre' ";

        $res = query($sql);

        $pegamento = "";

        while ($row = Row($res)) {


            $id_nodo = $row["id_nodo"];
            $tipo = $row["tipo"];

            //echo "<h3>". var_export($row,true) . "</h3>";

            if ($tipo == 1 or $tipo == 2) {
                $out .= " $pegamento ";

                $out .= " <span class='interno'>( ";
                $out .= arbol::describir($id_nodo, $tipo);
                $out .= " )</span>";
            } else {
                //$desc = $row["dato"];

                $desc = valido8(nodo::_raw_describe($row));


                $out .= " $pegamento $desc ";
            }

            $pegamento = $text_tipo;
        }
        return $out;
    }

    function mostrar($id_padre, $nivel = 0, $tipo_rama = 1) {
        $grupo = "";

        $text_tipo = ($tipo_rama == 1) ? " <span class='operadorlogico'>y</span> " : " <span class='operadorlogico'>o</span> ";

        $sql = "SELECT * FROM arbol_permisos WHERE id_padre='$id_padre' ";

        $res = query($sql);
        $primero = true;
        $primeroS = true;

        $out = "";

        while ($row = Row($res)) {
            $id_nodo = $row["id_nodo"];
            $tipo = $row["tipo"];

            if ($tipo == 1 or $tipo == 2) {  // es un subarbol
                $text = ($tipo == 1) ? " y esto " : " o esto ";

                if (!$primero)
                    $out .= "<li>$text_tipo</li>";
                $out .= "<ul class='nivel_$nivel'>";

                $primero = false;

                $out .= " <li class='cabezagrupo'> ";

                $hijos = nodo::hijos($id_nodo);
                $out .= botonEliminarNodo($id_nodo, $this->id_rama);

                if ($hijos > 1) {
                    $out .= gui_permisos::botonToggleTipo($id_nodo, $tipo, $this->id_rama);
                }

                $out .= botonElevarAgrupo($id_nodo, $this->id_rama) . "</li>";
                $out .= $this->mostrar($id_nodo, $nivel + 1, $tipo);

                $out .= " </ul>";
            } else {
                $desc = $row["dato"];

                if (!$primero)
                    $out .= "<li>$text_tipo</li>";

                $out .= "<li><input type='checkbox' class='ck_selector' value='$id_nodo'> " . ui_ModificarNodo($id_nodo, $this->id_rama) . "</li>\n";
                $primero = false;
            }
        }

        $out .= " <li> " . formAgnadirAqui($id_padre, $this->id_rama) . "</li>";
        return $out;
    }

}

class sql_permisos {

    var $pocket = "";

    function sql_permisos($usa_extrajoins = false) {
        $this->Init($usa_extrajoins);
    }

    /*
     * Inicializa variables de la clase.
     */

    function Init($usa_extrajoins = false) {
        $this->atributos_perdidos = array();
        $this->texto_joins = ""; //texto de joins que sera necesario para la consulta en el banco de trabajo

        if ($usa_extrajoins) {
            $this->usa_extrajoins = true;
            $this->PrepopulaJoins();
        }
    }

    function PrepopulaJoins() {
        $this->tablas_parches = array();

        $this->requierejoins = array();

        $this->posiblejoins = array();
        $this->yaincluidas = array(); //tablas que ya tenemos
        $this->yaincluidas[$this->pocket] = true;
        $this->yaincluidas_condiciones = array();


        $this->posiblejoins["D_Business_Risk"] = array();
    }

    function construir_joins() {
        //ninguno es necesario!
        if (!count($this->requierejoins)) {
            if ($this->debug)
                echo "no requiere extra tablas!<br>";
            return "";
        }

        $out = "";
        foreach ($this->requierejoins as $index => $data) {
            $out .= " JOIN " . $data["texto"] . " ";
        }
        return $out;
    }

    function construir_bindtext($actual, $posible) {

        if ($posible["requierejoin"]) {
            $tablajoin = $posible["tablajoin"];
            $tabla = $posible["tabla"];

            if ($this->yaincluidas[$tablajoin]) {
                return true;
            }

            if (!$this->yaincluidas[$tabla]) {
                $campobind = $actual . "." . $posible["campo"];
                $campobind2 = $posible["campotabla"];
                $texto = " $tablajoin ON " . $campobind . " = " . $campobind2 . " ";
                $this->requierejoins[] = array("tabla" => $posible["tablajoin"], "texto" => $texto);
                $this->yaincluidas[$tabla] = true;
            }

            $campobind = $tabla . "." . $posible["campojoina"];
            $campobind2 = $tablajoin . "." . $posible["campojoinb"];
            $texto = " $tabla ON " . $campobind . " = " . $campobind2 . " ";

            $this->yaincluidas[$tablajoin] = true;

            $this->requierejoins[] = array("tabla" => $posible["tabla"], "texto" => $texto);

            if ($this->debug) {
                echo "[construir_bindtext] creado DOBLE binding, usando:";
                new dBug($posible);
            }
            return true;
        } else {
            $tabla = $posible["tabla"];

            if ($this->yaincluidas[$tabla]) {
                return true;
            }

            $campobind = $actual . "." . $posible["campo"];
            $campobind2 = $posible["campotabla"];
            
            $texto = " $tabla ON " . $campobind . " = " . $campobind2 . " ";
            $this->yaincluidas[$tabla] = true;
            $this->requierejoins[] = array("tabla" => $posible["tabla"], "texto" => $texto);

            if ($this->debug) {
                echo "[construir_bindtext] creado SIMPLE binding, usando:";
                new dBug($posible);
            }
            return true;
        }
        return false;
    }

    /* Intentaremos buscar join necesarios para obtener tabla */

    function necesitamos_join($tabla, $actual) {
        if (!$this->posiblejoins) {
            return false; //error
        }
        if (!$this->posiblejoins[$actual]) {

            if ($this->debug) {
                echo "[necesitamos_join] no se puede crear bindings de $tabla con $actual. no tiene opciones<br>";
            }
            return false;
        }

        $posibles = $this->posiblejoins[$actual];

        foreach ($posibles as $posible) {
            if ($posible["tabla"] == $tabla) {

                /* Esta tabla se puede enlazar con las actuales */
                $bindeado = $this->construir_bindtext($actual, $posible);

                if ($this->debug) {
                    echo "[necesitamos_join] tabla $tabla bindeado: $bindeado a actual:$actual<br>";
                }

                if ($bindeado)
                    return true;
            }else {

                if ($this->debug) {
                    echo "[necesitamos_join] tabla $tabla no encontrado en posible<br>";
                    new dBug($posible);
                }
            }
        }

        if ($this->debug) {
            echo "[necesitamos_join] no se puede crear bindings de $tabla con $actual. intentados todos<br>";
        }
        return false;
    }

    /*
     * No tenemos un campo, y nos gustaria remediarlo.
     */

    function buscaParche($id_condicion) {

        if ($this->yaincluidas_condiciones[$id_condicion]) {

            if ($this->debug) {
                echo "[buscaParche] id_condicion:$id_condicion ya es una vieja conocida, se reusa alias conocido<br>";
            }

            return $this->yaincluidas_condiciones[$id_condicion];
        }


        /* Con que tablas puede trabajar esta macro? */
        $sql = "SELECT * FROM condiciones_macros WHERE id_condicion='$id_condicion' LIMIT 1";
        $row = queryrow($sql);

        if ($this->debug) {
            //echo " para row:" . html(var_export($row, true)) . ", se buscara en tp:" . html(var_export($this->tablas_parches, true)) . "<br>";

            echo "[buscaParche]";
            new dBug($datosmacro_en_busqueda = $row);
            echo ",tablasparche:";
            new dBug($this->tablas_parches);
        }

        /* Vamos a intentar bindear otras tablas que tengan ese campo */
        foreach ($this->tablas_parches as $index => $tabla) {

            if ($row[$tabla]) {
                if ($this->debug) {
                    echo "[buscaParche]  tabla '$tabla' encontrada en row. sacando posible join<br>";
                }

                $posible = $this->necesitamos_join($tabla, $this->pocket);

                if ($this->debug) {
                    echo "[buscaParche] join posible:$posible, de $tabla con " . $this->pocket . "<br>";
                }

                if ($posible) {
                    $solucion = $tabla . "." . $row[$tabla];
                    $this->yaincluidas_condiciones[$id_condicion] = $solucion;
                    return $solucion;
                }
            } else {
                if ($this->debug) {
                    echo "[buscaParche]  tabla '$tabla' no esta en row<br>";
                }
            }
        }

        if ($this->debug) {
            echo "[buscaParche] ($id_condicion)  tablas no esta en row<br>";
            new dBug($condiciones = $row);
        }

        return false;
    }

    function puede_ver_articulo($cod) {

        return true;
    }

    function puede_ver_cliente($cod) {
        return true;
    }

    function filtro_comunicaciones() {
        return "";
    }

    function get_nodo_de_perfil($id_perfil) {
        $sql = "SELECT id_nodo FROM profiles WHERE id_profile='$id_perfil' ";
        $row = queryrow($sql);
        if (!$row)
            return false;

        return $row["id_nodo"];
    }

    function get_filtro_conocido($tabla, $entidad, $valor) {
        $tabla = $this->valida_tabla($tabla);

        if ($tabla == "")
            return "";

        $entidad_s = sql($entidad);
        $tabla_s = sql($tabla);
        $data = queryrow("SELECT $tabla_s as dato FROM condiciones_macros WHERE phpname='$entidad_s' ");

        if (!$data)
            return "";
        if (!$data["dato"])
            return "";

        return " and (" . $data["dato"] . "='" . sql($valor) . "') ";
    }

    function get_capsula_and($tabla) {

        $this->Init();

        $sql = $this->get_permisos_logueado($tabla);

        $this->texto_joins = $this->construir_joins();

        if (!$sql)
            return "";

        return " and ($sql)";
    }

    function es_ignorable($sql) {
        if (!$sql)
            return true;

        //TODO: cambiar esto por un regex
        $sql = trim($sql);
        $sql = str_replace(" ", "", $sql);
        $sql = str_replace("1", "", $sql);
        $sql = str_replace("and", "", $sql);
        $sql = str_replace("or", "", $sql);
        $sql = str_replace("(", "", $sql);
        $sql = str_replace(")", "", $sql);

        $sql = trim($sql);
        if (!$sql)
            return true;

        return false;
    }

    function get_and_subsql_codcomercialin($field) {
        return "";
    }

    function get_and_subsql_codclientin($field) {
        return "" ;
    }

    function get_and_subsql_codclientin_total($field) {

        return "";
    }

    function valida_tabla($tabla) {
        switch ($tabla) {
            case "D_RESUMEN_DATOS":
                $this->pocket = $tabla;
                //echo "new pocket: " . $this->pocket;
                break;

            default: return "";
        }

        return $tabla;
    }

    function get_permisos_logueado($tabla) {
        //$id_user = getSesionDato("id_user");

        $tabla = $this->valida_tabla($tabla);
        if ($tabla == "")
            return "";

        $id_nodo = getSesionDato("id_nodo_permisos_user"); //nodo permisos

        //die("id_nodo:$id_nodo");

        if ($id_nodo > 0) {
            $thisPart = $this->auto_get_filtro($id_nodo);
            return $thisPart;
        } else {
            return "0";
        }


        return $out;
    }

    function _raw_sql($nodo) {
        global $operadores;

        if (!$this->pocket)
            return "1";

        /* Texto del campo objetivo */

        $id = $nodo["condicion_dato1"];
        $sql = "SELECT " . $this->pocket . " as sqlname FROM condiciones_macros WHERE id_condicion='$id' ";

        if ($this->debug)
            $sql = "SELECT " . $this->pocket . " as sqlname,descripcion FROM condiciones_macros WHERE id_condicion='$id' ";

        $row = queryrow($sql);

        if ($this->debug) {
            new dBug($datosmacro = $row);
        }

        $descrito_campo = trim($row["sqlname"]);


        if (!$descrito_campo) {

            $this->atributos_perdidos[$id] = true;

            if ($this->usa_extrajoins) {
                $parche = $this->buscaParche($id);

                if ($this->debug) {
                    echo "Se podria usar parche ($parche)<br>";
                }

                if (!$parche) {
                    $retorna = $nodo["obligatorio"] ? "0" : "1";
                    return $retorna; //. var_export($row,true). $sql;
                }

                $descrito_campo = $parche;
            } else {
                $retorna = $nodo["obligatorio"] ? "0" : "1";

                return $retorna; //. var_export($row,true). $sql;
            }
        } else {

            if ($this->nombres_completo)
                $descrito_campo = $this->pocket . "." . $descrito_campo;
        }

        /* Texto del operador necesario */

        $operador = $operadores[$nodo["condicion_operador"]];
        $operador_txt = $operador["sql"];

        $p1 = $nodo["condicion_param1"];


        /* Texto del dato o datos */

        switch ($nodo["condicion_operador"]) {
            case "entrey":
                $p2 = $nodo["condicion_param2"];
                $p1_txt = " '$p1' and '$p2' ";
                break;

            case "in":
                $p1_txt = " ($p1) ";
                break;

            default:
                $p1_txt = " '$p1' ";
                break;
        }

        /* Devuelve todo junto */
        return " ($descrito_campo $operador_txt $p1_txt) ";
    }

    function auto_get_filtro($id_padre) {
        $sql = "SELECT * FROM arbol_permisos WHERE id_nodo='$id_padre' ";
        $row = queryrow($sql);

        //echo "row($id_padre):". var_export($row,true);

        return $this->get_filtro($id_padre, $row["tipo"]);
    }

    /*
     * Generando filtro para rama de id_padre tal
     */

    function get_filtro($id_padre, $tipo_rama = 1) {

        $out = "";
        $grupo = "";
        $text_tipo = ($tipo_rama == 1) ? " and " : " or ";

        $sql = "SELECT * FROM arbol_permisos WHERE id_padre='$id_padre' ";
        $res = query($sql);

        $pegamento = "";

        while ($row = Row($res)) {

            //echo "row($id_padre):". var_export($row,true)."<br>\n";

            $id_nodo = $row["id_nodo"];
            $tipo = $row["tipo"];

            if ($tipo == 1 or $tipo == 2) {

                $fragmento = trim($this->get_filtro($id_nodo, $tipo));

                //echo "fragmento:($fragmento)\n";

                if (!( (($fragmento) == "1" or ($fragmento) == "0" ) and trim($pegamento) == "or")) { //  noseque or 1 => siempre true.  
                    if ($fragmento and $fragmento != "()") {
                        $out .= " $pegamento ";
                        $out .= " ( $fragmento ) ";

                        $pegamento = $text_tipo; //siguiente necesita pegamento
                    }
                }
            } else {
                $desc = $this->_raw_sql($row);

                //echo "desc:($desc)\n";

                if ((trim($desc) == "1" or trim($desc) == "0")) {
                    //nada     
                    if ($this->debug) {
                        echo "se ignora desc($desc):<pre>";
                        new dBug($row);
                    }
                } else {
                    $out .= " $pegamento $desc ";

                    $pegamento = $text_tipo; //siguiente necesita pegamento
                }
            }
        }

        return $out;
    }

}

function capsula_and($sql) {
    if (!$sql)
        return "";

    return " and ($sql)";
}

function gen_html_comparadores($comparador) {
    global $operadores;

    $out = "";

    foreach ($operadores as $key => $macro) {
        $sel = ($key == $comparador) ? " selected='selected' " : "";

        $out .= "<option $sel value='" . $key . "' class='entidad'>" . html($macro["simbolo"]) . "</option>";
    }

    return $out;
}

function gen_html_elige_condicion($condicion1) {
    global $macros_permisos;

    $out = "";

    foreach ($macros_permisos as $macro) {

        $sel = ($macro["id_condicion"] == $condicion1) ? " selected='selected' " : "";

        $out .= "<option $sel value='" . $macro["id_condicion"] . "'  class='entidad'>" . html(valido8($macro["descripcion"])) . "</option>";
    }

    return $out;
}

function ui_ModificarNodo($id_nodo, $id_rama = 0) {

    $data = nodo::datos($id_nodo);

    $param1_s = html($data["condicion_param1"]);
    $param2_s = html($data["condicion_param2"]);

    $condicion1 = $data["condicion_dato1"];
    $operador = $data["condicion_operador"];

    $html_elige_condicion = gen_html_elige_condicion($condicion1);
    $html_elige_comparador = gen_html_comparadores($operador);

    if ($data["obligatorio"]) {
        $extra = " [obligatorio] ";
    }


    return "<form method='post'><select  class='eligeentidad'  name='condicion'>$html_elige_condicion</select>"
            . "<select class='eligeentidad' name='comparador'>$html_elige_comparador</select>"
            . "<input type='text' name='param1' value='$param1_s'>"
            . "<input type='text' name='param2' value='$param2_s'>"
            . "<input type='hidden' name='id_rama' value='$id_rama'>"
            . "<input type='hidden' name='id_nodo' value='$id_nodo'>"
            . "<input type='hidden' name='modo' value='actualiza_condiciones'>"
            . "<input type='submit' value='Actualizar'>  $extra"
            . "</form>";
    ;
}

class gui_permisos {

    public function get_ui_subarbol($id_padre, $id_rama = 0) {

        $arbol = new arbol();

        $arbol->id_rama = $id_padre;


        $data = nodo::datos($id_padre);

        $andor = $data["tipo"];

        $borrar = botonEliminarNodo($id_padre, $id_rama);
        $toggle = gui_permisos::botonToggleTipo($id_padre, $data["tipo"], $id_rama);

        $out = "<p>&nbsp;</p>";
        $out .= $borrar . $toggle;
        $out .= "<ul class='nivel_root'>";
        $out .= $arbol->mostrar($id_padre, 0, $andor); //tipo 1=> primera rama es and
        $out .= "</ul>";

        return $out;
    }

    public function arbol() {
        global $NODO_RAIZ, $ARBOL_AND;


        $out = "";
        $out .= "<ul class='nivel_root'>";
        $out .= arbol::mostrar($NODO_RAIZ, 0, $ARBOL_AND); //tipo 1=> primera rama es and
        $out .= "</ul>";

        return $out;
    }

    public function get_descripcion($nodoraiz, $andor) {
        $out = "";
        $out .= "<div class='internoroot'> Descripción: <br>(";
        $out .= arbol::describir($nodoraiz, $andor);
        $out .= " )</div>";

        return $out;
    }

    public function botonToggleTipo($id_nodo, $tipo, $id_rama = 0) {
        $newtipo = ($tipo == 1) ? 2 : 1;

        return "<form method='post'><input type='hidden' name='id_rama' value='$id_rama' ><input type='hidden' name='id_nodo' value='$id_nodo' ><input type='hidden' name='modo' value='toggleTipoNodo' >"
                . "<input type='hidden' name='newtipo' value='$newtipo' ><button>y/o $c</button></form>";
    }

    public function ejemploPermisos() {
        global $NODO_RAIZ, $ARBOL_AND;

        $permisos = new sql_permisos();
        return $permisos->get_filtro($NODO_RAIZ, $ARBOL_AND);
    }

}

function botonEliminarNodo($id_nodo, $id_rama = 0) {
    return "<form method='post'><input type='hidden' name='id_rama' value='$id_rama' >"
            . "<input type='hidden' name='id_nodo' value='$id_nodo' >"
            . "<input type='hidden' name='modo' value='eliminarNodo' ><button>x</button></form>";
}



function formAgnadirAqui($id_padre, $id_rama = 0) {
    return "<form action='modpermisos.php' method='post'> "
            . "<input type='hidden' name='modo' value='agnadenodo' > "
            . "<input type='hidden' name='id_padre' value='$id_padre' > "
            . "<input type='hidden' name='dato' value=''> "
            . "<input type='hidden' name='id_rama' value='$id_rama'> "
            . "<input type='submit' value='+ condicion' style='margin-left:26px' class='mascontenido'> "
            . "</form>";
}

function botonElevarAgrupo($id_nodo, $id_rama = 0) {
    return "<form method='post'><input type='hidden' name='id_rama' value='$id_rama'><input type='hidden' name='modo' value='crearSubNodo' >"
            . "<input type='hidden' name='id_padre' value='$id_nodo'><input type='submit' value='&gt;&gt;' ></form>";
}

