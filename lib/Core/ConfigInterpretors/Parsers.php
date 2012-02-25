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
     * This interpretor allows you to registrate default pre and post parser. They will be applyed to every template 
     * instance.
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Parsers extends ConfigInterpretor
    {
        const PRE = "pre";
        const POST = "post";
        
        /**
         * Preparser
         * @var array
         */
        private $arrPreParsers = array();
        /**
         * Postparser
         * @var array
         */
        private $arrPostParsers = array();
        
        
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
                
                switch ($objChild->getAttribute("position"))
                {
                    case self::PRE:
                        $this->arrPreParsers[] = $strClassName;
                        break;
                        
                    case self::POST:
                        $this->arrPostParsers[] = $strClassName;
                        break;
                    
                    default:
                        // There was no position registrated for this parser => might be a configuration error
                        throw new ConfigException("There is no parser position for class '". $strClassName ."'.");
                        break;
                }
            }
        }
        
        /**
         * Returns all pre parsers
         * 
         * @return      array
         */
        public function getPreParsers()
        {
            return $this->arrPreParsers;
        }
        
        /**
         * Returns all post parsers
         * 
         * @return      array
         */
        public function getPostParsers()
        {
            return $this->arrPostParsers;
        }
    }
}
?>