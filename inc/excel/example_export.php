<?php

/**
 * MS-Excel stream handler
 * Excel export example
 * @author      Ignatius Teo            <ignatius@act28.com>
 * @copyright   (C)2004 act28.com       <http://act28.com>
 * @date        21 Oct 2004
 */
require_once "excel.php";
$export_file = "xlsfile://tmp/example.xls";
$fp = fopen($export_file, "wb");
if (!is_resource($fp)) {
    die("Cannot open $export_file");
}

// typically this will be generated/read from a database table
$assoc = array(
    array("Sales Person" => "Sam Jackson", "Q1" => "$3255", "Q2" => "$3167", "Q3" => 3245, "Q4" => 3943),
    array("Sales Person" => "Jim Brown", "Q1" => "$2580", "Q2" => "$2677", "Q3" => 3225, "Q4" => 3410),
    array("Sales Person" => "John Hancock", "Q1" => "$9367", "Q2" => "$9875", "Q3" => 9544, "Q4" => 10255),
);

fwrite($fp, serialize($assoc));
fclose($fp);
?>