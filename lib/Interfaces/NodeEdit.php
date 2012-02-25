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
     * Classes which implement this interface are able to edit certain nodes of the template. The most important classes
     * or XTemplate\ElementList and XTemplate\DOMElement which implement NodeEdit. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate
     */
    interface NodeEdit
    {
        /**
         * Sets the content of the nodes. If the node contains other nodes they will be removed. There are two common 
         * ways you can use this method:
         * 
         * XTemplate\ElementList:
         * $this["#selector"]->setContent("a string");
         * Sets the content of all nodes which have the attribute 'xid="selector"'. 
         * 
         * XTemplate\DOMElement
         * $objNode->setContent("a string");
         * 
         * The parameter are proved with XTemplate\Core\Params. If you add something which cannot be converted 
         * implicitly to a string. You get a E_USER_WARNING error. 
         * 
         * @param       string                $strContent
         * @return      XTemplate\Interfaces\NodeEdit
         */
        public function setContent($strContent);
        
        /**
         * Returns the node content. If there is no node, an empty string will be returned.
         * 
         * @return      string
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function getContent();
        
        /**
         * This method removes the connected node/s from the document which they belong to. There are two common 
         * ways you can use this method:
         * 
         * XTemplate\ElementList:
         * $this["#selector"]->remove()
         * Removes all nodes from the document which have the attribute 'xid="selector"'
         * 
         * XTemplate\DOMElement
         * $objNode->remove();
         * Removes the node from its document. 
         */
        public function remove();
        
        /**
         * Sets the value of an existing attribute with the name $strName or adds this attribute. The value is always
         * the string $strValue. The parameters are proved by XTemplate\Core\Params. If you pass something which cannot
         * be implicitly be converted into a string, you get a E_USER_WARNING error. There are two common ways you can 
         * use this method:
         * 
         * XTemplate\ElementList
         * $this["#selector"]->setAttribute("name", "value");
         * Set/adds the attribute 'name' with the value 'value' of/to all nodes which have the attribute 'xid="selector"'
         * 
         * XTemplate\DOMElement
         * $objNode->setAttribute("name, "value");
         * Adds the attribute 'name' or sets the value of the existing attribute .
         * 
         * @param       string                $strName
         * @param       string                $strValue
         * @return      XTemplate\Interfaces\NodeEdit
         */
        public function setAttribute($strName, $strValue);
        
        /**
         * This method returns the value of the attribute with name $strName. The parameter is proved by 
         * XTemplate\Core\Params. If you pass something which cannot be converted implicitly into string, you get an 
         * E_USER_WARNING error. There are two common ways to use this method:
         * 
         * XTemplate\ElementList
         * $this[".selector"]->getAttribute("name");
         * Returns the attribute value of the first node which has the classname "selector". 
         * 
         * XTemplate\DOMElement
         * $objNode->getAttribute("name");
         * Returns the attribute's value.
         * 
         * If the attribute which you requested does not exist, an XTemplate\Exceptions\NodeEditException is thrown.
         * 
         * @param       string                $strName
         * @return      string
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function getAttribute($strName);
        
        /**
         * Adds a classname to the class attribute. The parameter is proved by 
         * XTemplate\Core\Params. If you pass something which cannot be converted implicitly into string, you get an 
         * E_USER_WARNING error. There are two common ways to use this method:
         * 
         * XTemplate\ElementList
         * $this["#selector"]->addClass("foo");
         * Each node which has the 'xid="selector"' attribute will get the classname foo. 
         * Example:
         * Before
         * <a xid="selector" class="bar" />
         * 
         * After
         * <a xid="selector" class="bar foo" />
         * 
         * XTemplate\DOMNode
         * $objNode->addClass("foo");
         * Example: Same as above but just for a single node.
         * 
         * @param       string                $strClass
         * @return      XTemplate\Interfaces\NodeEdit
         */
        public function addClass($strClass);
        
        /**
         * Removes a class from the class attribute. The parameter is proved by 
         * XTemplate\Core\Params. If you pass something which cannot be converted implicitly into string, you get an 
         * E_USER_WARNING error. There are two common ways to use this method:
         * 
         * XTemplate\ElementList
         * $this["#selector"]->removeClass("foo");
         * Each node which has the 'xid="selector"' attribute will be proved and if the list of classnames contains the
         * requested one, it will be removed. 
         * Example:
         * Before
         * <a xid="selector" class="bar foo" />
         * 
         * After
         * <a xid="selector" class="bar" />
         * 
         * XTemplate\DOMNode
         * $objNode->addClass("foo");
         * Example: Same as above but just for a single node.
         * 
         * @param       string                $strClass
         * @return      XTemplate\Interfaces\NodeEdit
         */
        public function removeClass($strClass);
        
        /**
         * This method allows you to append some HTML code which is saved in a stirng to a certain node. The string will
         * be parsed and converted into the document object model.  The parameter is proved by 
         * XTemplate\Core\Params. If you pass something which cannot be converted implicitly into string, you get an 
         * E_USER_WARNING error. There are two common ways to use this method:
         * 
         * XTemplate\ElementList
         * $this["#headline"]->appendHTML("<h1>Headline</h1>");
         * 
         * Before:
         * <div xid="headline" />
         * 
         * After
         * <div xid="headline">
         *  <h1>Headline</h1>
         * </div>
         * 
         * XTemplate\DOMNode:
         * $objNode->appendHTML("<h1>Headline</h1>");
         * Same as above.
         * 
         * @param       string              $strHTML
         * @return      XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function appendHTML($strHTML);
        
        /**
         * Converts the current node or the first node of the set to a string. If there is no node, an empty string will
         * be returned. This method must not throw any exception. 
         * 
         * @return      string
         */
        public function __toString();
        
        /**
         * Appends a child(-tree) to the current node. 
         * 
         * @param       \DOMNode            $objElement
         * @return      XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function append(\DOMNode $objElement);
    }
}