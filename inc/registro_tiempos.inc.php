<?php

$registro_tiempos = array();

$cargaPagina = microtime(true);



function terminaActividad($name){
    global $registro_tiempos;

    $item = array("name"=>$name,"tiempo"=>microtime(true));

    $registro_tiempos[]= $item;         
}


function volcarTiemposText(){
    global $registro_tiempos,$cargaPagina;

    $actividadEnProceso = "Inicia";
    $actividadt0 = $cargaPagina;

    foreach($registro_tiempos as $key=>$item){
        $t1 = $item["tiempo"];
        $tiempo = $t1 - $actividadt0;

        $tarea = $item["name"];
        $tarea = str_pad($tarea, 30 , "           ");
        echo "[$tarea] ".number_format($tiempo,20) ." s\n";

        $actividadt0 = $t1;
    }

    //print_r($registro_tiempos);
}


function enviarEmailAdmin($mensaje="corre:enviarEmailAdmin"){
    global $registro_tiempos;
    
    include_once('class/51/class.phpmailer.php');
    include_once('class/51/class.smtp.php');    

    $to = "oscar.vives@gmail.com";
    
    $mail = new PHPMailer();

    $mail->From       = "nocontestar@binow.es";
    $mail->FromName   = "enviarEmailAdmin";

    $marca = date("Ymd H:m:s");

    $mail->Subject    = "enviarEmailAdmin:[$marca]$mensaje";

    $mail->IsHTML(true);
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

    $mail->MsgHTML( $mensaje  . str_replace("\n","<br>",var_export($registro_tiempos,true)) );

    $mail->AddAddress($to, "");
    $mail->From       = "sistemabinowplus@binow.es";
    //$mail->AddReplyTo("binow@binow.es","");
    //$mail->AddReplyTo("binow@binow.es","");

    $mail->Username = "pedidos@binow.es";
    $mail->Password = "binow123";
    $mail->IsSMTP();								

    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = "tls";			
    $mail->Host       = "smtp.gmail.com";

    $mail->Port       = 587;

    $mail->Send();                                        
}

function enviarEmailUser($user,$mensaje="corre:enviarEmailAdmin"){
    global $registro_tiempos;
    
    include_once('class/51/class.phpmailer.php');
    include_once('class/51/class.smtp.php');    

    $to = $user;
    
    $mail = new PHPMailer();

    $mail->From       = "nocontestar@binow.es";
    $mail->FromName   = "enviarEmailAdmin";

    $marca = date("Ymd H:m:s");

    $mail->Subject    = "[901+] Actividad pasarelas";

    $mail->IsHTML(true);
    $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

    $mail->MsgHTML( $mensaje  );

    $mail->AddAddress($to, "");
    $mail->From       = "sistemabinowplus@binow.es";

}

