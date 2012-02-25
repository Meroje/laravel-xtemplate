<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core\ConfigInterpretors
{
    use \XTemplate\Core\ConfigInterpretor;
    use \XTemplate\Core\Exceptions\ConfigException;
    
    /**
     * This is the config interpretor to create autoloaders from the configuration files. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Autoloader extends ConfigInterpretor
    {
        /**
         * Autoloader instance
         * @var XTemplate\Core\Autoloader
         */
        private $objAutoloader = null;
        
        /**
         * Parse the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected function parse()
        {
            // Create a new autoloader
            try 
            {
                $this->objAutoloader = new \XTemplate\Core\Autoloader();
                $this->objAutoloader->loadXML($this->objXML);
            }
            catch (\Exception $objException)
            {
                // This method must only throw ConfigException
                // So we catch all other exceptions and convert them into a ConfigException
                throw new ConfigException($objException->getMessage(), $objException->getCode(), $objException->getPrevious());
            }
        }
    }
}
?>