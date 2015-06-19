<?php

/**
 *
 * @package binow
 */

include("tool.php");


setcookie("TestCookie", "");//hace un cambio para forzar un push
session_unset();
session_destroy();

header("Location: login.php");

