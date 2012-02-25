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
    use \ArrayAccess;
    
    /**
     * This interpretor allows you to put random configuration settings into your config file. You can access these 
     * settings later via array operator. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Settings extends ConfigInterpretor implements ArrayAccess
    {
        /**
         * Settings
         * @var array
         */
        private $arrSettings = array();
        
        /**
         * Parse the config entry
         * 
         * @throws      XTemplate\Core\Exceptions\ConfigException
         */
        protected function parse()
        {
            $this->arrSettings = $this->objXML->toArray();
        }
        
        /**
         * Returns if an entry exists
         * 
         * @see         ArrayAccess
         * @param       string              $strOffset
         * @return      bool
         */
        public function offsetExists($strName)
        {
            return isset($this->arrSettings[$strName]);
        }
        
        /**
         * Returns an entry from the settings
         * 
         * @see         ArrayAccess
         * @param       string              $strOffset
         * @return      mixed
         * @throws      OutOfRangeException
         */
        public function offsetGet($strOffset)
        {
            if (!$this->offsetExists($strOffset))
            {
                throw new \OutOfRangeException("There is no settings entry with key '". $strOffset ."'.");
            }
            
            return $this->arrSettings[$strOffset];
        }
        
        /**
         * Adds a setting entry
         * 
         * @param       string              $strOffset
         * @param       mixed               $varValue
         */
        public function offsetSet($strOffset, $varValue)
        {
            $this->arrSettings[$strOffset] = $varValue;
        }
        
        /**
         * Removes a setting entry
         * 
         * @param       string              $strOffset
         * @throws      OutOfRangeException
         */
        public function offsetUnset($strOffset)
        {
            if (!$this->offsetExists($strOffset))
            {
                throw new \OutOfRangeException("There is no settings entry with key '". $strOffset ."'.");
            }
            
            // Remove the entry
            unset($this->arrSettings[$strOffset]);
        }
    }
}
?>