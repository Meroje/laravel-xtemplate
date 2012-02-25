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
     * This selector lets you select tags which have a certain value in a white space speparated liste of an attribute
     * value. e.g.
     * 
     * div[name~='foo'] would select <div name="foo bar"></div>
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.selectors
     */
    class AttributeContain extends AttributeSelectorBase
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
            // Thanks to: 
            // http://stackoverflow.com/questions/1390568/xpath-how-to-match-attributes-that-contain-a-certain-string
            $objModel->pushAttribute("contains(concat(' ', normalize-space(@". $strName ."), ' '), ' ". $strValue ." ')");
        }
    }
}
?>