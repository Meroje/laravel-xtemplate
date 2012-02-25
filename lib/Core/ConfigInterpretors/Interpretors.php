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
     * This interpretor allows you to registrate new config interpretors. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Interpretors extends ConfigInterpretor
    {
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
                $strName = $objChild->getAttribute("name");
                
                // If there is no operator, throw an exception
                if ($strName === null)
                {
                    throw new ConfigException("No name registrated for interpretor '". $strClassName ."'.");
                }
                $this->objConfig->getEngineInstance()->registrateConfigInterpretor($strName , $strClassName);
            }
        }
    }
}
?>