<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Interfaces
{
    /**
     * Classes which implement this interface provide the CSS selectors to get elements from the template. You can find
     * a whole list of supported selectors under http://xtemplate.net
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate
     */
    interface Selector
    {
        /**
         * This is the basic select method. You give a selector as argument and receive a list of matching elements. 
         * 
         * @param       string                  $strSelector        The CSS selector
         * @return      XTemplate\ElementList
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function select($strSelector);
    }
}
?>