<?PHP
/**
 * patTemplate modfifier Numberformat
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate modfifier Formatexto
 *
 * formats textos si presentes
 *
 *
 * See the PHP documentation for number_format() for
 * more information.
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 */
class patTemplate_Modifier_Formatexto extends patTemplate_Modifier{
   /**
	* modify the value
	*
	* @access	public
	* @param	string		value
	* @return	string		modified value
	*/
	function modify($value, $params = array()){

            if(!$value || $value==="0")
                return "";
            
            $texto = sprintf($params["salida"],$value);

            return $texto;
        }
}
