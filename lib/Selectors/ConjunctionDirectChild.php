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
     * Selects direct child relations in the DOM tree. e.g.
     * 
     * div > span would match <div><span></span></div> but not <div><a><span></span></a></div>
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class ConjunctionDirectChild extends ConjunctionSelectorBase
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
            $objModel->pushPath("/child::". $strTagName);
        }
    }
}
?>