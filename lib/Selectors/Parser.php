<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Selectors
{
    use \XTemplate\Core\Params;
    use \XTemplate\Core\Classes;
    use \XTemplate\Core\Helper;
    use \XTemplate\Selectors\Exceptions\SelectorException;
    use \XTemplate\Core\EnginePartImpl;
    
    /**
     * This is an implementation of the XTemplate\Selector interface. The different selectors are configured in the
     * config fie. You can even create your own selector. The class parses the string selectors and creates the selector
     * model.
     * 
     * @author      Tobias Pohlen
     * @version     0.4
     * @package     xtemplate
     */
    class Parser extends EnginePartImpl implements \XTemplate\Interfaces\Selector
    {
        /**
         * Regular expression for a name
         */
        const REGEX_NAME = "[A-Za-z_]{1}[A-Za-z0-9_\-()]*";
        /**
         * Regular expression for an argument
         */
        const REGEX_ARG = "(\"([^\"]*)\"|'([^']*)')";
        /**
         * RegEx names
         */
        const NEW_SELECTOR          = "new_selector";
        const TAGNAME               = "tagname";
        const PATH_SELECTOR         = "path_selector";
        const PATH_SELECTOR2        = "path_selector2";
        const ATTRIBUTE_SELECTOR    = "attribute_selector";
        const CONJUNCTION           = "conjunction";
        
        /**
         * RegEx chars which have to be escaped
         * @var array
         */
        private static $arrRegExChars = array("|", "+", "{", "}", "[", "]", "*", "-", ".", "/", "^", "$");
        
        /**
         * The refering root node of the DOM
         * @var XTemplate\DOMElement
         */
        protected $objSelectorNode = null;
        /**
         * The template object
         * @var \DOMDocument
         */
        protected $objSelectorTemplate = null;
        /**
         * The selector model
         * @var XTemplate\Selectors\Model
         */
        protected $objSelectorModel = null;
        /**
         * Path selectors
         * @var array
         */
        private $arrPathSelectors = array();
        /**
         * Attribute selectors
         * @var array
         */
        private $arrAttributeSelectors = array();
        /**
         * Selector conjunctions
         * @var array
         */
        private $arrSelectorConjunctions = array();
        /**
         * The regular expression for this selector
         * @var string
         */
        private $strRegEx = "";
        /**
         * The regular expression for the path selectors
         * @var string
         */
        private $strRegExPath = "";
        /**
         * The regular expression for the attribute selectors
         * @var string
         */
        private $strRegExAttribute = "";
        

        /**
         * Returns an array of operators from the config definitions and takes that all characters which are used for 
         * regular expressions are escaped
         * 
         * @param       array               $arrDefinitions
         * @return      array
         */
        private static function getOperators(array $arrDefinitions)
        {
            $arrOperators = array_keys($arrDefinitions);
            
            // Create the array of escaped chars
            $arrEscapedChars = array();
            foreach (self::$arrRegExChars as $strChar)
            {
                $arrEscapedChars[] = "\\". $strChar;
            }
            
            // Walk through the array and replace all important regex chars
            $intLength = count($arrOperators);
            for ($i = 0; $i < $intLength; $i++)
            {
                // Escape the reg ex chars
                $arrOperators[$i] = str_replace(self::$arrRegExChars, $arrEscapedChars, $arrOperators[$i]);
            }
            
            return $arrOperators;
        }
        
        /**
         * Removes a certain string from the beginning of another string
         * 
         * @param       string              $strRemove
         * @param       string              $strFrom
         * @return      string
         */
        private static function removeStr($strRemove, $strFrom)
        {
            // Only calculate the length once
            $intLContent = strlen($strRemove);
            // Remove the whitespaces and parse the string
            return trim(substr($strFrom, $intLContent, strlen($strFrom) - $intLContent));
        }
        
        /**
         * Creates a new selector
         * 
         * @param       XTemplate\DOMElement        $objSelectorNode    The root node where the selector starts at
         * @param       \DOMDocument                $objView        The template object
         */
        public function __construct(\XTemplate\DOMElement $objSelectorNode, \DOMDocument $objView, \XTemplate\Engine $objEngine)
        {
            $this->objSelectorNode = $objSelectorNode;
            $this->objSelectorTemplate = $objView;
            // Copy the engine instance to have the correct config file
            $this->setEngineInstance($objEngine);
            
            $objConfig = $this->getEngineInstance()->getConfig();
            
            // Get all selectors and their operators
            $arrEntries = $objConfig->getConfigEntries("selectors");
            
            foreach ($arrEntries as $objEntry)
            {
                // Import the defined selectors
                $this->importSelectors($objEntry);
            }
            
            // Create the regular expression for the selectors
            $this->createRegEx();
        }
        
        /**
         * Imports selectors from the configuration
         * 
         * @param       XTemplate\Core\ConfigInterpretors\Selectors $objSelectors
         */
        private function importSelectors(\XTemplate\Core\ConfigInterpretors\Selectors $objSelectors)
        {
            // Import the path selectors
            foreach ($objSelectors->getPathSelectors() as $strOperator => $strClassName)
            {
                $this->arrPathSelectors[$strOperator] = $strClassName;
            }
            
            // Import the attribute selectors
            foreach ($objSelectors->getAttributeSelectors() as $strOperator => $strClassName)
            {
                $this->arrAttributeSelectors[$strOperator] = $strClassName;
            }
            
            // Import the selector conjunctions
            foreach ($objSelectors->getSelectorConjunctions() as $strOperator => $strClassName)
            {
                $this->arrSelectorConjunctions[$strOperator] = $strClassName;
            }
        }
        
        /**
         * Creates the regular expression
         */
        protected function createRegEx()
        {
            // Start with an optional tag name
            // The tag name can either be a name or a *
            $strTagName = "(".self::REGEX_NAME ."|\*)";
            
            // The path selectors can be stacked
            $arrPathOperators = self::getOperators($this->arrPathSelectors);
            $this->strRegExPath = "(". implode("|", $arrPathOperators) .")(". self::REGEX_NAME .")";
            
            // There can only be one attribute selector
            // Get all attribute operators
            $arrAttributeOperators = self::getOperators($this->arrAttributeSelectors);
            $strAttributeSelector = "\[(". self::REGEX_NAME . ")(". implode("|", $arrAttributeOperators) .")(". self::REGEX_ARG .")\]";
            $this->strRegExAttribute = "/". $strAttributeSelector ."/";
            
            // There can only be one conjunction between two selectors
            // Get all conjunctions
            $arrConjunctions = self::getOperators($this->arrSelectorConjunctions);
            $strConjunction = "(". implode("|", $arrConjunctions) .")";
            
            // Random whitespace
            $strWhiteSpace = "[ ]*";
            
            // Please don't break that line, although it's abover the 120 chars
            $this->strRegEx = "/^(?<". self::NEW_SELECTOR .">[ ]*,[ ]*)?(?<". self::TAGNAME .">". $strTagName .")?(?<". self::PATH_SELECTOR .">(". $this->strRegExPath .")*)(?<". self::ATTRIBUTE_SELECTOR .">". $strAttributeSelector .")?(?<". self::PATH_SELECTOR2 .">(". $this->strRegExPath .")*)". $strWhiteSpace ."(?<". self::CONJUNCTION .">". $strConjunction .")?/";
        }
        
        /**
         * Performs a selector and returns the element set
         * 
         * @param       string                  $strSelector            The CSS selector
         * @return      XTemplate\ElementList
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function select($strSelector)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            
            // Create the result set
            $objEngine = $this->getEngineInstance();
            $objResult = $objEngine->createInstance(Classes::ELEMENTLIST);
            // Create the xpath object to perform the xpath query on
            $objXPath = $objEngine->createInstance(Classes::DOM_XPATH, $this->objSelectorTemplate);
            // Get the xpath to the selector root node
            $strXPathBase = $this->objSelectorNode->getNodePath();
            
            // Array of all selector models
            $arrSelectorModels = array(
                array()
            );
            
            $arrCurrentStack =& $arrSelectorModels[0];
            
            $strLastConjunction = "";
            
            while (strlen($strSelector) > 0)
            {
                // Get the result of the regex
                $arrMatch = array();
                
                $varResult = preg_match($this->strRegEx, $strSelector, $arrMatch);
                
                // Everything alright?
                if (strlen($arrMatch[0]) === 0)
                {
                    // There is a syntax error
                    throw new SelectorException("Invalid selector '". $strSelector ."'.");
                }
                
                // Everything alright?
                if ($varResult === 0 || $varResult === false)
                {
                    throw new SelectorException("Illegal selector '". $strSelector ."'.");
                }
                
                // Shall a new model be created?
                if ($arrMatch[self::NEW_SELECTOR] !== "")
                {
                    // Start a new selector stack
                    $arrSelectorModels[] = array();
                    $arrCurrentStack =& $arrSelectorModels[count($arrSelectorModels) - 1];
                }
                
                // Create the selector model
                $objModel = new SelectorModel;
                $arrCurrentStack[] = $objModel;
                
                // Parse the tagname
                $this->parseTagName($arrMatch[self::TAGNAME], $strLastConjunction, $objModel);
                
                if (isset($arrMatch[self::PATH_SELECTOR]))
                {
                    // Parse the path selectors
                    $this->parsePathSelectors($arrMatch[self::PATH_SELECTOR], $objModel);
                }
                
                if (isset($arrMatch[self::PATH_SELECTOR2]))
                {
                    // Parse the path selectors
                    $this->parsePathSelectors($arrMatch[self::PATH_SELECTOR2], $objModel);
                }
                
                if (isset($arrMatch[self::ATTRIBUTE_SELECTOR]))
                {
                    // Parse the attribute selectors
                    $this->parseAttributeSelectors($arrMatch[self::ATTRIBUTE_SELECTOR], $objModel);
                }
                
                // The conjunction is passed to the next tag selector
                if (isset($arrMatch[self::CONJUNCTION]))
                {
                    $strLastConjunction = $arrMatch[self::CONJUNCTION];
                }
                else
                {
                    $strLastConjunction = "";
                }
                
                // Remove the parsed string from the selector and continue
                $strSelector = self::removeStr($arrMatch[0], $strSelector);
            }
            
            // Perform the created selectors
            foreach ($arrSelectorModels as $arrModelStack)
            {
                $strXPath = $strXPathBase;
                
                foreach ($arrModelStack as $objModel)
                {
                    $strXPath .= $objModel->getXPath();
                }
                
                // Perform the xpath
                $objXPathResult = @ $objXPath->query($strXPath);

                if (!$objXPathResult)
                {
                    throw new SelectorException("Invalid xpath created: '". $strXPath ."'.");
                }
                
                $objResult->addDOMList($objXPathResult);
            }
            
            return $objResult;
        }
        
        /**
         * Parses the tag name
         * 
         * @param       string              $strTagName
         * @param       string              $strLastConjunction
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        protected function parseTagName($strTagName, $strLastConjunction, SelectorModel $objModel)
        {
            Params::string($strTagName, __CLASS__, __FUNCTION__);
            
            $objSelector = null;
            
            if ($strTagName == "")
            {
                $strTagName = "*";
            }
            
            // Is there a conjunction?
            if ($strLastConjunction !== "")
            {
                // Yes, Get the selector
                $objSelector = $this->getConjunctionInstance($strLastConjunction);
                $objSelector->performSelector($strLastConjunction, $strTagName, $objModel);
            }
            else
            {
                // No, use the default conjunction
                $objModel->pushPath("/descendant::". $strTagName);
            }
            
        }
        
        /**
         * Parses the path selectors
         * 
         * @param       string              $strTagName
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        protected function parsePathSelectors ($strPathSelectors, SelectorModel $objModel)
        {
            Params::string($strPathSelectors, __CLASS__, __FUNCTION__);
            
            // If there are no path selectors, do nothing
            if ($strPathSelectors == "")
            {
                return;
            }
            
            $arrMatch = array();
            // Get the single path selectors
            $varResult = preg_match_all("/(". $this->strRegExPath .")/", $strPathSelectors, $arrMatch);
            
            // Walk through the selectors and store the datas in the selectormodel
            $intLength = count($arrMatch[0]);
            for ($i = 0; $i < $intLength; $i++)
            {
                // Get the classname
                $strClassName = $this->arrPathSelectors[$arrMatch[2][$i]];
                
                // Create the instance of this selector
                $objSelector = $this->getEngineInstance()->createInstance($strClassName);
                
                // Is this a valid path selector?
                if (!$objSelector instanceof PathSelectorBase)
                {
                    throw new SelectorException("Invalid path selector class '". $strClassName ."'.");
                }
                
                $objSelector->performSelector($arrMatch[2][$i], $arrMatch[3][$i], $objModel);
            }
        }
        
        /**
         * Parses the attribute selectors
         * 
         * @param       string              $strAttribute
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        protected function parseAttributeSelectors($strAttribute, SelectorModel $objModel)
        {
            Params::string($strAttribute, __CLASS__, __FUNCTION__);
            
            // Skip any empty selector
            if ($strAttribute === "")
            {
                return;
            }
            
            $arrMatches = array();
            // Parse the attribute selector
            $varResult = @ preg_match($this->strRegExAttribute, $strAttribute, $arrMatches);
            
            // Everything alright?
            if ($varResult === 0 || $varResult === false)
            {
                throw new SelectorException("Invalid attribute selector '". $strAttribute ."'.");
            }
            
            // Get the correct class name
            $strClassName = $this->arrAttributeSelectors[$arrMatches[2]];
            
            // Create the instance of this selector
            $objSelector = $this->getEngineInstance()->createInstance($strClassName);

            // Is this a valid attribute selector?
            if (!$objSelector instanceof AttributeSelectorBase)
            {
                throw new SelectorException("Invalid attribtue selector class '". $strClassName ."'.");
            }
            
            // If there is no 6th array entry (using '), use the 5th one (using ")
            if (isset($arrMatches[6]))
            {
                $arrMatches[5] = $arrMatches[6];
            }
            
            $objSelector->performSelector($arrMatches[1], $arrMatches[2], $arrMatches[5], $objModel);
        }
        
        /**
         * Creates the conjunction selector instance and returns it
         * 
         * @param       string              $strConjunction
         * @return      XTemplate\Selectors\ConjunctionSelectorBase
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        protected function getConjunctionInstance($strConjunction)
        {
            Params::string($strConjunction, __CLASS__, __FUNCTION__);
            
            // Get the conjuction classname
            $strClassName = $this->arrSelectorConjunctions[$strConjunction];
            
            // Create the instance of this selector
            $objSelector = $this->getEngineInstance()->createInstance($strClassName);

            // Is this a valid selector conjunction?
            if (!$objSelector instanceof ConjunctionSelectorBase)
            {
                throw new SelectorException("Invalid selector conjunction class '". $strClassName ."'.");
            }
            
            return $objSelector;
        }
    }
}
?>