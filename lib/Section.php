<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use \XTemplate\Core\Classes;
    use \XTemplate\Core\Constants;
    use \XTemplate\Core\Params;
    use \XTemplate\Exceptions\SectionizeException;
    
    /**
     * This is one of the most important classes of the whole system. Each template is splitted into sections. Each 
     * section is an own id scope and can contain even further sections. 
     * 
     * You can use the selector with the array operator.
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate
     */
    class Section extends \XTemplate\Selectors\Parser implements \ArrayAccess
    {
        const DOM_ELEMENT = "DOMElement";
        const SECTION_ATTR = "xsection";
        
        /**
         * Instance counter
         * @var int
         */
        private static $intInstances = 0;
        /**
         * Array with all original sections
         * @var array
         */
        private static $arrCache = array();
        
        /**
         * The main template object
         * @var XTemplate\View
         */
        private $objMainTemplate = null;
        /**
         * This is the section document
         * @var \DOMDocument
         */
        private $objSectionDocument = null;
        /**
         * The parent section
         * @var XTemplate\Section
         */
        private $objParent = null;
        /**
         * The origin element in the parent template
         * @var XTemplate\DOMElement
         */
        private $objOriginalElement = null;
        /**
         * The current rootnode of the section template
         * @var XTemplate\DOMElement
         */
        private $objDOMElement = null;
        /**
         * The spaceholder in the parent document
         * @var XTemplate\DOMElement
         */
        private $objSpaceHolder = null;
        /**
         * Included sections
         * @var array
         */
        private $arrSections = array();
        /**
         * Created section clones
         * @var array
         */
        private $arrClones = array();
        /**
         * The instance ID
         * @var int
         */
        private $intID = 0;
        
        /**
         * Creates a new section
         * 
         * @param       XTemplate\DOMElement            $objElement
         * @param       XTemplate\View                  $objView
         */
        public function __construct(DOMElement $objElement, View $objView, Section $objParent = null)
        {
            $this->objDOMElement    = $objElement;
            $this->objMainTemplate  = $objView;
            $this->objParent        = $objParent;
            $this->intID            = self::$intInstances;
            self::$intInstances++;
            
            // Get the document of the parent section
            $objParentSectionDoc = $this->getParentSectionDoc();
            
            try
            {
                // The original section DOM element has to be removed from the template to not get touched by any
                // selectors
                // Add a space holder instead.
                $this->objSpaceHolder = $objParentSectionDoc->createElement("spacerholder_". $this->intID);
                // Append the spaceholder
                $this->objDOMElement->parentNode->insertBefore($this->objSpaceHolder, $this->objDOMElement);
                // Remove the actual element from the template
                $this->objDOMElement->remove();
                
                // Remove the section attribute to avoid recursions
                $this->objDOMElement->removeAttribute(self::SECTION_ATTR);
                $this->objOriginalElement = $this->objDOMElement->cloneNode(true);

                // Create the new section document
                $this->objSectionDocument = new \DOMDocument(Constants::DOM_VERSION, Constants::DOM_CHARSET);
                $this->objSectionDocument->registerNodeClass(
                        self::DOM_ELEMENT, $objView->getEngineInstance()->getClassName(self::DOM_ELEMENT));

                // Now move the root element of the section from the parent template to the section template
                $this->objDOMElement = $this->objSectionDocument->importNode($this->objDOMElement, true);
                $this->objSectionDocument->appendChild($this->objDOMElement);
                
                // Each section needs an ID to by identified by the clones
                self::$arrCache[$this->intID] = $this;
                
                // Check for included sections
                $this->parseSections();
            }
            catch (\DOMException $objException)
            {
                throw new SectionizeException($objException->getMessage());
            }
            
            parent::__construct(
                    $this->objDOMElement, $this->objSectionDocument, $this->objMainTemplate->getEngineInstance());
        }
        
        /**
         * Finds and creates the included sections
         * 
         * @throws      XTemplate\Exceptions\SectionizeException
         */
        private function parseSections()
        {
            $objEngine = $this->objMainTemplate->getEngineInstance();
            
            // Create a new XPath object to get all subsections
            $objXPath = $objEngine->createInstance(Classes::DOM_XPATH, $this->objSectionDocument);
            
            // Create the xpath string
            $strXPath = $this->objDOMElement->getNodePath() . 
                    "/descendant::*[@xsection]/ancestor-or-self::*[@xsection][position()=last()]";
            $objNodeSet = $objXPath->query($strXPath);
            
            if (!$objNodeSet)
            {
                throw new SectionizeException("Could not perform sectionize query.");
            }
            
            // The nodelist is connected to the document. So we move all nodes to an array to avoid problems while 
            // moving the nodes
            $intLength = $objNodeSet->length;
            $arrNodes = array();
            for ($i = 0; $i < $intLength; $i++)
            {
                $arrNodes[] = $objNodeSet->item($i);
            }
            
            foreach ($arrNodes as $objNode)
            {
                // Is there already a section with this name?
                if (isset($this->arrSections[$objNode->getAttribute(self::SECTION_ATTR)]))
                {
                    throw new SectionizeException("Duplicate section name '". 
                            $objNode->getAttribute(self::SECTION_ATTR) ."'.");
                }
                
                // Create a new section inside of this section
                $this->arrSections[$objNode->getAttribute(self::SECTION_ATTR)] = $objEngine->createInstance(
                        Classes::SECTION, 
                        $objNode, 
                        $this->objMainTemplate, 
                        $this);
            }
        }
        
        /**
         * Adds a section clone
         * 
         * @param       XTemplate\Section
         */
        public function addClone(Section $objSection)
        {
            $this->arrClones[] = $objSection;
        }
        
        /**
         * Sets the refering section dom element
         * 
         * @param       XTemplate\DOMElement        $objDOMElement
         */
        public function setDOMElement(DOMElement $objDOMElement)
        {
            $this->objDOMElement = $objDOMElement;
        }
        
        /**
         * Sets the parent section
         * 
         * @param       XTemplate\Section           $objParent
         */
        public function setParent(Section $objParent)
        {
            $this->objParent = $objParent;
        }
        
        /**
         * Returns a section 
         * 
         * @param       string              $strSectionName     The name of the section which shall be cloned
         * @return      XTemplate\Section
         * @throws      OutOfRangeException
         */
        public function getSection($strSectionName)
        {
            Params::string($strSectionName, __CLASS__, __FUNCTION__);
            
            // Does this section exist?
            if (!isset($this->arrSections[$strSectionName]))
            {
                throw new \OutOfRangeException("Section '". $strSectionName ."' does not exist.");
            }
            
            return $this->arrSections[$strSectionName];
        }
        
        /**
         * Returns a section 
         * 
         * @param       string              $strSectionName     The name of the section which shall be cloned
         * @return      XTemplate\Section
         * @throws      OutOfRangeException
         * @throws      XTemplate\Exceptions\SectionizeException
         */
        public function getSectionClone($strSectionName)
        {
            Params::string($strSectionName, __CLASS__, __FUNCTION__);
            return clone $this->getSection($strSectionName);
        }

        /**
         * Clones the section
         * 
         * @throws      XTemplate\Exceptions\SectionizeException
         */
        public function __clone()
        {
            try
            {
                // Add the clone
                self::$arrCache[$this->intID]->addClone($this);

                // Create a new section document
                $objView = $this->objMainTemplate;

                $this->objSectionDocument = new \DOMDocument(Constants::DOM_VERSION, Constants::DOM_CHARSET);
                $this->objSectionDocument->registerNodeClass(
                        self::DOM_ELEMENT, 
                        $objView->getEngineInstance()->getClassName(self::DOM_ELEMENT));

                // Create a clone of the root element
                $this->objDOMElement = $this->objOriginalElement->cloneNode(true);

                // Add the clone
                $this->objDOMElement = $this->objSectionDocument->importNode($this->objDOMElement, true);
                $this->objSectionDocument->appendChild($this->objDOMElement);

                // Clear the clone array
                $this->arrClones = array();
                // Clear the section array
                $this->arrSections = array();

                $this->objSelectorNode = $this->objDOMElement;
                $this->objSelectorTemplate = $this->objSectionDocument;

                // Reparese the included sections
                $this->parseSections();
            }
            catch (\DOMException $objException)
            {
                throw new SectionizeException($objException->getMessage());
            }
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
         * Renders the section
         * 
         * @throws      XTemplate\Exceptions\RenderException
         */
        public function render()
        {
            // Render all section clones and add the dom elements
            foreach ($this->arrClones as $objSection)
            {
                $objSection->renderSection();
            }
            
            // Remove the spaceholder
            $this->objSpaceHolder->remove();
        }
        
        /**
         * Renders the actual section clone
         * 
         * @throws      XTemplate\Exceptions\RenderException
         */
        public function renderSection()
        {
            // Render the sub sections
            foreach ($this->arrSections as $objSection)
            {
                $objSection->render();
            }
            
            try 
            {
                $objParentSectionDoc = $this->getParentSectionDoc();

                $objNode = $objParentSectionDoc->importNode($this->objDOMElement, true);
                $this->objSpaceHolder->parentNode->insertBefore($objNode, $this->objSpaceHolder);
            }
            catch (\DOMException $objException)
            {
                // We have to be shure to stick to the interface of the method
                throw new Exceptions\RenderException($objException->getMessage());
            }
        }
        
        /**
         * Returns the parent section document
         * 
         * @return      \DOMDocument
         */
        private function getParentSectionDoc()
        {
            if ($this->objParent === null)
            {
                return $this->objMainTemplate;
            }
            return $this->objParent->getDocument();
        }
        
        /**
         * Returns the section document
         * 
         * @return      \DOMDocument
         */
        public function getDocument()
        {
            return $this->objSectionDocument;
        }
    }
}
?>