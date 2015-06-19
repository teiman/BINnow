<?php

/**
 * MS-Excel stream handler
 * Excel download example
 * @author      Ignatius Teo            <ignatius@act28.com>
 * @copyright   (C)2004 act28.com       <http://act28.com>
 * @date        21 Oct 2004
 */
require_once "excel.php";
$export_file = "xlsfile://tmp/example.xls";
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/x-msexcel");
header("Content-Disposition: attachment; filename=\"" . basename($export_file) . "\"");
header("Content-Description: PHP/INTERBASE Generated Data");
readfile($export_file);
exit;
?>
