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
     * This is the base selector which defines the interface for all path selectors. All selectors receive their 
     * operator, the argument and the selector model. 
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    abstract class PathSelectorBase
    {
        public final function __construct() {}
        
        /**
         * Selects the element
         * 
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strArgument        Everything behinde the operator
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public abstract function performSelector($strOperator, $strArgument, SelectorModel $objModel);
    }
}
?>