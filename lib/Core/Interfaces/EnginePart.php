<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core\Interfaces
{
    use XTemplate\Engine;
    
    /**
     * All classes which implement this interface belong to the engine. They share all a certain instance of the Engine
     * class which contains the configuration and controls the behavior of the classes. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    interface EnginePart
    {
        /**
         * Sets the engine instance
         * 
         * @param       XTemplate\Engine            $objEngine      The engine instance
         * @throws      CoreException
         */
        public function setEngineInstance(Engine $objEngine = null);
        
        /**
         * Returns the engine instance
         * 
         * @return      XTemplate\Engine
         * @throws      CoreException
         */
        public function getEngineInstance();
    }
}
?>