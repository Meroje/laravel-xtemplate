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
     * This selectors selects nodes which have a certain attribute which ends with a certain string. 
     * e.g.
     * 
     * div[name$='foo'] would select <div name="barfoo"></div>
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class AttributeEndsWith extends AttributeSelectorBase
    {
        /**
         * Selects the element
         * 
         * @param       string              $strName            The name of the attribute
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strValue           The value of the attribute
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function performSelector($strName, $strOperator, $strValue, SelectorModel $objModel)
        {
            $objModel->pushAttribute("substring(@". $strName .", string-length(@". $strName .") - ". (strlen($strValue) -1) .", ". strlen($strValue) .")='". $strValue ."'");
        }
    }
}
?>