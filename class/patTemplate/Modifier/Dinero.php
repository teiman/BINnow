<?PHP
/**
 * patTemplate modfifier Truncate
 *
 * $Id: modifiers.xml 34 2004-05-11 19:46:09Z schst $
 *
 * @package        patTemplate
 * @subpackage     Modifiers
 * @author
 */

function p_CleanFloat($val) {
	$val = str_replace(",", ".", $val );
	return (float)$val;
}


function p_FormatMoney($val,$symbol=" &euro;") {
	$val = p_CleanFloat($val);
	//return htmlentities(money_format('%.2n $euro;', $val),ENT_QUOTES,'ISO-8859-15');
	//return money_format('%.2n &euro;', $val);
	return number_format($val, 2, ',', "."). $symbol;
}



class patTemplate_Modifier_Dinero extends patTemplate_Modifier
{
   /**
    * truncate the string
    *
    * @access    public
    * @param    string        value
    * @return    string       modified value
    */
    function modify( $value, $params = array() )  {
        /*
       $data = htmlentities($value,ENT_QUOTES,'UTF-8');

       if(!$data && $value){
           $data = str_replace("<","&lt;",$value);
           $data = str_replace(">","&gt;",$data);
       }*/

       if(!$value) return "";
       
       $data = p_FormatMoney($value," €");

       return $data;
    }


}


?>