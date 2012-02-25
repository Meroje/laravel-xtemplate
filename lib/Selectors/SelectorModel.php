<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Selectors
{
    use \XTemplate\Core\Params;
    
    /**
     * This is the selector model which contains the different parts of the final xpath selector
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class SelectorModel
    {
        /**
         * XPath stack
         * @var array
         */
        private $arrPathStack = array();
        /**
         * Attribute stack
         * @var array
         */
        private $arrAttributeStack = array();
        
        /**
         * Pushes something on the path stack
         * 
         * @param       string              $strXPathSnippet
         */
        public function pushPath($strXPathSnippet)
        {
            Params::string($strXPathSnippet, __CLASS__, __FUNCTION__);
            $this->arrPathStack[] = $strXPathSnippet;
        }
        
        /**
         * Pushes something on the attribute stack
         * 
         * @param       string              $strXPathSnippet
         */
        public function pushAttribute($strXPathSnippet)
        {
            Params::string($strXPathSnippet, __CLASS__, __FUNCTION__);
            $this->arrAttributeStack[] = $strXPathSnippet;
        }
        
        /**
         * Creates the final xpath and returns it
         * 
         * @return      string
         */
        public function getXPath()
        {
            $strSelector = implode("", $this->arrPathStack);
            
            // Are there any attribute values?
            if (count($this->arrAttributeStack) > 0)
            {
                $strSelector .= "[". implode(" and ", $this->arrAttributeStack) . "]";
            }
            
            return $strSelector;
        }
        
        /**
         * Returns the last element from the path stack and removes it from the stack
         * 
         * @return      string
         */
        public function popPath()
        {
            return array_pop($this->arrPathStack);
        }
        
        /**
         * Returns the last elment from the attribute stack and removes it from the stack.
         * 
         * @return      string
         */
        public function popAttriubute()
        {
            return array_pop($this->arrAttributeStack);
        }
    }
}
?>