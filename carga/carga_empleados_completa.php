<?php

chdir("..");


require_once("tool.php");
require_once(__ROOT__ . "/inc/migraciondatos.inc.php");
require_once(__ROOT__ . "/inc/runmeforever.inc.php");
require_once(__ROOT__ . "/inc/registro_tiempos.inc.php");
require_once(__ROOT__ . "/inc/log_cambiotablas.php");

header("Content-Type: text/plain");



terminaActividad("Inicia");

$origen = "D_empleados";
$destino = "D_RESUMEN_DATOS";
$baseorigen = "binow";


$basedestino = $ges_database;

/* ----------------------------------------------------------------- */

registra($destino . ": Se van a vaciar todos los datos en $destino");
$sql = "TRUNCATE $basedestino.$destino";
query($sql);
registra($destino . ": Se han vaciado datos ");

terminaActividad($destino . ": Vacia $destino");

/* ----------------------------------------------------------------- */

registra($destino . ": Se van a cargar todos los datos");
$sql = gen_sql_copiaDatos_completa($origen, $destino, $basedestino, $baseorigen);

query($sql);
$filas_actualizadas = $FilasAfectadas;

registra($destino . ": Se han cargado '$filas_actualizadas' filas ");

terminaActividad($destino . ": Carga Todo");

/* ----------------------------------------------------------------- */


if (1)
    anotar_tabla_actualizada($destino, "carga_" . $destino . "_tablacompleta");

volcarTiemposText();
