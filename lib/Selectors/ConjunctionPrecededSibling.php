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
     * This conjunction select all elements are preceded by the next selector. e.g.
     * 
     * div + a would select the a tag: <div></div><span></span><a></a>
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class ConjunctionPrecededSibling extends ConjunctionSelectorBase
    {
        
        /**
         * Create the xpath part 
         * 
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strTagName
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function performSelector($strOperator, $strTagName, SelectorModel $objModel)
        {
            $objModel->pushPath("/following-sibling::". $strTagName);
        }
    }
}
?>