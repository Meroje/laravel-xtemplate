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
     * This selector lets you select tags which don't have a certain attribute value. e.g.
     * 
     * div[name!='foo'] would select every div except this one <div name="foo"></div>
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class AttributeUnequal extends AttributeSelectorBase
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
            $objModel->pushAttribute("not(@". $strName . "='". $strValue ."')");
        }
    }
}
?>