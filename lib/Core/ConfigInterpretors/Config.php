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
    
    /**
     * This interpretor allows you to place a config entry in an already existing config file. So you are able to have
     * different configurations for different domains. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Config extends ConfigInterpretor
    {
        /**
         * Parse the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected function parse()
        {
            // Create the config object
            $objConfig = new \XTemplate\Core\Config($this->objXML, $this->getEngineInstance());
            
            // Extend the current configuration to the new instance
            $this->objConfig->extend($objConfig);
        }
    }
}
?>