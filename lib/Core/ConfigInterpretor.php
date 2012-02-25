<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core
{
    use \XTemplate\Engine;
    /**
     * This is the base class for all config interpretors. These interpretors receive a certainly named entry from the
     * config file and can work with it. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    abstract class ConfigInterpretor extends EnginePartImpl
    {
        /**
         * The config entry
         * @var XTemplate\Core\XML
         */
        protected $objXML = null;
        /**
         * Origin config instance
         * @var XTemplate\Core\Config
         */
        protected $objConfig = null;
        
        /**
         * Creates a new interpretor
         * 
         * @param      XTemplate\Core\XML      $objEntry       The config entry
         * @param      XTemplate\Core\Config   $objConfig      Origin config object
         */
        public final function __construct(XML $objEntry, Config $objConfig)
        {
            $this->objXML = $objEntry;
            $this->objConfig = $objConfig;
            
            $this->setEngineInstance($objConfig->getEngineInstance());
            
            // Parse the entry
            $this->parse();
        }
        
        /**
         * Parses the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected abstract function parse();
    }
}
?>