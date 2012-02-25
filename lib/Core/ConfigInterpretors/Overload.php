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
     * This is the config interpretor registrates class overloads. You are not able to overload any of the core classes. 
     * But you can overload any other class of the engine. This gives you a greate flexibility while working on custom
     * solutions. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Overload extends ConfigInterpretor
    {
        /**
         * Parse the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected function parse()
        {
            $objEngine = $this->getEngineInstance();
            
            // Registrate all overloads at the engine instance
            foreach ($this->objXML->children() as $objEntry)
            {
                // Get the classname
                $strClassName = $objEntry->getAttribute("name");
                $strNewClassName = (string) $objEntry;
                
                // Registrate the overload
                $objEngine->registrateClassOverload($strClassName, $strNewClassName);
            }
        }
    }
}
?>