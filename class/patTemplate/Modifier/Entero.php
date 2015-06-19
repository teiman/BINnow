<?php

/**
 * patTemplate modfifier Entero
 *
 * $Id: modifiers.xml 34 2004-05-11 19:46:09Z schst $
 *
 * @package        patTemplate
 * @subpackage     Modifiers
 * @author
 */


class patTemplate_Modifier_Entero extends patTemplate_Modifier {

    /**
     * truncate the string
     *
     * @access    public
     * @param    string        value
     * @return    string       modified value
     */
    function modify($value, $params = array()) {
        
        if ($value==="")
            return "";

        $data = intval($value);

        return $data;
    }

}

