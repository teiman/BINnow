<?php

/**
 * Pantalla de explotacion de datos
 * @package binow
 */


include("tool.php");

include_once(__ROOT__ . "/class/permisos.class.php");

$auth = canRegisteredUserAccess("modreporting",false);

if ( !$auth["ok"] ){
    include("moddisable.php");
}

function esColumnaElegidoAgrupar($value,$tabla="D_RESUMEN_DATOS") {//Si esta columna se utiliza para agrupar
    global $eligidoagrupar;

    if(in_array($value,$eligidoagrupar)){
    return true;
    }

    return false;
}

$tamagnoPaginaTransferencia = 1000; /* El tamaño real que utiliza para _pagina_ en el lado cliente  */
$PREPAD = 1000; /* registros extra que se muestran antes de la _pagina_  */
$tamagnoBigBloque = 3000; /* registros que se traen de la base de datos y que se serviran, la _pagina_ esta en el centro de estos registros  */

include_once(__ROOT__ . "/inc/paginacompleta.php");
include_once(__ROOT__ . "/inc/D_RESUMEN_DATOS.inc.php");
include_once(__ROOT__ . "/inc/modreporting.inc.php");

$id_user = intval(getSesionDato("id_usuario_logueado"));


$AGRUPAMIENTOS  = $_REQUEST["agrupamientos"];
$COMBO          = $_REQUEST["combo"];
$COLUMNAS       = $_REQUEST["columnas"];
$FILTROS        = $_REQUEST["filtros"];
$SUBTOTALES     = $_REQUEST["subtotales"];
$AVERAGE        = $_REQUEST["avgcolumnas"];
$FILTROSDATA    = false;
$ACUMULADORES   = $_REQUEST["campos_acumulador"];

$MODOSFILTRO    = $_REQUEST["modosfiltros"];

//error_log("MF:".$MODOSFILTRO);

$parteSelect = "";
$parteWhere = "";
$parteOrder = "";
$parteLimit = "";

$WHERE      = false;
$ORDER_BY   = false;

$agrupar    = array();
$columnas   = array();
$subtotal   = array();
$filtro     = array();
$filtroParametrizado = array();
$average    = array();

$subtotaljs = array();
$subtotalsql = "";
$orderbysubtotal = "";

$nombreListadoViendo = "";

$informeSoloLectura = false;

switch($modo){

    case "aceptar-informe":
        $id_informe_s = sql($_POST["id_informe"]);
        $nombre_s  = sql($_POST["nombre_informe"]);

        $sql = "UPDATE reporting_user_list SET pendiente=0,name='$nombre_s' WHERE `id_reporting_user_list` ='$id_informe_s' ";
        query($sql);
        break;

    case "rechazar-informe":
        $id_informe_s = sql($_POST["id_informe"]);
    
        $sql = "UPDATE reporting_user_list SET pendiente=2 WHERE `id_reporting_user_list` ='$id_informe_s' ";
        query($sql);
        break;

    /*
     * Intenta eliminar informe. Solo puede eliminar un informe creado o en su posesión.
     */
    case "eliminarinforme":

        $id_user = intval(getSesionDato("id_user"));

        if(!$id_user){
            echo json_encode(array("ok"=>false,"error"=>"El usuario no esta logueado"));
            exit;
        }

        $id_informe_s = sql($_REQUEST["id_informe"]);
        $sql = "SELECT * FROM reporting_user_list WHERE id_reporting_user_list='$id_informe_s' AND (id_user='$id_user') ";
        $row = queryrow($sql);

        if($row){
            $sql = "UPDATE reporting_user_list SET eliminado=1 WHERE `id_reporting_user_list` ='$id_informe_s' ";

            query($sql);
            echo json_encode(array("ok"=>true));
            exit;
        }

        echo json_encode(array("ok"=>false,"error"=>"No se puede eliminar."));
        exit;
        break;
    /*
     * Carga el informe por defecto
     */
    case "self":
    case "init":
        //$id_s = 21;
        break;
    case "loadreport":
        /*
         * Cargamos los datos desde la base de datos, como si vinieran de la red.
         */

        if(!$id_s)
            $id_s = sql($_REQUEST["id"]);

        //$sql = "SELECT * FROM reporting_user_list WHERE (id_reporting_user_list='$id_s') AND (id_user='$id_user' OR id_user=0)  ";
        $sql = "SELECT * FROM reporting_user_list WHERE (id_reporting_user_list='$id_s') AND eliminado=0   ";
        $row = queryrow($sql);

        if($row){
            $AGRUPAMIENTOS  = $row["groupsby"];
            $COLUMNAS       = $row["columns"];
            $FILTROS        = $row["filtros"];//TODO
            $SUBTOTALES     = $row["subtotals"];
            $AVERAGE        = $row["average"];//campos que deben hacer media
            $ACUMULADORES   = $row["acumuladores"];
            $MODOSFILTRO    = $row["modosfiltro"];
            
            if($row["filter_php_data"]){
                $saved_guardados = unserialize($row["filter_php_data"]);
                $FILTROSDATA    = $saved_guardados;

                $base = combinaFiltros($saved_guardados, $autofiltros);
                $filtroParametrizado = combinaFiltros($filtroParametrizado,$base);
                $nombreListadoViendo = $row["name"];
            }

            if($row["id_user"]!=$id_user){
                $informeSoloLectura = true;
            }

        } else {
            error_log("ERROR: No se pudo cargar report($id_s) con sql:($sql)");
        }

        break;

    case "consultacombo":{
        $campo = validaFiltro($COMBO);

        if(isset($_SESSION["consultacombo_".$campo]) ){
            echo $_SESSION["consultacombo_".$campo];
            exit();
        }

            include_once(__ROOT__ . "/inc/modreporting.autofiltros.php");

        /*
         * Vamos a generar filtros, de modo que no se vean cualquier tipo de dato, sino solo los que tiene permiso este usuario
         */
        $tipo = getSesionDato("jd_tipopermisos");
        $user_data = getSesionDato("user_data");

        $extraFiltro = "";

        $filtros = array();
        aplicarAutoFiltros($filtros,$tipo,$user_data);
        $data = buildWhere($filtros,$filtrosmodo,"D_RESUMEN_DATOS");

        $extraFiltro = $data["parteWhere"];

        if($extraFiltro){
            $extraFiltro = " WHERE $extraFiltro ";
        }

        /*
         * Sacamos el filtro que necesito
         */

        $campos = array();

        $campo_s = sql($campo);//evida inyecciones de sql

        $sql = "SELECT distinct $campo_s as campo FROM D_RESUMEN_DATOS $extraFiltro ORDER BY $campo_s ASC";

        $res = query($sql);

        $nokey = ($campo != "nombre_del_asesor");

        while($row = Row($res)){
         //   if(trim($row["campo"])>0 or $nokey )
                $campos[] = array("val"=>iso2utf($row["campo"]),"text"=>iso2utf(trim($row["campo"])));
        }

        $jsoncode =  json_encode($campos);
        $_SESSION["consultacombo_".$campo] = $jsoncode;

        echo $jsoncode;

        exit();
        }
        break;

    case "consultacombo_old":
        $campo = validaFiltro($COMBO);

        if(isset($_SESSION["consultacombo_".$campo]) ){
            echo $_SESSION["consultacombo_".$campo];
            exit();
        }

        $campos = array();

        $nokey = ($campo != "nombre_del_asesor");


        $campo_s = sql($campo);//evida inyecciones de sql

        $sql = "SELECT distinct $campo_s as campo FROM D_RESUMEN_DATOS ORDER BY $campo_s ASC";

        $res = query($sql);

        while($row = Row($res)){
            if(trim($row["campo"]) or $nokey)
                $campos[] = array("val"=>iso2utf($row["campo"]),"text"=>iso2utf(trim($row["campo"])));
        }

        $jsoncode =  json_encode($campos);
        $_SESSION["consultacombo_".$campo] = $jsoncode;

        echo $jsoncode;
        
        exit();
        break;

    default:
        break;
}


//-----------------------------------------------------------------------------------

$OFFSET         = isset($_REQUEST["OFFSET"])?$_REQUEST["OFFSET"]:0;

if( isset($_REQUEST["offsetvalue"]) ){
    $paginaPosicion = $_REQUEST["offsetvalue"];
    $OFFSET = $paginaPosicion*$tamagnoPaginaTransferencia;
}


//-----------------------------------------------------------------------------------

$PREOFFSET = $OFFSET -$PREPAD;
if ($PREOFFSET <0){
    $PREOFFSET = 0;
}

$parteLimit = " LIMIT $PREOFFSET,$tamagnoBigBloque ";// muestra 300 resultados, visualiza ~20, hay "100 anteriores y ~160 siguientes" extra.


include_once(__ROOT__ . "/inc/modreporting.autofiltros.php");
include_once(__ROOT__ . "/modreporting.shared.php");


/*
 * Construyendo el SQL definitivo. uniendo las partes
 * -----------------------------------------------------------------------------------------
 */


$parteSelectSubtotal2 = "";


$hayAgrupar = count($agruparjs);
$hayAcum = count($acumuladores);


$parteSelectSubtotal2 = uniendo_columnas_select($columnas,$average,$hayAgrupar);


$parteSelectSubtotal3 = "";
$andSelect3 = "";
$n = 0;

if(count($columnas)>0)
foreach($columnas as $key=>$value){

    $se_llamara = $value;

    if ( esColumnaAgrupar($value) and esColumnaAgruparEscogida($value) ){

        if(!esColumnaVirtual($value)){
            $parteSelectSubtotal3 .=  " $andSelect3 $se_llamara  ";
            $andSelect3 = ",";
        } else {
            $dato =  getValueColumnaVirtual($value);
            $parteSelectSubtotal3 .=  " $andSelect3 $dato  ";
            $andSelect3 = ",";
        }
    } else {
        if(!esColumnaTexto($value) ){
            $operador = getOperadorContexto($average,$value);
            $dato = $value;

            if(esColumnaVirtual($value)){
                $dato =  getValueColumnaVirtual($value);

                $parteSelectSubtotal3 .=  " $andSelect3 ($dato) as $se_llamara ";
            } else {
                $parteSelectSubtotal3 .=  " $andSelect3 $operador($dato) as $se_llamara ";
            }
            
            $andSelect3 = ",";
        } else {
            $parteSelectSubtotal3 .=  " $andSelect3 '' as pad$n ";//TODO: ya no es necesario..
            $n++;

            $andSelect3 = ",";
        }
    }

} else {
    $parteSelectSubtotal3 = " '' ";
}


/* Vamos a fabricar el Select de SQL_TOTAL */

$selectTotales = "";
$andSelectTotal = "";
$andExtraWhereX = "";
$extraWhere = "";

if(count($columnas)>0)
foreach($columnas as $key=>$value){

    //echo "<h1>$key,$value</h1>";
    if ( !esColumnaAgrupar($value) ){
        $se_llamara= $value;

        if(!esColumnaTexto($value)){
            $dato = $value;
            
            if(esColumnaVirtual($value)){
                 $dato = getValueColumnaVirtual($value);
                 $selectTotales .=  " $andSelectTotal $dato as $se_llamara ";
            } else {

                $extraWhere .=  " $andExtraWhereX $dato != 0 ";
                $andExtraWhereX = " or ";


                 $selectTotales .=  " $andSelectTotal SUM($dato) as $se_llamara ";
            }



            $andSelectTotal = ",";
        } else {
            $selectTotales .=  " $andSelectTotal '' as pad$n ";//TODO aun es necesario?
            $n++;

            $andSelectTotal = ",";
        }
    } else {
            $selectTotales .=  " $andSelectTotal '' as pad$n ";//TODO aun es necesario?
            $n++;

            $andSelectTotal = ",";

    }
}

if($extraWhere ){
    if($parteWhere)
        $extraWhere =   " and ( $extraWhere) ";
    else
        $extraWhere =   " WHERE $extraWhere ";
}


if(!$parteSelectSubtotal2)
        $parteSelectSubtotal = $parteSelect;


if($hayAgrupar){
    /*
     *
     NOTA "Por supuesto, en el caso de que no aparezca ninguna columna en "Agrupar por" no se aplica que en el SQL normal
     solo aparezca en el SELECT las columnas de la agrupación,
     aparecerán las que estén seleccionadas para visualizar."
     *
     *

     Hay agrupar => parte select especial que solo contiene los datos de agrupar, y los numericos sumados
     No hay agrupar => normal, en el que aparecen todas las columnas

     *
     */
    $parteSelectSubtotal2 = $parteSelectSubtotal3;
}


/*
 * Evita que se haga un "SELECT WHERE" que seria sintacticamente incorrecto.
 */
if (!$parteSelectSubtotal2){
    $parteSelectSubtotal2 = " '' ";//NOTA: abortar consulta?
    $parteLimit = " LIMIT 1 ";    
}
if (!$parteSelectSubtotal3){
    $parteSelectSubtotal3 = " '' ";//NOTA: abortar consulta?
}
if (!$selectTotales){
    $selectTotales = " '' ";//NOTA: abortar consulta?
}

/*
 * Diferentes SQL que podemos necesitar
 */

//SQL normal
$sql = "SELECT $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteOrder $parteLimit\n";

/*
$sql_selectivo  = "SELECT $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteLimit\n";*/

$sql_nolimit = "SELECT $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteOrder\n";


$sql_count = "SELECT SQL_CALC_FOUND_ROWS  $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteOrder\n";

$sql_count2 = "SELECT  $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteOrder\n";

$sql_count_cogegrupo = "SELECT  $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $subtotalGroupby \n"
.    " $ORDER_BY ". xjoin(",",$eligidoagrupar);



/*
 * Vamos a intentar obtener los subtotales
 *
 */

if($subtotalGroupby) $GROUP_BY = " GROUP BY ";


/*
 OBSOLETO: no se usa, porque ahora se muestra sql_subtotales2
 *
 */
$sql_subtotales = "SELECT $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $GROUP_BY $subtotalGroupby \n"
.    " $ORDER_BY ". xjoin(",",array($orderbysubtotalORDERBY,$orderbysubtotal,$parteOrder));


/*
 * Se usa por "cogegrupo".
 *
 */

$sql_subtotales2 = "SELECT $parteSelectSubtotal3 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $extraWhere \n" //no permitir una fila de todo ceros
.    " $GROUP_BY $subtotalGroupby \n"
.    " $ORDER_BY ". xjoin(",",$eligidoagrupar);


/*
 "El total absoluto del Grid, que cogerá el último SQL ejecutado, le quitará los Group by y todas las columnas del SELECT salvo las númericas del SUM."
 */

$sql_total = "SELECT $selectTotales \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n";


if($hayAcum){
    $sql = $sql_final_acum;
}


/*
 *
 * Si hay acumulador, el acumulador se "apodera"
 */


/*
 * Prepara y visualiza los datos
 */

if($modo=="savereport"){
        $name_s = sql(trim($_REQUEST["nombre_para_informe"]));

        if($name_s){


            $id_user_s = sql(getSesionDato("id_user"));

            $sql = "SELECT count(*) as c FROM reporting_user_list WHERE id_user='$id_user_s' AND  name LIKE '$name_s' ";

            $row = queryrow($sql);
            if($row["c"]>0){
                //reportamos que la operacion no ha sido un exito
                $data = array("ok"=>false,"id"=>false,"error"=>"El listado ya existe"  );
                echo json_encode($data);
                exit();
            }


            $AGRUPAMIENTOS_s  = sql($AGRUPAMIENTOS);
            $COLUMNAS_s       = sql($COLUMNAS);
            $FILTROS_s        = sql($FILTROS);
            $SUBTOTALES_s     = sql($SUBTOTALES);
            $AVERAGE_s        = sql($AVERAGE);
            $ACUMULADORES_s     = sql($ACUMULADORES);
            $MODOSFILTRO_s    = sql($MODOSFILTRO);

            //ALTER TABLE  `reporting_user_list` ADD  `id_user_envia` INT NOT NULL DEFAULT  '0' AFTER  `pendiente` ;

            $filtroParametrizado_src_s = sql(serialize($filtroParametrizado));

            $sql = "INSERT reporting_user_list (name,id_user,columns,groupsby,subtotals,filter_php_data,average,acumuladores,modosfiltro,id_user_envia) "
            .   " VALUES "
            .   "( '$name_s','$id_user_s','$COLUMNAS_s','$AGRUPAMIENTOS_s','$SUBTOTALES_s', '$filtroParametrizado_src_s','$AVERAGE_s','$ACUMULADORES_s','$MODOSFILTRO_s' ,'0') ";

            query($sql);

            $data = array("ok"=>true,"id"=>$UltimaInsercion  );
            echo json_encode($data);
            exit();
        }   else {

            //reportamos que la operacion fallo
            $data = array("ok"=>false,"id"=>false,"error"=>""  );
            echo json_encode($data);
            exit();
        }

} else if($modo=="actualizareport"){
        $id_report_s = sql(trim($_REQUEST["idmodosharereport"]));
        $id_user_s = sql(getSesionDato("id_user"));

        $sql = "SELECT * FROM reporting_user_list WHERE (id_reporting_user_list='$id_report_s') AND eliminado=0  AND (id_user='$id_user_s') ";
        $row = queryrow($sql);

        if(!$row){
            //reportamos que la operacion ha fallado
            $data = array("ok"=>false,"id"=>false,"error"=>"No puedes modificar este informe"  );
            echo json_encode($data);
            exit();
        }


        if($id_report_s){
            $AGRUPAMIENTOS_s  = sql($AGRUPAMIENTOS);
            $COLUMNAS_s       = sql($COLUMNAS);
            $FILTROS_s        = sql($FILTROS);
            $SUBTOTALES_s     = sql($SUBTOTALES);
            $AVERAGE_s        = sql($AVERAGE);
            $ACUMULADORES_s     = sql($ACUMULADORES);
            $MODOSFILTRO_s    = sql($MODOSFILTRO);

            $filtroParametrizado_src_s = sql(serialize($filtroParametrizado));
            $name = $_REQUEST["nombresugerido"];
            $name = str_replace("( modificado )","",$name);

            $name_s = sql($name);

            $sql = "UPDATE reporting_user_list SET  "
            .   " name='$name_s',id_user='$id_user_s',columns='$COLUMNAS_s',groupsby='$AGRUPAMIENTOS_s',subtotals='$SUBTOTALES_s',"
            .   " filter_php_data='$filtroParametrizado_src_s',average='$AVERAGE_s',acumuladores='$ACUMULADORES_s',modosfiltro='$MODOSFILTRO_s' "
            .   " WHERE id_reporting_user_list ='$id_report_s' ";

            query($sql);

            $data = array("ok"=>true,"id"=>$UltimaInsercion,"succes"=>"Informe actualizado correctamente"  );
            echo json_encode($data);
            exit();
        }   else {

            //reportamos que la operacion ha fallado
            $data = array("ok"=>false,"id"=>false,"error"=>"Informe desconocido"  );
            echo json_encode($data);
            exit();
        }

} else if($modo=="savereport-share"){
        $modoshare = $_REQUEST["modosharereport"];

        $name_s = sql(trim($_REQUEST["nombre_para_informe"]));
        $idquien = sql(trim($_REQUEST["idmodosharereport"]));

        if($name_s){

            $id_user_s = sql(getSesionDato("id_user"));

            function getIdUserFromUsername($username){
                    $username_s = sql($username);
            $sql = "SELECT id_user FROM users WHERE user_login='$username_s' AND `deleted`='0' ";
                    $row = queryrow($sql);


                    return $row["id_user"];
            }

            $AGRUPAMIENTOS_s  = sql($AGRUPAMIENTOS);
            $COLUMNAS_s       = sql($COLUMNAS);
            $FILTROS_s        = sql($FILTROS);
            $SUBTOTALES_s     = sql($SUBTOTALES);
            $AVERAGE_s        = sql($AVERAGE);
            $ACUMULADORES_s     = sql($ACUMULADORES);
            $MODOSFILTRO_s    = sql($MODOSFILTRO);

            if($modoshare=="todos"){
                //No se guarda en si mismo, sino en "todos".
                $id_user2_s = 0;
            } else if($modoshare=="otrousuario") {
                $newiduser = getIdUserFromUsername($idquien);

                if(!$newiduser){
                    //reportamos que la operacion no ha sido un exito
                    $data = array("ok"=>false,"id"=>false  );
                    echo json_encode($data);
                    exit();
                }
                $id_user2_s = $newiduser;
            } else if($modoshare=="grupo") {


                $id_user_s = sql(getSesionDato("id_user"));

                $id_grupo_s = sql($idquien);
                $sql = "SELECT * FROM user_groups WHERE id_group='$id_grupo_s'";

                $res = query($sql);

                $filtroParametrizado_src_s = sql(serialize($filtroParametrizado));

                while($row=Row($res)){
                    $id_user2_s = $row["id_user"];

                    $sql = "INSERT reporting_user_list (name,id_user,columns,groupsby,subtotals,filter_php_data,average,acumuladores,id_group,modosfiltro,pendiente,id_user_envia) "
                    .   " VALUES "
                    .   "( '$name_s','$id_user2_s','$COLUMNAS_s','$AGRUPAMIENTOS_s','$SUBTOTALES_s', '$filtroParametrizado_src_s','$AVERAGE_s','$ACUMULADORES_s','$id_grupo_s','$MODOSFILTRO_s','1','$id_user_s' ) ";
                    query($sql);
                }

                $data = array("ok"=>true,"id"=>false  );
                echo json_encode($data);
                exit();
            }

            $filtroParametrizado_src_s = sql(serialize($filtroParametrizado));
            $sql = "INSERT reporting_user_list (name,id_user,columns,groupsby,subtotals,filter_php_data,average,acumuladores,modosfiltro,pendiente,id_user_envia) "
            .   " VALUES "
            .   "( '$name_s','$id_user2_s','$COLUMNAS_s','$AGRUPAMIENTOS_s','$SUBTOTALES_s', '$filtroParametrizado_src_s','$AVERAGE_s','$ACUMULADORES_s','$MODOSFILTRO_s', '1' ,'$id_user_s') ";

            query($sql);

            $data = array("ok"=>true,"id"=>$UltimaInsercion  );
            echo json_encode($data);
            exit();
        }   else {

            //reportamos que la operacion no ha sido un exito
            $data = array("ok"=>false,"id"=>false  );
            echo json_encode($data);
            exit();
        }
}





/*
 * Reorganizamos las columnas para que los campos de agrupacion aparezcan al principio
 */

$columnas_originales = $columnas;//guardamos el orden original para poder volver a el.

$primeros = array();

if(count($agruparjs)>0)
foreach( $agruparjs as $key=>$criterio  ){
    $id = $criterio["tipo"];

    $nombre = validaFiltro($id);
    if($nombre){
        $primeros[] = $nombre;

        //los quitamos de donde esten
        $offset = array_search($nombre, $columnas);
        unset($columnas[$offset]);
    }
}
$columnas = array_merge($primeros,$columnas);//los ponemos al principio

/*
 * Regulariza el array, para que tenga un formato logico  0=>"algo",1=>"algo",... y no  ""=>"algo","0"=>"algo"...
 */

$columnas2 = array();
$n=0;
foreach($columnas as $key=>$col){
    $columnas2[$n] = $col;
    $n++;
}
$columnas = $columnas2;


$namedColumnas = array();
if(count($columnas)>0)
foreach($columnas as $colum){
    $namedColumnas[]= array("tipo"=>code2nombre($colum),"id"=>$colum);
}


//---------------------------------------------------------------



switch($modo){
    case "toexcel": {

        ini_set('memory_limit', '1024M');

        require_once("inc/excel/excel.php");
        require_once("inc/excel/excel-ext.php");

        DumpExcel($sql_nolimit,array());
        exit();

        }
        break;

    case "imprimir":{

        ini_set('memory_limit', '1024M');

        include("inc/runmeforever.inc.php");//este script puede correr potencialmente durante un tiempo muy largo
        $res = query($sql_nolimit);


        //header("Content-type: octect/stream");

        header("Content-Type:  text/html");

        include("templates/listadodescarga.snip.html");

        $formateadores = generaFormateadores($columnas_originales);


        echo "<h4>Explorador de datos</h4>";
        echo "<p>Listado creado el: ".date("d-m-Y")."</p>";

        echo '<table id="tablaresultados" class="tablesorter droppable" style="margin:0px" border="0" cellpadding="0" cellspacing="1" width="100%" border="1">';
        cron_flush();


        echo '<thead><tr id="lista_columnas">';
        foreach($namedColumnas as $data){
             echo '<th class="cajacabeza_columna"><a class="cabeza_columna" id="cabeza_D_RESUMEN_DATOS_'.$data["id"].'" rel="D_RESUMEN_DATOS_'.$data["id"].'">'.html($data["tipo"]).'</a> &nbsp; &nbsp; </th>';
        }
        echo '</tr></thead>';

        cron_flush();

        


        while($row=Row($res)){
            echo "<tr class='f'>";
            $m=0;
            foreach($columnas_originales as $columna){

               $f = $formateradores[$m];
               $dato = $row[$columna];
               if(!$f) {
                   $f = $formateadores["simple"];
               }

               echo  $f($dato);

               $m++;
            }
            echo "</tr>\n";
            $n++;
        }
        echo "</table>";

        if(1)
            echo "<script language='Javascript' type='text/javascript'>if ($.browser.mozilla){window.print();
                                        } else {
                                            document.execCommand('print', false, null);
                                        }</script>";
        else
            echo "<script language='Javascript' type='text/javascript'>window.print(); console.log('algo se deberia estar imprimiendo');</script>";

        echo "</body></html>";
        //echo "<script>alert('window.print()');</script>";


        exit();
        }
        break;

  case "descargar": {

        ini_set('memory_limit', '1024M');

        include("inc/runmeforever.inc.php");//este script puede correr potencialmente durante un tiempo muy largo
        $res = query($sql_nolimit);

        //header("Content-type: octect/stream");

        if(1){
            header("Pragma: public"); // required
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false); // required for certain browsers
            header("Content-Type:  octect/stream");
            header("Content-Disposition: attachment; filename=\"listado.html\";" );
            header("Content-Transfer-Encoding: binary");
        }

        include("templates/listadodescarga.snip.html");

        echo "<h4>Explorador de datos</h4>";
        echo "<p>Listado creado el: ".date("d-m-Y")."</p>";


        echo '<table id="tablaresultados" class="tablesorter droppable" style="margin:0px" border="0" cellpadding="0" cellspacing="1" width="100%">';
        cron_flush();


        echo '<thead><tr id="lista_columnas">';
        foreach($namedColumnas as $data){
             echo '<th class="cajacabeza_columna"><a class="cabeza_columna" id="cabeza_D_RESUMEN_DATOS_'.$data["id"].'" rel="D_RESUMEN_DATOS_'.$data["id"].'">'.html($data["tipo"]).'</a> &nbsp; &nbsp; </th>';
        }
        echo '</tr></thead>';

        cron_flush();

        $formateadores = generaFormateadores($columnas_originales);

        while($row=Row($res)){
            echo "<tr class='f'>";
            $m=0;
            foreach($columnas_originales as $columna){
               $f = $formateradores[$m];
               $dato = $row[$columna];
               if(!$f) {
                   $f = $formateadores["simple"];
               }
               echo  $f($dato);
               $m++;
            }
            echo  "</tr>\n";
            $n++;
            cron_flush();
        }


        echo "</table>";
        echo "</body></html>";
        cron_flush();

        exit();
        }
        break;


    case "cogegrupo":
        global $eligidoagrupar;



        $salida = "";

        $res = query($sql_subtotales2);

        $LANDINGPAD = $OFFSET-$PREOFFSET;
        if ($LANDINGPAD<0)
            $LANDINGPAD=0;

        $tablaprepad = $OFFSET*20 - $LANDINGPAD * 20 ;

        $inicia = true;
        $actual = array();//tendra el valor "actual" (viejo) del subtotal, cuando cambie, toca mostrar el resumen.

        $primeraLinea = true;
        $numListados = 0;

        $formateadores = generaFormateadores($columnas_originales);

        $n = $PREOFFSET;
        $str = "";
        while($row=Row($res)){
            if($primeraLinea) {
                if($tablaprepad>0)
                    echo "<tr data-info='tablaprepad'><td height='$tablaprepad' style='visibility:hidden'>&nbsp;</tr>";
            }

            $line = "<tr class='f'>";
            $n = 0;
            foreach($columnas_originales as $columna){
                $dato = $row[$columna];
               $f = $formateadores[$n];

               if(!$f) $f = $formateadores["simple"];

                if(esColumnaElegidoAgrupar($columna) or !esColumnaTexto($columna) ){
                    $line .= $f($dato). "<!-- $columna -->";
                } else {
                    $line .= "<td class='oculto' width='0'></td>";
                }
			//$line .= "<td>". html( var_export($eligidoagrupar,true)). "</td>";
		
               $n++;
            }
            $line .=  "</tr>\n";

            $primeraLinea = false;

            echo $line;
            $n++;
            $numListados++;

            if($numListados>=$tamagnoBigBloque) break;
        }

        @flush();

        if(1){
            $cuantosAlmacen = "cuantos_" . md5($sql_count_cogegrupo  . "_salt5_subtotales_");//identifica una consulta

            if( isset($_SESSION[$cuantosAlmacen])){

                    $cuantos = $_SESSION[$cuantosAlmacen];//saca el numero de este cache

                    //error_log("sql_count,el numero de registros de cache:".$cuantos);
            } else {
                    $res2 = query($sql_count_cogegrupo ); //pide todos
                    $cuantos =  mysql_num_rows($res2); //saca cuantos

                    $_SESSION[$cuantosAlmacen] = $cuantos;//almacena el dato en este cache
            }

            //$tablepostpad = $cuantos * 20 - $OFFSET*20  - $tamagnoBigBloque*20 - $LANDINGPAD*20;
            $tablepostpad = $cuantos * 20 - $tablaprepad - $tamagnoBigBloque*20;
            if($tablepostpad<0) $tablepostpad = 0;

            $page = $_REQUEST["offsetvalue"];

            if($tablepostpad>0)
                echo "<tr  data-info='tablapostpad' data-numlineas='$cuantos' data-page='$page'><td height='$tablepostpad' style='visibility:hidden'>&nbsp;</td></tr>";
            else
                echo "<tr style='visibility:hidden' data-info='tablapostpad' data-numlineas='$cuantos' data-page='$page'><td height='0' style='visibility:hidden'>&nbsp;</td></tr>";

        } else {
            if($numListados>=$tamagnoBigBloque)
                echo "<tr  data-info='fake-tablapostpad'><td height='2000' style='visibility:hidden'>&nbsp;</td></tr>";
            else
                echo "<tr  data-info='fake-tablapostpad' data-info2='registros completos'><td height='0' style='visibility:hidden'>&nbsp;</td></tr>";
        }


        if(1){//debug
            echo "<div class='autoeval oculto' data-tipo='sql_mostrar_subtotales'>".html($sql_subtotales2)."</div>";
        }


        //echo "<script>/* $sql \n $sql_count \n tpp:$tablepostpad,  tablepostpad=$cuantos *20  - $tablaprepad - 300*20  */</script>";

        exit();
        break;
    case "cogedatos":


        if(count($columnas)<=0){
            $line = "<tr class='f'>";
            $line .=  "</tr>\n";

            echo $line;

            if(1){//debug
                echo "<div class='autoeval oculto' data-tipo='sql_mostrar'></div>";
                echo "<div class='autoeval oculto' data-tipo='cuantas_lineas'>0</div>";
            }

            exit();
        }

        $salida = "";
        $cuantos = 0;

        $res = query($sql);

        $LANDINGPAD = $OFFSET-$PREOFFSET;
        if ($LANDINGPAD<0)
            $LANDINGPAD=0;

        $tablaprepad = $OFFSET*20 - $LANDINGPAD * 20 ;

        $inicia = true;
        $actual = array();//tendra el valor "actual" (viejo) del subtotal, cuando cambie, toca mostrar el resumen.

        $primeraLinea = true;
        $numListados = 0;

        $formateadores = generaFormateadores($columnas_originales);
        $simple = $formateadores["simple"];
        //$debugoutput = function($dato) {  return "<td>DEBUG:". $dato . "</td>"; };
        //$debugoutput2 = function($dato) {  return "<td>DEBUG2:". $dato . "</td>"; };


        $n = $PREOFFSET;
        $str = "";
        while($row=Row($res)){
            if($primeraLinea) {
                if($tablaprepad>0){
                    echo "<tr data-info='tablaprepad'><td height='$tablaprepad' style='visibility:hidden'>&nbsp;</tr>";
                }
            }

            $line = "<tr class='f'>";
            $n = 0;
            $m = 0;

            foreach($columnas_originales as $columna){

               $f = $formateadores[$n];

               if(!es_columna_acumulador($columna)) {
                  $dato = $row[$columna];//. ".$columna";
                  //$f = $debugoutput2;
               } else {
                  $dato = $row[$columna ."_acum"];
                  //$f = $debugoutput;
               }
               
               $line .= $f($dato);
               $n++;
               $m++;
            }

            $line .=  "</tr>\n";

            //error_log("columnas:". var_export($columnas));

            $primeraLinea = false;

            echo $line;
            $n++;
            $numListados++;

            if($numListados>=$tamagnoBigBloque) break;
        }

        @flush();

        if(1){
            $cuantosAlmacen = "cuantos_" . md5($sql_count . "_salt4_" );//identifica una consulta

            if( isset($_SESSION[$cuantosAlmacen])){

                    $cuantos = $_SESSION[$cuantosAlmacen];//saca el numero de este cache

                    error_log("sql_count,el numero de registros de cache:".$cuantos);
            } else {
                    $res2 = query($sql_count2); //pide todos
                    $cuantos =  mysql_num_rows($res2); //saca cuantos

                    $_SESSION[$cuantosAlmacen] = $cuantos;//almacena el dato en este cache
            }

            //$tablepostpad = $cuantos * 20 - $OFFSET*20  - $tamagnoBigBloque*20 - $LANDINGPAD*20;
            $tablepostpad = $cuantos * 20 - $tablaprepad - $tamagnoBigBloque*20;
            if($tablepostpad<0) $tablepostpad = 0;

            $page = $_REQUEST["offsetvalue"];

            if($tablepostpad>0)
                echo "<tr  data-info='tablapostpad' data-numlineas='$cuantos' data-page='$page'><td height='$tablepostpad' style='visibility:hidden'>&nbsp;</td></tr>";
            //else
            //    echo "<tr style='visibility:hidden' data-info='tablapostpad' data-numlineas='$cuantos' data-page='$page'><td height='0' style='visibility:hidden'>&nbsp;</td></tr>";
        } else {
            if($numListados>=$tamagnoBigBloque)
                echo "<tr  data-info='fake-tablapostpad'><td height='2000' style='visibility:hidden'>&nbsp;</td></tr>";
            else
                echo "<tr  data-info='fake-tablapostpad' data-info2='registros completos'><td height='0' style='visibility:hidden'>&nbsp;</td></tr>";
        }


        //if($numListados<$tamagnoBigBloque)
        //    $cuantos = $numListados;

        if(1){//debug
            echo "<div class='autoeval oculto' data-tipo='sql_mostrar'>".html($sql)."</div>";
            echo "<div class='autoeval oculto' data-tipo='cuantas_lineas'>$cuantos</div>";
        }


        //echo "<script>/* $sql \n $sql_count \n tpp:$tablepostpad,  tablepostpad=$cuantos *20  - $tablaprepad - 300*20  */</script>";

        exit();
        break;


   case "capturatotal":

        /*
         "El total absoluto del Grid, que cogerá el último SQL ejecutado, le quitará los Group by y todas las columnas del SELECT salvo las númericas del SUM."
         */
        if(count($columnas)<=0){
            $line = "<tr class='f'>";
            $line .=  "</tr>\n";

            echo $line;

            if(1){//debug
                echo "<div class='autoeval oculto' data-tipo='sql_mostrar_total'></div>";
            }

            exit();
        }

        $formateadores = generaFormateadores($columnas_originales);


        $salida = "";

        $res = query($sql_total);

        $inicia = true;
        $actual = array();//tendra el valor "actual" (viejo) del subtotal, cuando cambie, toca mostrar el resumen.

        $row=Row($res);
        echo "<tr>";

        
        function filtroOcultar($columna,$primeraColumna){
                global $eligidoagrupar;
                $line = "";
                
                $ocultar = count($eligidoagrupar)>1?"oculto":"";//solo necesitamos ocultar columnas si hay filtreo por elegidoagrupar

                $m = count($eligidoagrupar);

                if(esColumnaElegidoAgrupar($columna) or !esColumnaTexto($columna) ){
                    $line .= "<td id='totalc__D_RESUMEN_DATOS_$columna' class='totalcolumna'>$primeraColumna  &nbsp; </td>";
                } else {
                    $line .= "<td id='totalc__D_RESUMEN_DATOS_$columna' class='$ocultar z_$m' width='0'></td>";
                }
                return $line;
        }

        $primeraColumna = "<span id='labelTotal'>Total:</span>";
        $t=0;
        foreach($columnas_originales as $key){
            $dato = $row[$key];
            $f = $formateadores[$t];

            if(!$f){
                echo filtroOcultar($key,$primeraColumna);
            }
            else  if(esColumnaTexto($key) and esColumnaAgrupar($key) ){
                echo filtroOcultar($key,$primeraColumna);
            } else if (esColumnaAgrupar($key))  {
                echo filtroOcultar($key,$primeraColumna);
            } else if (esColumnaTexto($key))  {
                echo filtroOcultar($key,$primeraColumna);
            } else if (!$key){ //Error?
                echo  $f($dato,$primeraColumna);
            } else if(!esColumnaTexto($key) ){
                echo  $f($dato,$primeraColumna);
            } else {
                echo filtroOcultar($key,$primeraColumna);
            }

           $t++;
           $primeraColumna = "";
        }
        echo '<td style="width:15px"> </td>';
        echo "</tr>\n";


        if(1){//debug
            echo "<div class='autoeval oculto' data-tipo='sql_mostrar_total'>".html($sql_total)."</div>";
        }

        exit();
        break;

    case "capturasubtotal":
        $salida = "";


        $formateadores = generaFormateadores($columnas_originales);

        $res = query($sql_subtotales);

        $inicia = true;
        $actual = array();//tendra el valor "actual" (viejo) del subtotal, cuando cambie, toca mostrar el resumen.

        while($row=Row($res)){
            echo "<tr>";
            $t=0;
            $m=0;
            foreach($columnas_originales as $columna){
                $f = $formateadores[$t];

                $simple = "<td>$primeraColumna  &nbsp; </td>";

                $key = $columna;
                $dato = $row[$columna];

                if(esColumnaTexto($key) and esColumnaAgrupar($key) ){
                    $val = "<span>". html8859(trim($dato)). "</span>";
                    echo  "<td>". $val ." &nbsp; </td>";
                } else if (esColumnaAgrupar($key))  {
                    $val =  "<span b>".html8859(trim($dato))."</span b>";
                    echo  "<td>". $val ." &nbsp ;</td>";
                } else if (esColumnaTexto($key))  {
                    $val =  "<i>".html8859(trim($dato)) . "</i>";
                    echo  "<td class='nomos'>". $val ." &nbsp; </td>";
                } else if (!$key){
                    $val =  "<span b>".html8859(trim($dato))." &nbsp; </span b>";
                    echo  "<td>". $val ." &nbsp; </td>";
                } else if(!esColumnaTexto($key) ){
                    $val = intval(trim($dato));


                    if($val==0 || $val=="0"){
                        if ($dato!="0" && $dato !="0.0" && $dato!="0.00")
                            $val = "";
                    }

                    $val = "<tt>$val</tt>";
                    echo  "<td data-align='right'>". $val ." &nbsp; </td>";

                } else {
                    $val = "###";
                    echo  "<td align='center'>". $val ." &nbsp; </td>";
                }


               $t++;
               $m++;
            }
            echo "</tr>\n";
        }
        //echo "<tr><td colspan='$numColumnas'><a name='base'><pre>$sql_subtotales</pre></a></td></tr>";



        exit();
        break;
    case "autoenvio":
    default:

        $_SESSION["pagina_ayuda"] = "modreporting";

        $page->readTemplatesFromInput('modreporting2.html');


        $hayComparte = false;

        $sqllist = "SELECT * FROM reporting_user_list WHERE eliminado=0 AND (id_user=0 or id_user='$id_user' or id_user IS NULL ) ORDER BY name ASC ";

        $res = query($sqllist);

        while($row = Row($res)){
            if($row["pendiente"]==1){
                $hayComparte = true;
                $usuario_comparte_id = $row["id_user_envia"];
                $nombre_sugerido = $row["name"];
                $id_reporting_user_list = $row["id_reporting_user_list"];
            }
        }


        //lista_reporting
        $sqllist = "SELECT * FROM reporting_user_list WHERE eliminado=0 AND (id_user=0 or id_user='$id_user' or id_user IS NULL ) and pendiente=0 ORDER BY name ASC ";


        $id_user = getSesionDato("id_user");

        $reportes = array();
        $reportes2 = array();
        $reportescompartidos = array();

        $res = query($sqllist);

        while($row = Row($res)){

            if (!$row["id_user"] or $row["id_user"]!=$id_user or $row["id_user"]=="0"){
                $row["readonly"] = true;

                $reportes[] = $row;
            } else if($row["id_user_envia"]){
                $id_user_s = sql($row["id_user_envia"]);
                $datos = queryrow("SELECT * FROM users WHERE id_user='$id_user_s'");

                if($datos){
                    $nombre_usuario = " - compartido por ". $datos["name"] . " " . $datos["name_s"];
                }

                $row["name"] = "".$row["name"] . " ". $nombre_usuario;// . ",[C]id_user:". $row["id_user"];
                $row["readonly"] = false;
                $reportescompartidos[] = $row;
            } else {

                $row["readonly"] = false;
                $reportes2[] = $row;
            }

            $row = null;
        }

        if($hayComparte){
            $id_user_s = sql($usuario_comparte_id);
            $row = queryrow("SELECT * FROM users WHERE id_user='$id_user_s'");
            $nombre_usuario = $row["name"] . " " . $row["name_s"];


            $page->addVar('page','nombre_usuario_comparte',$nombre_usuario);
            $page->addVar('page','nombre_informe',$nombre_sugerido);
            $page->addVar('page','id_informe_aceptar', $id_reporting_user_list);

        }else{
            $page->addVar('page','csscompartir',"oculto");
        }

        $page->addRows('lista_reporting', $reportes );
        $page->addRows('lista_reporting2', $reportes2 );
        $page->addRows('lista_reporting_compartidos', $reportescompartidos );

        if($informeSoloLectura){
            $page->addVar('page','informesololectura',"true");
        } else {
            $page->addVar('page','informesololectura',"false");
        }



        $columnasConTabla = array();
        $columnasSinTabla = array();
        if(count($columnas)>0)
        foreach($columnas as $columna){ //TODO: merge this with the other? why not?
            $columnasConTabla[] = array("tipo"=>"D_RESUMEN_DATOS_". $columna);
            $columnasSinTabla[] =  "D_RESUMEN_DATOS_" . $columna;
        }


        /* Algunos datos como fechas no se almacenan en el mismo formato que se reciben, y hace falta una re-conversion para que el usuario
         * vea lo mismo que recibio, y cuando se re-guarde el mismo dato, no se corrompa.
         * 2012-01-12 => 12-01-2012 
         */
        function escapaFiltro($tipo,$dato){

            $tipo = code2tipo($tipo);
            switch($tipo){
                case "fecha":
                    $dato = CleanFechaES($dato);//se mostrara como d-m-Y, en lugar de Y-m-d
                default:
                    break;
            }

            return $dato;
        }



        foreach($filtroParametrizado as $key=>$dato){
             //$agruparjsinvertida[] = $dato;
             //array_unshift($agruparjsinvertida,$dato);
            $out = "";
            $coma = "";
            for($t=0;$t<1000;$t++){
                if(isset($dato["param".$t])){
                    $out .= " , \"param$t\": \"".  escapaFiltro($dato["tipo"],$dato["param".$t]) ."\"";
                }
            }
            $dato["params"] = $out ;
            $filtroParametrizado[$key] =  $dato;
        }

        if($nombreListadoViendo==""){
            if($_REQUEST["nombresugerido"]){
                $nombreListadoViendo = $_REQUEST["nombresugerido"];
            }
        }

        $page->addVar('page','numcolumnas', count($columnas) );

        $page->addRows('list_agrupar_js', $agruparjs );
        $page->addRows('list_subtotal_js', $subtotaljs );

        $page->addRows('list_filtros_js', $filtroParametrizado );//was $filtrosjs
        $page->addRows('list_filtros_param_js', $filtroParametrizado );

        $page->addRows('list_columnas_js', $columnasConTabla );

        if(count($columnasSinTabla)>0) {
            $page->addRows('list_columnas_data',  join(",",$columnasSinTabla) );
        }

        $page->addRows('list_columnas', $namedColumnas );
        $page->addRows('list_columnas_icons', $namedColumnas );


        $page->addVar('page','autoenvio',1 );
        $page->addVar('page','iduser',$id_user?$id_user:0);

        $page->addRows('list_filtros_ro_js', $autofiltros );

        $page->addVar('page','nombreListado',$nombreListadoViendo);

        $page->addVar('page','random',rand());

        if($nombreListadoViendo != "")
            $page->addVar('page','conflecha', 'conflecha');

        if($modo=="loadreport")
            $page->addVar('page','idinforme',$_REQUEST["id"]);
        else
            $page->addVar('page','idinforme',0);


        $mostrar_subtotales = 0;
        $botones_crud_informe = 0;
        $agnadir_columnas_engrid = 0;
        $debug_sql = 0;
        $filtro_agnade_columna = 0;

        //error_log("Vamos a comprobar estaHabilitado");
        if(estaHabilitado("modreporting_avanzado",false)){
            $mostrar_subtotales = 1;
            $botones_crud_informe = 1;
            $agnadir_columnas_engrid = 1;
            $debug_sql = 1;
            $filtro_agnade_columna = 0;
        }

        $page->addVar('page','mostrarsubtotales',$mostrar_subtotales);
        $page->addVar('page','botonescrud',$botones_crud_informe);
        $page->addVar('page','agnadircolumnasengrid',$agnadir_columnas_engrid);
        $page->addVar('page','debugsql',$debug_sql);
        $page->addVar('page','filtroagnadecolumna',$filtro_agnade_columna);


        $sql = "SELECT  ultima_actualizacion FROM ag_frescuratablas WHERE tabla='D_RESUMEN_DATOS' ";

        $row = queryrow($sql);
        $page->addVar('page','ultima_actualizacion',$row["ultima_actualizacion"]);


        //MODOFILTRO
        $page->addVar('page','modofiltro_js',json_encode(($filtrosmodo)));


        $page->Volcar();

        $_SESSION["pagina_ayuda"] = "";


        //debug_imprimeSesion();



        exit();
        break;
}






