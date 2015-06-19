<?php


function crear_path_distributivo(){
     //$base = getcwd().'/documentos';

     $base  = getParametro("binow.carpeta_adjuntos_incidencias");

     $carpeta = "";
     $md5 = md5( rand() );
     $agno = date("Y");

     $carpeta = $carpeta . "/" . $agno;

     
     mkdir($base. $carpeta,0777,true);//crea las carpetas recursivamente
     chmod($base. $carpeta,0777);
     

     $carpeta =  $carpeta . "/" . substr($md5,0,2);

     
     mkdir($base. $carpeta,0777,true);//crea las carpetas recursivamente
     chmod($base. $carpeta,0777);
     
     $fichero = $carpeta . "/" .  substr($md5,2);
     

     $path_absoluto = $base .$fichero;
     
     return array("path"=>$path_absoluto, "relativo"=>$fichero);
}


