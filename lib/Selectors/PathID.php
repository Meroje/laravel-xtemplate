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
     * This selector allows you to select tags with their xids. e.g.:
     * 
     * 'div#test'
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class PathID extends PathSelectorBase
    {
        /**
         * Selects the element
         * 
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strArgument        Everything behinde the operator
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function performSelector($strOperator, $strArgument, SelectorModel $objModel)
        {
            $objModel->pushAttribute("@xid='". $strArgument ."'");
        }
    }
}
?>