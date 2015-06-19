<?php

/*
 * ejecuta un comando y devuelve el valor de retorno, errores y salida estandar
 * @return array
 */
function exec_wrapper($cmd, $input='') {
    $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
    
    fwrite($pipes[0], $input);fclose($pipes[0]);

    $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
    $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
    $rtn=proc_close($proc);
    return array('stdout'=>$stdout,
               'stderr'=>$stderr,
               'return'=>$rtn
              );
}