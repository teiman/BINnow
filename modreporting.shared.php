<?php


/**
 *  Extras del explorador de datos
 * @package binow
 */

/*
 * Cargamos data desde request, para luego poder modificarla sin que el
 * resto del programa lo note. Y asi poderle alimentar de cualquier input
 * que necesitemos.
 */



/*
 * Se recogen los datos de filtro y se ponen en una forma organizada
 * trabaja con COLUMNAS
 * -----------------------------------------------------------------------------------------
 */

$columnas = limpiarColumnas($COLUMNAS);

/*
 * Procesado de AGRUPAMIENTOS, limpia datos de entrada
 */


$agrupar = limpiarAgrupamientos($AGRUPAMIENTOS);


/*
 * Procesado de AVERAGE, limpia datos de entrada
 */

$average = limpiaAverage($AVERAGE);

/*
 * Procesado de FILTROS
 * -----------------------------------------------------------------------------------------
 */

$tmp = limpiarFiltros($FILTROS,"D_RESUMEN_DATOS");
$tmpf = limpiaFiltroParametrizado($tmp);
$filtroParametrizado = combinaFiltros($filtroParametrizado,$tmpf);


$filtroParametrizado = combinaFiltros($autofiltros, $filtroParametrizado);


$res = prepara2js($agrupar);

$parteGroup .= $res["parteGroup"];
$agruparjs = $res["agruparjs"];



$filtrosmodo = limpiaFiltrosModo($MODOSFILTRO);


/*
 *  Procesado de SUBTOTALES
 * -----------------------------------------------------------------------------------------
 */


$subtotal = limpiaSubtotal($SUBTOTALES);


$res = preparaSubtotalYOrders($subtotal,"D_RESUMEN_DATOS");

$subtotaljs = $res["subtotaljs"];
$subtotalsql = $res["subtotalsql"];
$orderbysubtotal = $res["orderbysubtotal"];
$subtotalGroupby = $res["subtotalGroupby"];

//error_log("subtotalGroupby:".$subtotalGroupby);


$parteSelect = join(",",$columnas);//TODO: ckin??


/*
 * Se usan los datos de filtro para preparar las partes del SQL
 * -----------------------------------------------------------------------------------------
 */


$res = buildWhere($filtroParametrizado,$filtrosmodo,"D_RESUMEN_DATOS");


$parteWhere = $res["parteWhere"];


/* ----------------------------------------------------------------------------------------- */

/*
 * Gestiona la dirección, y el campo por el que se quiere ordenar
 */


if(!$parteOrder and !$orderbysubtotalORDERBY and !$orderbysubtotal){

    if(isset($_REQUEST["ordenarpor"])){

        $dato = $_REQUEST["ordenarpor"];
        $v = validaFiltro($dato);

        if($v){
            $direccion = $_REQUEST["ordenarpor_direccion"]=="up"?"ASC":"DESC";

            $parteOrder = " $v $direccion  ";

            //error_log("ORDENbtn: Success:parteOrder:$parteOrder");
        } else {
           // error_log("ORDENbtn: dato:$dato es desconocido");
        }

    }  else {
        //error_log("ORDENbtn: no encuentro motivos para ordenar");
    }
} else {

    //error_log("ORDENbtn: ya hay un filtro de ordenar:!$parteOrder and !$orderbysubtotalORDERBY and !$orderbysubtotal");
}

/* ----------------------------------------------------------------------------------------- */


$_eligidoagrupar = $_REQUEST["eligidoagrupar"];
$_eligidoagrupar = explode(",",$_eligidoagrupar);

$eligidoagrupar = array("");

foreach($_eligidoagrupar as $elemento){
    $v = validaFiltro($elemento);

    if($v) {
        array_push($eligidoagrupar,$v);
    } else {

    }
}



/* ----------------------------------------------------------------------------------------- */


if( $modo=="init" || $modo=="self") {
 //Inicia
}


/*
 * Forzando ajustes correctos. Si hay un where, tiene que haber clausula where. Si hay un order, ...blablalbla. Y asi lo demas.
 *
 */


$parteOrder = ($orderbysubtotal)?$orderbysubtotal:$parteOrder;//NOTA: humm?


if (!$parteSelect) $parteSelect = " * ";

if ($parteWhere) $WHERE = " WHERE ";

$WHERE = $res["cuandos"]>0?" WHERE ":$WHERE;

$GROUP_BY = (count($agruparjs)>0 or $GROUP_BY)?" GROUP BY ":"";
$GROUPBYSUBTOTAL = (count($subtotaljs)>0)?"GROUP BY":"";

//$orderbysubtotalORDERBY = count($subtotaljs)>0?" ORDER BY ":"";

if ($parteOrder or $orderbysubtotalORDERBY or $orderbysubtotal){
    $ORDER_BY = " ORDER BY ";
}


/* ------------------------------ */

/* Terminan las operaciones normales, y empiezan las del sistema acumulador */

/*
 * Limpia los datos de entrada de acumuladores, para que se aseguren que son validos
 *
 */

$acumuladores = limpiarColumnas($ACUMULADORES);


/*

 P1: select generico
 "SELECT {lo de siempre},"

 P2: fna de acumulado
@fna:=
IF (
	foreach( campo){
	  @prev_{campo1}!= {campo1} or
	}
	, {campo inventado1},
	 @fna + {campo inventado1}
) As {campo inventado1}_acum,

 P3: prev de campos
foreach( campo){
	@prev_{campo1}:= {campo1} AS prev_{campo1},
}

 P4: cuerpo de from, para subselect generico
FROM   (
           	SELECT {campo1} , {campo2} , {campo3} , {campo4} , SUM({campo acumulado1}) AS {campo inventado1}
	FROM D_RESUMEN_DATOS
            WHERE
           	{ lo de siempre }
	GROUP BY {lo de siempre}  LIMIT 0,3000

) AS agrupados,

 P5: reinicio de variables.

(
	SELECT @fna:=0,

	foreach( campo ){
	      @prev_{campo1}:= 0,
	}
) as vars;
*/

/* Inicializa */

$columnas_genericas = $columnas;

$columnas_insignificantes = array();//todas excepto la/s columna/s acumulada/s, y el criterio de acumulacion


$c = count($agrupar);
$ultimo_agrupar = $agrupar[$c-1];
$ultimo_agrupar = validaFiltro($ultimo_agrupar);

foreach($columnas_genericas as $colum){        
    if(!es_columna_acumulador($colum)){
        if($colum != $ultimo_agrupar)
            array_push($columnas_insignificantes,$colum);
    }
}


/* Preparamos P1 */


$sql_listacolumnas = join(",",$columnas_genericas);

$sql_p1 = "SELECT $sql_listacolumnas, ";


/* Preparamos P2 */


$fragmentos = array();
$t=0;

if($acumuladores)
foreach($acumuladores as $acum ){

    $c = array();
    foreach($columnas_insignificantes as $col){
        array_push($c,"@prev_".$col."!= " . $col);
    }
    $sql_p2_2 = join(" or ",$c);

    $fragmento = "@fna".$t.":=
        IF (
                $sql_p2_2
                , $acum ,
                 @fna$t + $acum
        ) AS ".$acum."_acum ";
    array_push($fragmentos,$fragmento);
}

$sql_p2 = join(",",$fragmentos) .",";


/* Preparamos P3 */

$fragmentos = array();
$t=0;
foreach($columnas_insignificantes as $col ){
    array_push($fragmentos,"@prev_".$col.":=".$col . " AS prev_". $col);
}
$sql_p3 = join(",",$fragmentos);

/* Preparamos P4 */

$hayAgrupar = count($agruparjs);

$parteSelectSubtotal2 = uniendo_columnas_select($columnas,$average,$hayAgrupar);

$sql_nucleo = "SELECT $parteSelectSubtotal2 \n"
.    " FROM D_RESUMEN_DATOS \n"
.    " $WHERE $parteWhere \n"
.    " $GROUP_BY $parteGroup \n"
.    " $ORDER_BY $parteOrder $parteLimit\n";


$sql_p4 = " FROM ( $sql_nucleo  ) AS agrupados,";


/* Preparamos P5 */


$andand = "";

$f = array();$t=0;
if($acumuladores)
foreach($acumuladores as $col){
    array_push($f," @fna".$t.":=0");$t++;
}
$campos_fna_reseteados = join(",",$f);


$f = array();
foreach($columnas_insignificantes as $col){
    array_push($f," @prev_".$col.":=0");$t++;
}

$camposnormales_reseteados = join(",",$f);


if($campos_fna_reseteados and $camposnormales_reseteados)
    $andand = ",";

$sql_p5 = "
(
    SELECT $campos_fna_reseteados $andand
    $camposnormales_reseteados
) as vars;
";


/* Debug*/

$sql_final_acum = "$sql_p1 $sql_p2 $sql_p3 $sql_p4 $sql_p5";

