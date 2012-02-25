<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use XTemplate\Exceptions\ElementListException;
    use XTemplate\Exceptions\NodeEditException;
    use XTemplate\Core\Params;
    
    /**
     * Element list which can contain several elements and perform modification on them. 
     * 
     * @author      Tobias Pohlen
     * @version     0.4
     * @package     xtemplate
     */
    class ElementList implements \ArrayAccess, Interfaces\NodeEdit, \IteratorAggregate, \Countable
    {
        /**
         * Contained elements
         * @var array
         */
        protected $arrList = array();
        
        /**
         * Create a new element list. You can use it like:
         * new ElementList($element1, $element2, $element3 ...)
         * 
         * @param       ...                 Elements
         * @throws      XTemplate\Exceptions\ElementListException
         */
        public function __construct()
        {
            $arrElements = func_get_args();
            
            foreach ($arrElements as $objElement)
            {
                if (!is_object($objElement))
                {
                    throw new ElementListException("You may only pass objects to the elementlist.");
                }
                
                // The element must be an instance of DOMElement
                if (!$objElement instanceof \DOMElement)
                {
                    throw new ElementListException(
                            "The element of the elementlist must be instances of XTemplate\DOMElement.");
                }
                
                $this->addElement($objElement);
            }
        }
        
        /**
         * Adds an element to the list
         * 
         * @param       XTemplate\DOMElement        $objElement
         */
        public function addElement(DOMElement $objElement)
        {
            $this->arrList[] = $objElement;
        }
        
        /**
         * Removes an element from the list
         * 
         * @param       XTemplate\DOMElement        $objElement
         * @return      bool                        If the element was found
         */
        public function removeElement(DOMElement $objElement)
        {
            $intLength = count($this->arrList);
            for ($i = 0; $i < $intLength; $i++)
            {
                if ($objElement === $this->arrList[$i])
                {
                    // Remove the element from the list
                    unset($this->arrList[$i]);
                    return true;
                }
            }
            return false;
        }
        
        /**
         * Returns a specific element from the list by its offset
         * 
         * @param       int                $intOffset
         * @return      DOMElement
         * @see         \ArrayAccess
         * @throws      XTemplate\Exceptions\ElementListException
         */
        public function offsetGet($intOffset)
        {
            Params::int($intOffset, __CLASS__, __FUNCTION__);
            
            $intOffset = (int) $intOffset;
            
            if ($this->offsetExists($intOffset))
            {
                return $this->arrList[$intOffset];
            }
            
            // The element does not exist
            throw new ElementListException("Unknown offset: '". $intOffset ."'.");
        }
        
        /**
         * Returns whether or not an offset exists in the list
         * 
         * @param       int                $intOffset
         * @return      bool
         * @see         \ArrayAccess
         */
        public function offsetExists($intOffset)
        {
            Params::int($intOffset, __CLASS__, __FUNCTION__);
            
            $intOffset = (int) $intOffset;
            
            return isset($this->arrList[$intOffset]);
        }
        
        /**
         * Returns adds an element to the list
         * 
         * @param       mixed            $intOffset
         * @param       DOMElement        $objNode
         * @throws      XTemplate\Exceptions\ElementListException
         */
        public function offsetSet($intOffset, $objNode)
        {
            // Prove the element
            if (!is_object($objNode))
            {
                // That means the user called something like $list[0] = "test". The content of this element will be set 
                // to the value
                if ($intOffset === null || !$this->offsetExists($intOffset))
                {
                    throw new ElementListException("You cannot set the content of an not existing element.");
                }
                
                $objElement = $this->offsetGet($intOffset);
                
                $objElement->setContent((string) $objNode);
                return;
            }
            
            if (!$objNode instanceof DOMElement)
            {
                    throw new ElementListException(
                            "The element of the elementlist must be instances of XTemplate\DOMElement.");
            }
            
            // If the offset is null, the element shall be added to the end of the list
            if ($intOffset === null)
            {
                $this->addElement($objNode);
                return;
            }
            
            $this->arrList[$intOffset] = $objNode;
        }
        
        /**
         * Removes an element from the list
         * 
         * @param       int                $intOffset
         * @throws      XTemplate\Exceptions\ElementListException
         */
        public function offsetUnset($intOffset)
        {
            Params::int($intOffset, __CLASS__, __FUNCTION__);
            
            if ($this->offsetExists($intOffset))
            {
                $this->removeElement($this->arrList[$intOffset]);
                return;
            }
            
            throw new ElementListException("Unknown offset: '". $intOffset ."'.");
        }
        
        /**
         * Sets the note content
         * 
         * @param       string                $strContent
         * @return      XTemplate\Interfaces\NodeEdit
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function setContent($strContent)
        {
            Params::string($strContent, __CLASS__, __FUNCTION__);
            
            foreach ($this->arrList as $objElement)
            {
                $objElement->setContent($strContent);
            }
            
            return $this;
        }
        
        /**
         * Removes the node
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function remove()
        {
            foreach ($this->arrList as $objElement)
            {
                $objElement->remove();
            }
        }
        
        /**
         * Sets a specific attribute
         * 
         * @param       string                $strName
         * @param       string                $strValue
         * @return      XTemplate\Interfaces\NodeEdit
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function setAttribute($strName, $strValue)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            Params::string($strValue, __CLASS__, __FUNCTION__);
            
            foreach ($this->arrList as $objElement)
            {
                $objElement->setAttribute($strName, $strValue);
            }
            
            return $this;
        }
        
        /**
         * Returnst a specific's attribute value from the currently active element of the list
         * 
         * @param       string                $strName
         * @return      string
         * @see         XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function getAttribute($strName)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            if ($strName === "")
            {
                throw new NodeEditException("Attribute name must not be empty.");
            }
            
            return $this->arrList[key($this->arrList)]->getAttribute($strName);
        }
        
        /**
         * Adds a class to the class attribute
         * 
         * @param       string                $strClass
         * @return      XTemplate\Interfaces\NodeEdit
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function addClass($strClass)
        {
            Params::string($strClass, __CLASS__, __FUNCTION__);
            
            foreach ($this->arrList as $objElement)
            {
                $objElement->addClass($strClass);
            }
            
            return $this;
        }
        
        /**
         * Removes a class from the class attribute
         * 
         * @param       string                $strClass
         * @return      XTemplate\Interfaces\NodeEdit
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function removeClass($strClass)
        {
            Params::string($strClass, __CLASS__, __FUNCTION__);
            
            foreach ($this->arrList as $objElement)
            {
                $objElement->removeClass($strClass);
            }
            
            return $this;
        }
        
        /**
         * Appends dom elements from an input string
         * 
         * @param       string              $strHTML
         * @return      XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function appendHTML($strHTML)
        {
            Params::string($strHTML, __CLASS__, __FUNCTION__);
            
            foreach ($this->arrList as $objElement)
            {
                $objElement->appendHTML($strHTML);
            }
            
            return $this;
        }
        
        /**
         * Creates an interator for the foreach construct
         * 
         * @see         \IteratorAggregate
         * @return      ArrayIterator
         */
        public function getIterator()
        {
            return new \ArrayIterator($this->arrList);
        }
        
        /**
         * Clears the list
         */
        public function clear()
        {
            $this->arrList = array();
        }
        
        /**
         * Adds an resultset to the elementlist
         * 
         * @param       \DOMNodeList            $objList
         */
        public function addDOMList(\DOMNodeList $objList)
        {
            $intLength = $objList->length;
            
            for ($i = 0; $i < $intLength; $i++)
            {
                $this[] = $objList->item($i);
            }
        }
        
        /**
         * Returns how many node are in the list
         * 
         * @return      int
         */
        public function count()
        {
            return count($this->arrList);
        }
        
        /**
         * Returns the string version of the first node in the list or an empty string if the list is empty. 
         * 
         * @return      string
         */
        public function __toString()
        {
            if (isset($this->arrList[0]))
            {
                return (string) $this->arrList[0];
            }
            
            // There is nothing in the list
            // Return an empty string
            return "";
        }
        
        /**
         * Appends a child(-tree) to the current node. 
         * 
         * @param       \DOMNode            $objElement
         * @return      XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         */
        public function append(\DOMNode $objElement)
        {
            foreach ($this->arrList as $objListEntry)
            {
                $objListEntry->append($objElement);
            }
            
            return $this;
        }
        
        /**
         * Returns the node content. If there is no node, an empty string will be returned.
         * 
         * @return      string
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function getContent()
        {
            if (isset($this->arrList[0]))
            {
                return $this->arrList[0]->getContent();
            }
            return "";
        }
        
    }
}
?>