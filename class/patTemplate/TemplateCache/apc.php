<?php


/**
 * patTemplate Template cache that stores data in the APC Cache
 *
 * If the lifetime is set to auto, the cache files will be kept until
 * you delete them manually.
 *
 *
 * @package		patTemplate
 * @subpackage	Caches
 */
class patTemplate_TemplateCache_apc extends patTemplate_TemplateCache
{
   /**
	* parameters of the cache
	*
	* @access	private
	* @var		array
	*/
   var $_prefixapc = "template_";
   var $_params = array( 'lifetime' => 'auto');

   /**
	* load template from cache
	*
	* @access	public
	* @param	string			cache key
	* @param	integer			modification time of original template
	* @return	array|boolean	either an array containing the templates or false cache could not be loaded
	*/
    function load( $key, $modTime = -1 )
    {
        if (!function_exists('apc_fetch')) {
            return false;
        }

        $something = apc_fetch($this->apckey($key));
        if (is_null($something)){
            return false;
        }else{
            return unserialize($something);
        }
    }

   /**
	* write template to cache
	*
	* @access	public
	* @param	string		cache key
	* @param	array		templates to store
	* @return	boolean		true on success
	*/
    function write( $key, $templates )
    {
        if (!function_exists('apc_store')) {
            return false;
        }


        //error_log("[APC] write:$key");

        //mmcache_lock($key);
        if ($this->getParam( 'lifetime' ) == 'auto'){
            apc_store($this->apckey($key), serialize( $templates ));
        }else{
            apc_store($this->apckey($key), serialize( $templates ), $this->getParam( 'lifetime' ) * 60);
        }
        //mmcache_unlock($key);

        return true;
   }

   function apckey($key){
       return $this->_prefixapc . $key;
   }
}