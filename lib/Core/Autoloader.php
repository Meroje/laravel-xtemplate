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
     * Autoloader class
     * 
     * @author        Tobias Pohlen
     * @package        xtemplate.core
     * @version        0.1
     */
    class Autoloader
    {
        /**
         * Array of files to autoload
         * @var array
         */
        protected $arrAutoloadFiles = array();
        /**
         * Base path
         * @var string
         */
        protected $strBasePath = "";
        
        public function __construct()
        {
            // Register the autoloader
            spl_autoload_register(array($this, "autoloadClass"));
        }
        
        public function __destruct()
        {
            // Unregister the autoloader
            spl_autoload_unregister(array($this, "autoloadClass"));
        }
        
        /**
         * Autoloads a file
         * 
         * @param       string            $strClassName
         */
        public function autoloadClass($strClassName)
        {
            Params::string($strClassName, __CLASS__, __FUNCTION__);
            
            // Search for the class
            if (isset($this->arrAutoloadFiles[$strClassName]))
            {
                // Load the file
                Engine::includeFile($this->strBasePath . $this->arrAutoloadFiles[$strClassName], false);
            }
        }
        
        /**
         * Fill the autoloader with an xml node
         * 
         * @param        XTemplate\Core\XML      $objXMl
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function loadXML(XML $objXML)
        {
            // Set the base path which is append before every class path
            if($objXML->getAttribute("base") !== null)
            {
                $this->setBasePath($objXML->getAttribute("base"));
            }
            else
            {
                // The engines root is the base path
                $this->setBasePath(Engine::getPath());
            }
            
            // Load the files
            foreach ($objXML->children() as $objFile)
            {
                $this->addClass((string) $objFile->attributes()->class, (string) $objFile);
            }
        }

        /**
         * Returns the base path
         * 
         * @return      string
         */
        public function getBasePath()
        {
            return $this->strBasePath;
        }
        
        /**
         * Sets the base path
         * 
         * @param        string            $strBase
         */
        public function setBasePath($strBase)
        {
            Params::string($strBase, __CLASS__, __FUNCTION__);
            $this->strBasePath = $strBase;
        }
        
        /**
         * Adds a class to load
         * 
         * @param       string            $strClassName
         * @param       string            $strPath
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function addClass($strClassName, $strPath)
        {
            Params::string($strClassName, __CLASS__, __FUNCTION__);
            Params::string($strPath, __CLASS__, __FUNCTION__);
            
            if ($strClassName === "")
            {
                throw new Exceptions\CoreException("The classname must not be empty.");
            }
            
            if ($strPath === "")
            {
                throw new Exceptions\CoreException("The path must not be empty.");
            }
            
            $this->arrAutoloadFiles[$strClassName] = $strPath;
        }
    }
}
?>