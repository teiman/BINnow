<?php


/*
- Tiempo medio de introduccion en jd, desde que entra al sistema
- Tiempo medio de introduccion del usuario, una vez que lo "recibe"
 
- Por usuario
- Por delegacion
- Por empresa? Global?
   
  
 * 
*/
        
        

class estadisticas {
    var $tamagnoMuestra=100;
    
    function getArrayStatusStart(){        
        $entrada = array();

        $sql = "SELECT id_status FROM `status` WHERE `default`=1  ";

        $res = query($sql);

        while($row=Row($res)){
            array_push($entrada,$row["id_status"]);
        }        
        
        return $entrada;
    }
    
    function calcular_medias_usuario($id_user,&$resultados){
        $this->creaUserSiNoExiste($id_user);        
        
        /* $media_vel_reaccion: Velocidad reacción */
        $this->media_vel_reaccion_usuario($id_user,$resultados);

        /* $media_duracion_estados: media entre cambios de estado */
        $this->media_duracion_estados($id_user,$resultados);
        
        /* $media_duracion_gestion: Tiempo tarda en gestionarse */
        $this->media_duracion_gestion($id_user,$resultados);                         
    }
 
    function calcular_medias_delegacion($id_delegacion,&$resultados){
        
        $this->creaDelegacionSiNoExiste($id_delegacion);
        
        /* $media_vel_reaccion: Velocidad reacción */
        $this->media_vel_reaccion_delegacion($id_delegacion,$resultados);                
        
        /* $media_duracion_gestion: Tiempo tarda en gestionarse */
        $this->media_duracion_gestion_delegacion($id_delegacion,$resultados);            
        
        /* $media_duracion_estados: media entre cambios de estado */
        $this->media_duracion_estados_delegacion($id_delegacion,$resultados);                
    }
    
    function calcular_medias_empresa(&$resultados){
        $this->calcular_medias_grupo("empresa",$resultados);        
    }
    
    function calcular_medias_grupo($codigo,&$resultados){
        $this->creaGrupoSiNoExiste($codigo);
        
        /* $media_vel_reaccion: Velocidad reacción */
        $this->media_vel_reaccion_grupo($codigo,$resultados);        
                
        /* $media_duracion_gestion: Tiempo tarda en gestionarse */
        $this->media_duracion_gestion_grupo($codigo,$resultados);            
        
        /* $media_duracion_estados: media entre cambios de estado */
        $this->media_duracion_estados_grupo($codigo,$resultados);                                  
    }
    
    function media_duracion_estados_grupo($codigo,&$resultados){

        $tamagnoMuestra = $this->tamagnoMuestra;
        
        $sql = "SELECT id_comm,UNIX_TIMESTAMP(date_change) as tiempo FROM trace  "
        //.   " WHERE (id_location=$id_delegacion) "
        .   " ORDER BY date_change DESC LIMIT $tamagnoMuestra";

        $ultimotiempo = 0;
        $estados = 0;
        $totaltiempo = 0;

        $res = query($sql);

        while($row= Row($res)){

            $tiempo = $row["tiempo"];
            if($ultimotiempo){
                $delta = $ultimotiempo - $tiempo;
                $totaltiempo +=  $delta;
            } else{
                $ultimotiempo = $tiempo;
            }

            $estados++;
        }

        if($estados)
            $media_duracion_estados = $totaltiempo/$estados;
        else
            $media_duracion_estados = 0;

        $cuantos_mde = $estados;       
        
        $resultados["media_duracion_estados"] = $media_duracion_estados;                                      
    }
            
    function media_duracion_gestion_grupo($codigo,&$resultados){
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        $estados_sql = implode(",",$entrada);
        
        $cuantos = 0;
        $tiempototal = 0;

        $sql = "SELECT  UNIX_TIMESTAMP(date_change) as tiempoinicial, trace.id_comm as id_comm FROM trace  "
        .   " WHERE (trace.id_status IN ($estados_sql)) "
        .   " ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        echo "\n".$sql ."\n";

        $res = query($sql);

        while($row = Row($res)){

            $id_comm = $row["id_comm"];

            $sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY date_change DESC LIMIT 1";
            //$sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY id_trace DESC LIMIT 1";//no se puede usar id_trace porque ha habido deletes y no sigue un orden cronologico
            $data = queryrow($sql);

            if($data["tiempofinal"]){
                //Comparamos
                $diferencia = $data["tiempofinal"] - $row["tiempoinicial"];

                $tiempototal = $tiempototal + $diferencia;
                $cuantos++;
             }
        }

        if($cuantos)
            $media_duracion_gestion = $tiempototal / $cuantos;
        else
            $media_duracion_gestion = 0;

        $gestionados_evaluados = $cuantos;

        $resultados["media_duracion_gestion"] = $media_duracion_gestion;        
    }    
    
            
    function media_vel_reaccion_grupo($codigo,&$resultados){
        
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        
        $segmento_temporal = " year(date_change)=2012  ";
        $estados_sql = implode(",",$entrada);

        $sql = "SELECT  date_change,date_cap,AVG(TIMESTAMPDIFF(MINUTE,date_cap,date_change)) media, trace.id_comm FROM trace JOIN communications ON trace.id_comm = communications.id_comm "
        .   " WHERE (trace.id_status IN ($estados_sql)) ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        $row = queryrow($sql);

        $resultados["media_vel_reaccion"] = intval($row["media"]);
    }
                
    function creaDelegacionSiNoExiste($id_delegacion){
        $id_delegacion_s = sql($id_delegacion);

        $sql = "SELECT id_delegacion FROM estadisticas_delegacion WHERE id_delegacion='$id_delegacion_s' ";

        $row = queryrow($sql);

        if(!$row){ //si no existe, se da de alta    
            $sql = "INSERT INTO estadisticas_delegacion (id_delegacion) VALUES( '$id_delegacion_s')";
            query($sql);                    
        }              
    }
    
    function media_vel_reaccion_delegacion($id_delegacion,&$resultados){
        
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        
        $segmento_temporal = " year(date_change)=2012  ";
        $estados_sql = implode(",",$entrada);

        $sql = "SELECT  date_change,date_cap,AVG(TIMESTAMPDIFF(MINUTE,date_cap,date_change)) media, trace.id_comm FROM trace JOIN communications ON trace.id_comm = communications.id_comm "
        .   " WHERE (trace.id_location='$id_delegacion') and (trace.id_status IN ($estados_sql)) ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        $row = queryrow($sql);

        $resultados["media_vel_reaccion"] = intval($row["media"]);
    }

    
    
    function media_duracion_gestion_delegacion($id_delegacion,&$resultados){
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        $estados_sql = implode(",",$entrada);
        
        $cuantos = 0;
        $tiempototal = 0;

        $sql = "SELECT  UNIX_TIMESTAMP(date_change) as tiempoinicial, trace.id_comm as id_comm FROM trace  "
        .   " WHERE (id_location=$id_delegacion) and (trace.id_status IN ($estados_sql)) "
        .   " ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        echo "\n".$sql ."\n";

        $res = query($sql);

        while($row = Row($res)){

            $id_comm = $row["id_comm"];

            $sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY date_change DESC LIMIT 1";
            //$sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY id_trace DESC LIMIT 1";//no se puede usar id_trace porque ha habido deletes y no sigue un orden cronologico
            $data = queryrow($sql);

            if($data["tiempofinal"]){
                //Comparamos
                $diferencia = $data["tiempofinal"] - $row["tiempoinicial"];

                $tiempototal = $tiempototal + $diferencia;
                $cuantos++;
             }
        }


        if($cuantos)
            $media_duracion_gestion = $tiempototal / $cuantos;
        else
            $media_duracion_gestion = 0;

        $gestionados_evaluados = $cuantos;

        $resultados["media_duracion_gestion"] = $media_duracion_gestion;
        
    }    
    
    function media_duracion_estados_delegacion($id_delegacion,&$resultados){

        $tamagnoMuestra = $this->tamagnoMuestra;
        
        $sql = "SELECT id_comm,UNIX_TIMESTAMP(date_change) as tiempo FROM trace  "
        .   " WHERE (id_location=$id_delegacion) "
        .   " ORDER BY date_change DESC LIMIT $tamagnoMuestra";

        $ultimotiempo = 0;
        $estados = 0;
        $totaltiempo = 0;

        $res = query($sql);

        while($row= Row($res)){

            $tiempo = $row["tiempo"];
            if($ultimotiempo){
                $delta = $ultimotiempo - $tiempo;
                $totaltiempo +=  $delta;
            } else{
                $ultimotiempo = $tiempo;
            }

            $estados++;
        }

        if($estados)
            $media_duracion_estados = $totaltiempo/$estados;
        else
            $media_duracion_estados = 0;

        $cuantos_mde = $estados;       
        
        $resultados["media_duracion_estados"] = $media_duracion_estados;        
    }
    
    
    function media_duracion_gestion($id_user,&$resultados){
        
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        $estados_sql = implode(",",$entrada);
     
        $cuantos = 0;
        $tiempototal = 0;

        $sql = "SELECT  UNIX_TIMESTAMP(date_change) as tiempoinicial, trace.id_comm as id_comm FROM trace  "
        .   " WHERE (id_user=$id_user) and (trace.id_status IN ($estados_sql)) "
        .   " ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        echo "\n".$sql ."\n";

        $res = query($sql);

        while($row = Row($res)){

            $id_comm = $row["id_comm"];

            $sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY date_change DESC LIMIT 1";
            //$sql = "SELECT UNIX_TIMESTAMP(date_change) as tiempofinal FROM trace WHERE id_comm='$id_comm'  ORDER BY id_trace DESC LIMIT 1";//no se puede usar id_trace porque ha habido deletes y no sigue un orden cronologico
            $data = queryrow($sql);

            if($data["tiempofinal"]){
                //Comparamos
                $diferencia = $data["tiempofinal"] - $row["tiempoinicial"];

                $tiempototal = $tiempototal + $diferencia;
                $cuantos++;
             }
        }


        if($cuantos)
            $media_duracion_gestion = $tiempototal / $cuantos;
        else
            $media_duracion_gestion = 0;

        $gestionados_evaluados = $cuantos;

        $resultados["media_duracion_gestion"] = $media_duracion_gestion;
    }
    

    function media_vel_reaccion_usuario($id_user,&$resultados){
        
        $tamagnoMuestra = $this->tamagnoMuestra;        
        $entrada = $this->getArrayStatusStart();
        
        $segmento_temporal = " year(date_change)=2012  ";
        $estados_sql = implode(",",$entrada);

        $sql = "SELECT  date_change,date_cap,AVG(TIMESTAMPDIFF(MINUTE,date_cap,date_change)) media, trace.id_comm FROM trace JOIN communications ON trace.id_comm = communications.id_comm "
        .   " WHERE (id_user=$id_user) and (trace.id_status IN ($estados_sql)) ORDER BY id_trace DESC LIMIT $tamagnoMuestra";

        $row = queryrow($sql);

        $resultados["media_vel_reaccion"] = intval($row["media"]);
    }

    
    function media_duracion_estados($id_user,&$resultados){

        $tamagnoMuestra = $this->tamagnoMuestra;
        
        $sql = "SELECT id_comm,UNIX_TIMESTAMP(date_change) as tiempo FROM trace  "
        .   " WHERE (id_user=$id_user) "
        .   " ORDER BY date_change DESC LIMIT $tamagnoMuestra";

        $marca = 0;
        $ultimotiempo = 0;
        $estados = 0;
        $totaltiempo = 0;

        $res = query($sql);

        while($row= Row($res)){

            $tiempo = $row["tiempo"];
            if($ultimotiempo){
                $delta = $ultimotiempo - $tiempo;
                $totaltiempo +=  $delta;
            } else{
                $ultimotiempo = $tiempo;
            }

            $estados++;
        }

        if($estados)
            $media_duracion_estados = $totaltiempo/$estados;
        else
            $media_duracion_estados = 0;

        $cuantos_mde = $estados;       
        
        $resultados["media_duracion_estados"] = $media_duracion_estados;        
    }
    
    function actualizaDatosUsuario($id_user,&$resultados){
        $id_user_s = sql($id_user);
        
        $media_vel_reaccion = $resultados["media_vel_reaccion"];
        $media_duracion_gestion = $resultados["media_duracion_gestion"];
        $media_duracion_estados = $resultados["media_duracion_estados"];

        $sql = "UPDATE estadisticas_usuarios SET media_vel_reaccion='$media_vel_reaccion' "
        .   " ,media_duracion_gestion='$media_duracion_gestion'"
        .   " ,media_duracion_estados='$media_duracion_estados'"        
        .   " WHERE id_user='$id_user_s'"       
                ;

        query($sql);                        
    }
    
    
    function actualizaDatosGrupo($codigo,&$resultados){
        $codigo_s = sql($codigo);
        
        $media_vel_reaccion = $resultados["media_vel_reaccion"];
        $media_duracion_gestion = $resultados["media_duracion_gestion"];
        $media_duracion_estados = $resultados["media_duracion_estados"];

        $sql = "UPDATE estadisticas_grupo SET media_vel_reaccion='$media_vel_reaccion' "
        .   " ,media_duracion_gestion='$media_duracion_gestion'"
        .   " ,media_duracion_estados='$media_duracion_estados'"        
        .   " WHERE codigo='$codigo_s'" ;

        query($sql);                        
    }
        
    
    function creaUserSiNoExiste($id_user){
        $id_user_s = sql($id_user);

        $sql = "SELECT id_user FROM estadisticas_usuarios WHERE id_user='$id_user_s' ";

        $row = queryrow($sql);

        if(!$row){ //si no existe, se da de alta    
            $sql = "INSERT INTO estadisticas_usuarios (id_user) VALUES( '$id_user')";
            query($sql);                    
        }              
    }
            
    
    function creaGrupoSiNoExiste($codigo){
        
        /* grupos son "conjuntos" de los que sacamos estadistica. Un grupo basico puede ser todaslasempresasjuntas   */
        
        $sql = "SELECT id_grupo FROM estadisticas_grupo WHERE codigo='$codigo' LIMIT 1";

        $row = queryrow($sql);

        if(!$row){ //si no existe, se da de alta    
            $sql = "INSERT INTO estadisticas_grupo (codigo) VALUES( '$codigo')";
            query($sql);                    
        }                
    }    
}