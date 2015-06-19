<?PHP
/**
 * patTemplate modfifier Truncate
 *
 * $Id: modifiers.xml 34 2004-05-11 19:46:09Z schst $
 *
 * @package        patTemplate
 * @subpackage     Modifiers
 * @author        Stephan Schmidt <schst@php.net>
 */
class patTemplate_Modifier_Checkbox extends patTemplate_Modifier
{
   /**
    * truncate the string
    *
    * @access    public
    * @param    string        value
    * @return    string       modified value
    */
    function modify( $value, $params = array() )
    {
        /**
         * no length specified
         */
        if( !$value or $value==0 or $value=="0" )
			return "";

        return " checked='checked' ";
    }
}