<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use XTemplate\Core\Params;
    use XTemplate\Core\Helper;
    use XTemplate\Core\Constants;
    
    /**
     * Overload the DOMElement class to provide some more functionality
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate
     */
    class DOMElement extends \DOMElement implements Interfaces\Selector, Interfaces\NodeEdit, \ArrayAccess
    {
        const CLASS_ATTR = "class";
        
        /**
         * Selector for this node
         * @var XTemplate\Selectors\Parser
         */
        private $objSelector = null;
        
        /**
         * Returns the current node as html string
         * 
         * @return        string
         */
        public function __toString()
        {
            // Create the new dom document to get the dom rendering function
            try {
                $objDocument = new \DOMElement(Constants::DOM_VERSION, Constants::DOM_CHARSET);
                $objDocument->importNode($this, true);
                return $objDocument->saveHTML();
            }
            catch (\Exception $objException)
            {
                return "An error occured: ". $objException->getMessage();
            }
        }
        
        /**
         * Sets the node content
         * 
         * @param       string                $strContent
         * @return      XTemplate\Interfaces\NodeEdit
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function setContent($strContent)
        {
            Params::string($strContent, __CLASS__, __FUNCTION__);
            
            $this->nodeValue = $strContent;
            return $this;
        }
        
        /**
         * Returns the node content
         * 
         * @return      string
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function getContent()
        {
            return $this->nodeValue;
        }
        
        /**
         * Removes the node
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function remove()
        {
            if ($this->parentNode !== null)
            {
                $this->parentNode->removeChild($this);
            }
        }
        
        /**
         * Define the getAttribute interface
         * 
         * @param       string                $strName
         * @return      string
         * @throws      XTemplate\Exceptions\NodeEditException
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function getAttribute($strName)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            try
            {
                return (string) parent::getAttribute($strName);
            }
            catch (\DOMException $objException)
            {
                throw new Exceptions\NodeEditException($objException->getMessage());
            }
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
            
            // Does this node already have a class attribute?
            if (!$this->hasAttribute(self::CLASS_ATTR))
            {
                // Create it
                $objAttribute = $this->setAttribute(self::CLASS_ATTR, $strClass);
                return $this;
            }
            
            // Get the already added classes
            $strClasses = $this->getAttribute(self::CLASS_ATTR);
            
            $arrClasses = Helper::trimExplode($strClasses, ' ');
            
            // Is the class already in the list?
            if (in_array($strClass, $arrClasses))
            {
                return $this;
            }
            
            $arrClasses[] = $strClass;
            
            // Add the class
            $this->setAttribute(self::CLASS_ATTR, implode(' ', $arrClasses));
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
            
            // Does this node already have a class attribute?
            if (!$this->hasAttribute(self::CLASS_ATTR))
            {
                return;
            }
            
            // Get the already added classes
            $strClasses = $this->getAttribute(self::CLASS_ATTR);
            
            $arrClasses = Helper::trimExplode($strClasses, ' ');
            
            $arrClasses = array_combine($arrClasses, $arrClasses);
            // Remove the class
            unset($arrClasses[$strClass]);
            
            // Rewrite the attribute
            $this->setAttribute(self::CLASS_ATTR, implode(' ', $arrClasses));
        }
        
        /**
         * Sets an attribute's value
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
            
            parent::setAttribute($strName, $strValue);
            return $this;
        }
        
        /**
         * Appends dom elements from an input string. Caution: The string is not parsed and converted to DOM. It's 
         * appended as string node! So you cannot access the nodes later with CSS parsers.
         * 
         * @param      string              $strHTML
         * @return     XTemplate\Interfaces\NodeEdit
         * @see        XTemplate\Interfaces\NodeEdit
         * @throws     XTemplate\Exceptions\NodeEditException
         */
        public function appendHTML($strHTML)
        {
            // If this node doesn't have a document, we cannot add the childnodes
            if ($this->ownerDocument === null)
            {
                return $this;
            }
            
            $this->appendChild($this->ownerDocument->createCDATASection($strHTML));
            
            return $this;
        }
        
        /**
         * Appends a child(-tree) to the current node. 
         * 
         * @param       \DOMNode            $objElement
         * @return      XTemplate\Interfaces\NodeEdit
         * @throws      XTemplate\Exceptions\NodeEditException
         * @see         XTemplate\Interfaces\NodeEdit
         */
        public function append(\DOMNode $objElement)
        {
            try
            {
                // If a dom document is given, the document node will be used
                if ($objElement instanceof View)
                {
                    $objElement->render();
                    $objNode = $objElement->documentElement;
                }
                else
                {
                    $objNode = $objElement;
                }
                // Import the node into the current document
                $objNode = $this->ownerDocument->importNode($objNode, true);
                $this->appendChild($objNode);
            }
            catch (\DOMException $objException)
            {
                throw new Exceptions\NodeEditException($objException->getMessage());
            }
            catch (\XTemplate\Exceptions\RenderException $objException)
            {
                throw new Exceptions\NodeEditException($objException->getMessage());
            }
            
            return $this;
        }
        
        /**
         * Returns if there are element which match this selector
         * 
         * @see         \ArrayAccess
         * @param       string              $strSelector
         * @return      bool
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function offsetExists($strSelector)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            return count($this->select($strSelector)) > 0;
        }
        
        /**
         * Sets the content of the matched elements
         * 
         * @see         \ArrayAccess
         * @param       string              $strSelector
         * @param       string              $strContent
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function offsetSet($strSelector, $strContent)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            Params::string($strContent, __CLASS__, __FUNCTION__);
            
            $this->select($strSelector)->setContent($strContent);
        }
        
        /**
         * Returns the resultset of the selector
         * 
         * @see         \ArrayAccess
         * @param       string              $strSelector
         * @return      XTemplate\ElementList
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function offsetGet($strSelector)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            return $this->select($strSelector);
        }
        
        /**
         * Removes the matching elements from the template
         * 
         * @see         \ArrayAccess
         * @param       string              $strSelector
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function offsetUnset($strSelector)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            $this->select($strSelector)->remove();
        }
        
        /**
         * Creates a selection from this node off. 
         * 
         * @param       string                  $strSelector
         * @return      XTemplate\ElementList
         * @throws      XTemplate\Selectors\Exception\SelectorException
         */
        public function select($strSelector)
        {
            if ($this->objSelector === null)
            {
                $this->objSelector = new Selectors\Parser($this, $this->ownerDocument, Engine::getDefaultInstance());
            }
            return $this->objSelector->select($strSelector);
        }
    }
}
?>