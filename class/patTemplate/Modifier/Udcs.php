<?PHP

/**
 * patTemplate modifier UDCS
 *
 *
 * Saca un codigo de la base de datos
 *
 * $Id: modifiers.xml 34 2004-05-11 19:46:09Z schst $
 *
 * @package        patTemplate
 * @subpackage     Modifiers
 * @author
 */
class patTemplate_Modifier_Udcs extends patTemplate_Modifier {

    /**
     * truncate the string
     *
     * @access    public
     * @param    string        value
     * @return    string       modified value
     */
    function modify($value, $params = array()) {

        $key_s = sql(strtoupper($params["key"]));

        if ($key_s == "MACRO_YN") {
            $value = strtolower($value);
            $value = ($value == "y") ? "si" : "no";
            return $value;
        }
        
        if($key_s=="MACRO_ACTIVOINACTIVO"){                                    
            if($value ==="") return "";
            
            $value = ($value == "1") ? "inactivo":"activo";
            return $value;            
        }
        
        //error_log("key:$key_s");
        
        if ($key_s) {

            $value_s = sql($value);
            $sql = "SELECT DRDL01 as dato FROM D_UDCs WHERE FRDTAI='$key_s' AND DRKY='$value_s' LIMIT 1 ";
            $row = queryrow($sql);

            if ($row){
                $value = $row["dato"];
            } else {                
                $sql = "SELECT DRDL01 as dato FROM D_UDCs WHERE FRDTAI='".$key_s."' AND TRIM(DRKY) LIKE '".$value_s."' LIMIT 1 ";
                $row = queryrow($sql);                
                
                if($row)
                    $value= $row["dato"];                
                else {
                    error_log("UDCS[error], para key:$key_s,value:'$value_s',sql:$sql");
                    
                    $sql = "SELECT DRDL01 as dato FROM D_UDCs WHERE FRDTAI='".$key_s."' AND TRIM(DRKY) LIKE TRIM('".$value_s."') LIMIT 1 ";
                    $row = queryrow($sql);                    
                    
                    //SELECT DRDL01 as dato FROM D_UDCs WHERE FRDTAI='CTR' AND TRIM(DRKY) LIKE 'ES '
                    if($row){                        
                        $value = $row["dato"];
                    }
                    
                }
            }                                    
        }

        //die($sql);

        $value = iconv("ISO-8859-1", "UTF8", $value);

        $data = htmlentities($value, ENT_QUOTES, 'UTF-8');

        if (!$data && $value) {
            $data = str_replace("<", "&lt;", $value);
            $data = str_replace(">", "&gt;", $data);
        }

        return $data;
    }

}

