<?php

class patTemplate_Function_Session extends patTemplate_Function
{
   /**
    * name of the function
    * @access   private
    * @var      string
    */
    var $_name  =   'Session';

    
   /**
    * call the function
    *
    * @access   public
    * @param    array   parameters of the function (= attributes of the tag)
    * @param    string  content of the tag
    * @return   string  content to insert into the template
    */ 
    function call( $params, $content )
    {        
        return $_SESSION[$params["var"]];
    }
}


?>