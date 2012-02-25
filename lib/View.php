<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use \XTemplate\Core\Params;
    use \ArrayAccess;
    use \XTemplate\Core\Classes;
    use \XTemplate\Core\Constants;
    
    /**
     * This is the base class for all view classes. You extend the this class and overwrite the render method. In this 
     * method, you transfer the datas from your model datas to the template. You can add any model data by the assign
     * method. 
     * 
     * @author      Tobias Pohlen
     * @package     xtemplate
     * @version     0.4
     */
    abstract class View extends \DOMDocument implements ArrayAccess, Core\Interfaces\EnginePart, Interfaces\Selector
    {
        const DOM_NODE      = "DOMNode";
        const DOM_DOCUMENT  = "DOMDocument";
        const DOM_ELEMENT   = "DOMElement";
        const TYPE          = 0;
        const DEFAULT_VALUE = 1;
        
        /**
         * If this constant is set to true, an exception is thrown when an undefined model is assined or the model type
         * is wrong
         */
        const STRICT_MODE   = true;
        /**
         * This tag will be removed after rendering the template
         */
        const REMOVE        = "remove";
        
        /**
         * Model interfaces for the different view classes
         * @var array
         */
        private static $arrModelInterfaces = array();
        
        /**
         * Connected engine instance
         * @see XTemplate\Core\Interfaces\EnginePart
         * @var XTemplate\Engine
         */
        protected $objEngine = null;
        /**
         * Postparsers
         * @var array
         */
        protected $arrPostParsers = array();
        /**
         * Assigned models
         * @var array
         */
        private $arrModels = array();
        /**
         * Toplevel section
         * @var XTemplate\Section
         */
        protected $objTopLevelSection = null;
        /**
         * Working section
         * @var XTemplate\Section
         */
        protected $objWorkingSection = null;
        /**
         * Whether the template was already rendered
         * @var bool
         */
        private $bolRendered = false;
        
        /**
         * Creates a new template engine
         * 
         * @param      string               $strXMLFile         The path to an xml file or an instance of SimpleXMLElement
         * @param      XTemplate\Engine     $objEngine          The connected engine instance. 
         * @throws     XTemplate\Parsers\Exceptions\ParsingException
         * @throws     XTemplate\Exceptions\TemplateException
         */
        public function __construct($strXMLFile, Engine $objEngine = null)
        {
            // Load the default models
            $this->loadDefaultModels();
            
            // Get the correct engine instance
            $this->setEngineInstance($objEngine);
            
            $objEngine = $this->getEngineInstance();
            $objConfig = $objEngine->getConfig();
            
            parent::__construct(Constants::DOM_VERSION, Constants::DOM_CHARSET);
            
            // Register the dom class overloads to get own functionality 
            $this->registerNodeClass(self::DOM_DOCUMENT, $objEngine->getClassName(self::DOM_DOCUMENT));
            $this->registerNodeClass(self::DOM_NODE, $objEngine->getClassName(self::DOM_NODE));
            $this->registerNodeClass(self::DOM_ELEMENT, $objEngine->getClassName(self::DOM_ELEMENT));
            
            // Wrap the template in tags which will be removed after the rendering to allow access to the root node and
            // avoid error if there is no single root node.
            // Get the file content
            $hdlFile = @ fopen($strXMLFile, "r");
            if (!$hdlFile)
            {
                throw new Exceptions\TemplateException("Could not open template file '". $strXMLFile ."'.");
            }

            // Read the file content
            $strContent = @fread($hdlFile, filesize($strXMLFile));
            @fclose($hdlFile);

            if (!$strContent)
            {
                throw new Exceptions\TemplateException("Could not read template file content '". $strXMLFile ."'.");
            }

            if (!@$this->loadXML("<". self::REMOVE .">". $strContent . "</". self::REMOVE .">"))
            {
                throw new Exceptions\TemplateException("Could not parse template '". $strXMLFile ."'.");
            }
            
            // Perform the preparsers
            // Get all parsers from the config
            $arrConfigEntries = $objConfig->getConfigEntries("parsers");
            $arrPreParsers = array();
            foreach ($arrConfigEntries as $objConfigParser)
            {
                // Are there any preparsers?
                if (count($objConfigParser->getPreParsers()) > 0)
                {
                    $arrPreParsers = array_merge($arrPreParsers, $objConfigParser->getPreParsers());
                }
                
                // Are there any postparsers?
                if (count($objConfigParser->getPostParsers()) > 0)
                {
                    $this->arrPostParsers = array_merge($this->arrPostParsers, $objConfigParser->getPostParsers());
                }
            }
            
            // Perform the preparsers
            $this->performParsers($arrPreParsers);
            
            
            // Create the toplevel section of the template
            $this->objTopLevelSection = $objEngine->createInstance(Classes::SECTION, $this->documentElement, $this);
            $this->objWorkingSection  = clone $this->objTopLevelSection;
        }

        /***************************************************************************************************************
         * Implementation of the EnginePart interface
         **************************************************************************************************************/

        /**
         * Sets the engine instance
         * 
         * @param       XTemplate\Engine            $objEngine      The engine instance
         * @throws      CoreException
         */
        public function setEngineInstance(Engine $objEngine = null)
        {
            // You cannot overwrite an existing engine instance
            if ($this->objEngine !== null)
            {
                throw new Exceptions\CoreException("You cannot change the engine instance of an existing object.");
            }
            
            // If there is no engine instance given, select the default instance
            if ($objEngine === null)
            {
                $objEngine = Engine::getDefaultInstance();
            }
            
            $this->objEngine = $objEngine;
        }
        
        /**
         * Returns the engine instance
         * 
         * @return      XTemplate\Engine
         * @throws      CoreException
         */
        public function getEngineInstance()
        {
            return $this->objEngine;
        }
        
        /***************************************************************************************************************
         * Implement the interface to the main section
         **************************************************************************************************************/
        
        
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
            return $this->objWorkingSection->getSectionClone($strSectionName);
        }
        
        /**
         * A abbreviation of the getSectionClone method. 
         * 
         * @param       string              $strSectionName     The name of the section which shall be cloned
         * @return      XTemplate\Section
         * @throws      OutOfRangeException
         * @throws      XTemplate\Exceptions\SectionizeException
         */
        public function __invoke()
        {
            $arrArgs = func_get_args();
            
            // Are the argument correct?
            if (count($arrArgs) < 1)
            {
                throw new Exceptions\SectionizeException("Missing argument: section name.");
            }
            
            // Get the section
            return $this->getSectionClone($arrArgs[0]);
        }
        
        /**
         * Returns a clone of the working section
         * 
         * @return      XTemplate\Section
         * @throws      OutOfRangeException
         * @throws      XTemplate\Exceptions\SectionizeException
         */
        public function getClone()
        {
            return clone $this->objWorkingSection;
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
            return $this->objWorkingSection->offsetExists($strSelector);
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
            return $this->objWorkingSection->offsetSet($strSelector, $strContent);
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
            return $this->objWorkingSection->offsetGet($strSelector);
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
            return $this->objWorkingSection->offsetUnset($strSelector);
        }
        
        /**
         * Returns the resultset of the selector
         * 
         * @see         \ArrayAccess
         * @param       string              $strSelector
         * @return      XTemplate\ElementList
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function select($strSelector)
        {
            Params::string($strSelector, __CLASS__, __FUNCTION__);
            return $this->objWorkingSection->select($strSelector);
        }
        
        /**************************************************************************************************************/
        

        /**
         * Performs the pre or post parsers
         * 
         * @param       array               $arrParsers         The list of parsers
         * @throws      XTemplate\Parsers\Exceptions\ParsingException
         */
        private function performParsers(array $arrParsers)
        {
            $objEngine = $this->getEngineInstance();
            
            // Walk through the parser list
            foreach ($arrParsers as $strParserClass)
            {
                // Check for any overloads
                $objParser = $objEngine->createInstance($strParserClass, $this);
                
                // Check the class
                if (!$objParser instanceof Parsers\Base)
                {
                    throw new Parsers\Exceptions\ParsingException($strParserClass . " is not a valid parser.");
                }
            }
        }

        /**
         * Adds a postparser
         * 
         * @param       string              $strClassName
         */
        public function addPostParser($strClassName)
        {
            Params::string($strClassName, __CLASS__, __FUNCTION__);
            
            $this->arrPostParsers[] = $strClassName;
        }
        
        /**
         * Assignes a model to the template
         * 
         * @param       string              $strName
         * @param       mixed               $varModel
         * @return      XTemplate\View
         * @throws      XTemplate\Exceptions\ModelException
         */
        public function assign($strName, $varModel)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            // Is this class in strict mode?
            if (static::STRICT_MODE)
            {
                $strClassName = get_class($this);
                
                // Check the model definition
                if (!isset(self::$arrModelInterfaces[$strClassName][$strName]))
                {
                    throw new Exceptions\ModelException("Undefined model '". $strName ."'.");
                }
                
                Params::checkType($varModel, self::$arrModelInterfaces[$strClassName][$strName][self::TYPE], true);
            }
            
            $this->arrModels[$strName] = $varModel;
            return $this;
        }
        
        
        /**
         * Renders the template and returns the content HTML as string
         * 
         * @throws      XTemplate\Exceptions\RenderException
         */
        public function render()
        {
            // Do not render the template twice
            if ($this->bolRendered)
            {
                return;
            }
            
            // Perform the default rendering method
            $this->_render();
            
            // Render the sections
            $this->objTopLevelSection->render();
            
            // Perform the post parser
            $this->performParsers($this->arrPostParsers);
            $this->bolRendered = true;
        }
        
        /**
         * Returns the template as HTML
         * 
         * @return      string
         */
        public function __toString()
        {
            // The template must be rendered
            if (!$this->bolRendered)
            {
                // Since this method must not throw any exception, they are cought here and the error message will be
                // returned
                try
                {
                    $this->render();
                }
                catch (\Exception $objError)
                {
                    return $objError->getMessage();
                }
            }
            
            return $this->saveHTML();
        }
        
        /**
         * Returns the filename
         * 
         * @return      string
         */
        public function getFileName()
        {
            return $this->documentURI;
        }
        
        /**
         * Returns assigned datas from the name
         * 
         * @param       string              $strName
         * @return      mixed
         * @throws      XTemplate\Exceptions\RenderException
         */
        protected function getDatas($strName)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            
            // Are there datas with this name?
            if (!isset($this->arrModels[$strName]))
            {
                throw new Exceptions\RenderException("No datas with name '". $strName ."' assigned.");
            }
            
            return $this->arrModels[$strName];
        }
        
        /**
         * Returns the configuration for this instance
         * 
         * @return      XTemplate\Core\Config
         */
        protected function getConfig()
        {
            return $this->getEngineInstance()->getConfig();
        }

        /**
         * Creates the HTML output
         * 
         * @return      string
         */
        public function saveHTML()
        {
            // Remove the <remove>-Tags
            return str_replace(
                    array("<". self::REMOVE .">", "</". self::REMOVE .">"), 
                    array("", ""), 
                    parent::saveHTML());
        }
        
        /**
         * Custom rendering method. This method has to be implemented from the final view class developer. This method
         * is automatically called when the template is rendered. 
         * 
         * @throws      XTemplate\Exceptions\RenderException
         */
        protected abstract function _render();
        
        /**
         * This class defines a model interface
         * 
         * @param       string                  $strName        The name of the model
         * @param       string                  $strType        The type of the model. If the type is no primary type, 
         *                                                      it's assumed as a class name. You can find constants for
         *                                                      the primary types in XTemplate\Core\Constants
         * @param       mixed                   $varDefault     Default value
         */
        protected static function defModel($strName, $strType, $varDefault = null)
        {
            Params::string($strName, __CLASS__, __FUNCTION__);
            Params::string($strType, __CLASS__, __FUNCTION__);
            
            // Get the classname
            $strClassName = get_called_class();
            
            // Add the interface definitions
            self::$arrModelInterfaces[$strClassName][$strName] = array(
                self::TYPE          => $strType, 
                self::DEFAULT_VALUE => $varDefault
            );
        }
        
        /**
         * Loads the predefined model interface into the current object
         * 
         * @throws      XTemplate\Exceptions\ModelException
         */
        private function loadDefaultModels()
        {
            // Has the model interface for this class already been defined?
            if (!isset(self::$arrModelInterfaces[get_class($this)]))
            {
                // Define it
                static::defineModelInterface();
            }
            
            // Get the classname
            $strClassName = get_called_class();
            
            foreach (self::$arrModelInterfaces[$strClassName] as $strName => $arrMapping)
            {
                $this->assign($strName, $arrMapping[self::DEFAULT_VALUE]);
            }
        }
        
        /**
         * This method can be overwritten to define the model interface
         */
        protected static function defineModelInterface()
        {
            // Get the classname
            $strClassName = get_called_class();
            
            // Is there already a model interface for this class?
            if (!isset(self::$arrModelInterfaces[$strClassName]))
            {
                self::$arrModelInterfaces[$strClassName] = array();
            }
        }
    }
}
?>