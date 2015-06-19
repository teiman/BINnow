<?php

/**
 * Logout
 * @package binow
 */


include("tool.php");

setcookie("buscacampo", "");//resetea expresion de busqueda

header("Location: logout_raw.php");

