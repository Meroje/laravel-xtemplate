<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use \XTemplate\Core\Helper;
    
    /**
     * This class gives you a little bit more comfort. You just have to add a TEMPLATE constant to your class with the 
     * path to your template relative to your class file. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate
     */
    abstract class ComfortView extends View
    {
        /**
         * Init method which can be overwritten
         */
        protected function init()
        {}
        
        /**
         * Creates a new view object
         */
        public function __construct(Engine $objEngine = null)
        {
            // Determine the path to the template file
            $objRefClass = new \ReflectionClass($this);
            $strPathSeparator = Engine::getPathSeparator();
            
            $arrFolder = explode($strPathSeparator, dirname($objRefClass->getFileName()));
            $strFolder = implode($strPathSeparator, $arrFolder) . $strPathSeparator;

            $strPath = Helper::createRelPath($strFolder, static::TEMPLATE);
            
            parent::__construct($strPath, $objEngine);
        }
    }
}
?>