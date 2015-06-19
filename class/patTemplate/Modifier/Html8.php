<?PHP
/**
 * patTemplate modfifier Html-8
 *
 * Muestra texto en una pagina en UTF-8. El texto es escapado 
 *
 * $Id: modifiers.xml 34 2004-05-11 19:46:09Z schst $
 *
 * @package        patTemplate
 * @subpackage     Modifiers
 * @author
 */
class patTemplate_Modifier_Html8 extends patTemplate_Modifier
{
   /**
    * truncate the string
    *
    * @access    public
    * @param    string        value
    * @return    string       modified value
    */
    function modify( $value, $params = array() )  {
       $data = htmlentities($value,ENT_QUOTES,'UTF-8');

       if(!$data && $value){
           $data = str_replace("<","&lt;",$value);
           $data = str_replace(">","&gt;",$data);
       }
       
       return $data;
    }


}


?>