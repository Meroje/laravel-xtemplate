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
     * This interpretor allows you to registrate different CSS selectors. 
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.core
     */
    class Selectors extends ConfigInterpretor
    {
        /**
         * Tagname for a path selector
         */
        const PATH = "path";
        /**
         * Tagname for an attribute selector
         */
        const ATTRIBUTE = "attribute";
        /**
         * Tagname for a selector conjunction
         */
        const CONJUNCTION = "conjunction";
        
        /**
         * Path selectors
         * @var array
         */
        protected $arrPathSelectors = array();
        /**
         * Attribute selectors
         * @var array
         */
        protected $arrAttributeSelectors = array();
        /**
         * Selector conjunctions
         * @var array
         */
        protected $arrSelectorConjunctions = array();        
        
        /**
         * Parse the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected function parse()
        {
            // Collect all entries and sort them
            foreach ($this->objXML->children() as $objChild)
            {
                $strClassName = (string) $objChild;
                
                $strOperator = $objChild->getAttribute("operator");
                
                // If there is no operator, throw an exception
                if ($strOperator === null)
                {
                    throw new ConfigException("No operator registrated for selector '". $strClassName ."'.");
                }
                
                // Add the selector
                switch ($objChild->getName())
                {
                    case self::PATH:
                        $this->arrPathSelectors[$strOperator] = $strClassName;
                        break;
                    
                    case self::ATTRIBUTE:
                        $this->arrAttributeSelectors[$strOperator] = $strClassName;
                        break;
                    
                    case self::CONJUNCTION:
                        $this->arrSelectorConjunctions[$strOperator] = $strClassName;
                        break;
                    
                    default:
                        // Unknown selector type
                        throw new ConfigException("Unknown selector type '". $objChild->getName() ."'.");
                        break;
                }
            }
        }
        
        /**
         * Returns all path selectors
         * 
         * @return      array
         */
        public function getPathSelectors()
        {
            return $this->arrPathSelectors;
        }
        
        /**
         * Returns all attribute selectors
         * 
         * @return      array
         */
        public function getAttributeSelectors()
        {
            return $this->arrAttributeSelectors;
        }
        
        /**
         * Returns all selector conjunctions
         * 
         * @return      array
         */
        public function getSelectorConjunctions()
        {
            return $this->arrSelectorConjunctions;
        }
    }
}
?>