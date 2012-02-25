<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core
{
    use Interfaces\Enginepart;
    use \XTemplate\Engine;
    use \ArrayAccess;
    
    /**
     * Configuration class. It loads the configuration from an XML file and searches for configuration adapters which
     * can interpret the config entries. 
     * 
     * @author      Tobias Pohlen
     * @package     xtemplate.core
     * @version     0.1
     */
    class Config extends EnginePartImpl implements ArrayAccess
    {
        /**
         * Inheritanced config objects
         * @var array
         */
        private $arrInheritances = array();
        /**
         * XML config object
         * @var XTemplate\Core\XML
         */
        private $objXML = null;
        /**
         * Interpretor object
         * @var array
         */
        private $arrInterpretors = array();
        
        /**
         * Creates a new config object
         * 
         * @param       string              $strConfigFile        Path to the xml config file or an instance of an 
         * @param       XTemplate\Engine    $objEngine          XTemplate engine instance
         * @throws      XTemplate\Exceptions\CoreException
         */
        public function __construct($strConfigFile, Engine $objEngine = null)
        {
            $this->setEngineInstance($objEngine);

            // Is the given "file" already an XML object?
            if ($strConfigFile instanceof XML)
            {
                $this->objXML = $strConfigFile;
            }
            else
            {
                Params::string($strConfigFile, __CLASS__, __FUNCTION__);
                // Load the XML file
                $this->objXML = XML::getInstance($strConfigFile);
            }
            
            // Does the config have the correct xml-tag name?
            $this->parseConfig();
        }
        
        /**
         * Parses the template
         */
        protected function parseConfig()
        {
            // Is there a domain attribute?
            if ($this->objXML->getAttribute("domain") !== null)
            {
                // Do the domains match?
                if ($this->objXML->getAttribute("domain") !== Engine::getDomain())
                {
                    // This config file must not be parsed
                    return;
                }
            }
            
            // Is there an extending config file?
            if ($this->objXML->getAttribute("extends") !== null)
            {
                // There is
                // Create the new config object
                $objConfig = new Config($this->objXML->getAttribute("extends"), $this->getEngineInstance());
                $this->extend($objConfig);
            }
            // Is this a normal config or a domain config
            foreach ($this->objXML->children(Constants::XML_NS_URL) as $objEntry)
            {
                // Get the list of registrated config interpretors
                $arrInterpretors = $this->getEngineInstance()->getRegistratedConfigInterpretors();

                $strName = $objEntry->getName();
                // Is there an interpretor registrated for this config entry?
                if (isset($arrInterpretors[$strName]))
                {
                    $strClassName = $arrInterpretors[$strName];
                    
                    // Get the name of the config entry
                    $strEntryName = $objEntry->getAttribute("name");
                    
                    // Create the new interpretor instance
                    $objInterpretor = new $strClassName($objEntry, $this);
                    $this->addInterpretor($strEntryName, $objInterpretor);
                }
            }
        }
        
        /**
         * Returns all interpretors
         * 
         * @return      array
         */
        public function getInterpretors()
        {
            return $this->arrInterpretors;
        }
        
        /**
         * Adds an interpretor
         * 
         * @param       string              $strName        The name of the interpretor entry
         * @param       XTemplate\Core\ConfigInterpretor    $objInterpretor
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function addInterpretor($strName, ConfigInterpretor $objInterpretor)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            if ($strName === "")
            {
                throw new Exceptions\CoreException("The interpretor name must not be empty.");
            }
            
            $this->arrInterpretors[$strName] = $objInterpretor;
        }
        
        /**
         * Returns an interpretor
         * 
         * @param       string              $strName
         * @return      XTemplate\Core\ConfigInterpretor
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function getInterpretor($strName)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            // Does the interpretor exist?
            if (!isset($this->arrInterpretors[$strName]))
            {
                throw new Exceptions\CoreException("Interpretor '". $strName ."' does not exist.");
            }
            
            return $this->arrInterpretors[$strName];
        }
        
        /**
         * Extends this config form another one
         * 
         * @param       XTemplate\Core\Config $objConfig
         */
        public function extend(Config $objConfig)
        {
            $this->arrInheritances[] = $objConfig;
            $this->arrInterpretors = array_merge($this->arrInterpretors, $objConfig->getInterpretors());
        }
        
        /**
         * Returns whether or not an interpretor exists
         * 
         * @see         ArrayAccess
         * @param       string              $strOffset
         * @return      mixed
         */
        public function offsetExists($strOffset)
        {
            return isset($this->arrInterpretors[$strOffset]);
        }
        
        /**
         * Returns an interpretor value if it exists. If it doesn't it returns null
         * 
         * @see         ArrayAccess
         * @param       string              $strOffset
         * @return      XTemplate\Core\ConfigInterpretor
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function offsetGet($strOffset)
        {
            return $this->getInterpretor($strOffset);
        }
        
        /**
         * Operator for Config::addInterpretor.
         * 
         * @see         ArrayAccess
         * @param       string              $strName
         * @param       XTemplate\Core\ConfigInterpretor    $objInterpretor
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public function offsetSet($strName, $objInterpretor)
        {
            $this->addInterpretor($strName, $objInterpretor);
        }
        
        /**
         * Removes a config entry. 
         * 
         * @see         ArrayAccess
         * @param       string              $strName
         */
        public function offsetUnset($strName)
        {
            if ($this->offsetExists($strName))
            {
                unset($this->arrInterpretors[$strName]);
            }
        }
        
        /**
         * Returns all config entries of certain interpretor type
         * 
         * @param       string              $strName        The name of the interpretor e.g. x:config
         * @return      array
         */
        public function getConfigEntries($strName)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            $arrInterpretors = $this->getEngineInstance()->getRegistratedConfigInterpretors();
            
            // Is this name registrated
            if (!isset($arrInterpretors[$strName]))
            {
                return array();
            }
            
            // Get the classname
            $strClassName = $arrInterpretors[$strName];
            
            $arrResult = array();
            
            // walk through the interpretors and search for the right ones
            foreach ($this->arrInterpretors as $objInterpretor)
            {
                if ($objInterpretor instanceof $strClassName)
                {
                    $arrResult[] = $objInterpretor;
                }
            }
            
            return $arrResult;
        }
    }
}
?>