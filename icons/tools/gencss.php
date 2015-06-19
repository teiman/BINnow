<?php
include("iconlist.php");


$n = 0;
foreach ($iconos as $icon) {
    $icon_name = str_replace(".gif", "", $icon);
    ?>
    .ik_<?php echo $icon_name ?> {
    background-image: url(icons/fila.gif);
    background-position:  <?php echo ( (96 - $n) * 16); ?>px 0px;
width: 16px;
height: 16px;
}

<?
$n++;
}

echo $n;

echo "\n\n-----";

?>