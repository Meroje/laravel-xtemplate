<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core
{
    use XTemplate\Engine;
    
    /**
     * This is the default implementation of the enginepart interface. Whenever a class can be extended from this class
     * it should be done. Otherway you always had to implement the interface on your own. The class also provides static
     * helper function to simply the implementation of the interface. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    abstract class EnginePartImpl implements Interfaces\EnginePart
    {
        /**
         * Engine instance
         * @var XTemplate\Engine
         */
        private $objEngine = null;

        /**
         * Sets the engine instance
         * 
         * @param       XTemplate\Engine            $objEngine      The engine instance
         * @throws      CoreException
         */
        public function setEngineInstance(Engine $objEngine = null)
        {
            // You cannot overwrite an existing engine instance
            if ($this->objEngine !== null && $objEngine !== null)
            {
                throw new Exceptions\CoreException("You cannot change the engine instance of an existing object.");
            }
            
            // If there is no engine instance given, select the default instance
            if ($objEngine === null)
            {
                $objEngine = Engine::getDefaultInstance();
            }
            
            $this->objEngine = $objEngine;
        }
        
        /**
         * Returns the engine instance
         * 
         * @return      XTemplate\Engine
         * @throws      CoreException
         */
        public function getEngineInstance()
        {
            return $this->objEngine;
        }
    }
}
?>