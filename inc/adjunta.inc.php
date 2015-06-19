<?php


        function adjunta(&$mail,$id_comm) {

            error_log("generando adjuntos para $id_comm");

            if(!$id_comm) {
                error_log("ERROR: ($id_comm) vacio");
                return "";
            }

            $sql = "SELECT email_subject,email_preview_html	FROM emails WHERE email_id_comm=$id_comm";

            $row = queryrow($sql);

            if(!$row) {
                error_log("email no encontrado:".$id_comm);
                return "";
            } else {

            }

            $html = $row["email_preview_html"];
            $html = str_replace("&nbsp;"," ",$html);
            $html = html(strip_tags($html));

            $sql = "SELECT fax_path_system,fax_id_comm  FROM faxes  WHERE fax_id_comm='$id_comm'";
            $row = queryrow($sql);

            if($row){
                $path_origin_pdf = getParametro("path_store_pdf");

                $data = array();
                $data["pdf"] = $row["fax_path_system"];//nombre del fichero pdf
                $data["path_origin_pdf"] = $path_origin_pdf; //donde residen los PDF

                $finalname = NormalizarPath($data["path_origin_pdf"] ) . $data["pdf"];

                $finalname = "/var/www/ecomm_data/ecomm_view/". $data["pdf"];

                $mail->AddAttachment($finalname,"fax.pdf");
                error_log("generando adjuntos: para $id_comm,  añadiengo(PDF): $finalname");
            } else {
                //Buscamos adjuntos en PDF para extraerles su texto
                $sql = "SELECT path_subfile,description FROM gw_email_subfiles WHERE email_id_comm='$id_comm'";
                $res = query($sql);

                while($row = Row($res)) {
                    $file = $row["path_subfile"];

                    $mail->AddAttachment($file,$row["description"]);

                    error_log("generando adjuntos: para $id_comm,  añadiengo(FILE): $pathfinal");
                }
            }



            return $html;
        }