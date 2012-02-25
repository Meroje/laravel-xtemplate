<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Selectors
{
    /**
     * This is the base selector which defines the interface for all conjunction selectors. All selectors receive their 
     * operator and the selector model. 
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    abstract class ConjunctionSelectorBase
    {
        public final function __construct() {}
        
        /**
         * Create the xpath part 
         * 
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strTagName
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public abstract function performSelector($strOperator, $strTagName, SelectorModel $objModel);
    }
}
?>