<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 *
 * Released under the GPL License.
 */
namespace XTemplate\Parsers
{
    /**
     * This is the base class for all template parsers. With such a template parser you are able to perform default pre
     * or post processings on all templates. You can registrate a pre/post parser in the config file. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.parsers
     */
    abstract class Base
    {
        /**
         * This is the template instance
         * @var XTemplate\View
         */
        protected $objTemplate = null;
        
        /**
         * Creates a new parser instance
         * 
         * @param       XTemplate\View      $objTemplate        The template to parse
         * @throws      XTemplate\Exceptions\ParsingException
         */
        public function __construct(\XTemplate\View $objTemplate)
        {
            $this->objTemplate = $objTemplate;
            $this->parse();
        }
        
        /**
         * Parses the template
         * 
         * @throws      XTemplate\Exceptions\ParsingException
         */
        protected abstract function parse();
    }
}
?>