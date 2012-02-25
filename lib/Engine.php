<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate
{
    use \UnexpectedValueException;
    use \XTemplate\Core\Config;
    use \XTemplate\Core\Params;
    use \XTemplate\Core\Constants;
    use \XTemplate\Core\Exceptions\InitException;
    
    /**
     * XTemplate main engine class. It controls the configuration of the egine and the instance handling. If different
     * developers use different settings for the template engine, they have to use different engine instances. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1b
     * @package     xtemplate
     */
    class Engine
    {
        /**
         * Define the version as string and as integer. Version 1.0 would be 10 etc.
         */
        const VERSION = "0.3";
        const VERSION_INT = 3;
        
        const UNIX_PATH_SEPARATOR = '/';
        const WINDOWS_PATH_SEPARATOR = '\\';
        
        /**
         * The path to the template engine
         * @var string
         */
        private static $strPath = "";
        /**
         * Whether or not the engine is used on a unix system
         * @var bool
         */
        private static $bolUNIX = true;
        /**
         * The path separator for the filesystem
         * @var string
         */
        private static $strPathSeparator = self::UNIX_PATH_SEPARATOR;
        /**
         * If the static properties have been initialized yet
         * @var bool
         */
        private static $bolInit = false;
        /**
         * The core files which cannot be loaded via autoloader. 
         * @var array
         */
        private static $arrCoreFiles = array(
            "Core/Params.php", 
            "Core/Interfaces/EnginePart.php", 
            "Core/EnginePartImpl.php", 
            "Core/Constants.php", 
            "Core/Autoloader.php", 
            "Core/Classes.php", 
            "Core/Config.php", 
            "Core/ConfigInterpretor.php", 
            "Core/XML.php", 
            "Core/Helper.php", 
            "Core/ConfigInterpretors/Interpretors.php", 
            "Core/ConfigInterpretors/Autoloader.php", 
            "Core/ConfigInterpretors/Config.php", 
            "Core/ConfigInterpretors/Overload.php", 
            "Core/ConfigInterpretors/Parsers.php", 
            "Core/ConfigInterpretors/Selectors.php", 
            "Core/ConfigInterpretors/Settings.php", 
            "Core/Exceptions/Exception.php",
            "Core/Exceptions/ConfigException.php",
            "Core/Exceptions/CoreException.php",
            "Core/Exceptions/InitException.php",
            // Uncomment this line to debug.
            // "Core/ExceptionHandler.php",
        );
        /**
         * Default engine instance with the standard configuraion
         * @var XTemplate\Engine
         */
        private static $objDefaultInstance = null;
        /**
         * Current domain
         * @var string
         */
        private static $strDomain = "";
        /**
         * Indicates if PHP is in command line mode
         * @var bool
         */
        private static $bolCLI = false;
        
        /**
         * Configuration
         * @var XTemplate\Core\Config
         */
        private $objConfig = null;
        /**
         * Registrated config interpretors
         * @var array
         */
        private $arrConfigInterpretors = array(
            "interpretors" => "XTemplate\\Core\\ConfigInterpretors\\Interpretors"
        );
        /**
         * Registrated class overloads
         * @var array
         */
        private $arrClassOverloads = array();
        
        /**
         * Initializes the static properties of the engine. This has only to be run one single time. 
         * 
         * @throws      UnexpectedValueException
         */
        public static function init()
        {
            // If the properties have already been initialized, break up
            if (self::$bolInit === true)
            {
                return;
            }
            
            // Determine the path to the template engine
            self::$strPath = dirname(__FILE__);

            // Does the engine run on an unix-like or a windows system?
            if (substr(self::$strPath, 0, 1) !== self::UNIX_PATH_SEPARATOR)
            {
                self::$strPathSeparator = self::WINDOWS_PATH_SEPARATOR;
                self::$bolUNIX = false;
            }
            self::$strPath .= self::$strPathSeparator;
            
            // Set the current domain
            // It might not be available in command line mode
            if (isset($_SERVER["HTTP_HOST"]))
            {
                self::$strDomain = $_SERVER["HTTP_HOST"];
            }
            
            // Determine if PHP is in command line mode
            self::$bolCLI = php_sapi_name() === "cli";
            
            // Include the core files
            self::includeCoreFiles();
            
            // Create the default instance
            self::$objDefaultInstance = new Engine(self::getAbsPath(Constants::DEFAULT_CONFIG_FILE));
            
            self::$bolInit = true;
        }
        
        /**
         * Includes the core files
         * 
         * @throws      UnexpectedValueException
         */
        private static function includeCoreFiles()
        {
            foreach (self::$arrCoreFiles as $strFile)
            {
                $strAbsFileName = self::getAbsPath($strFile);
                if (!file_exists($strAbsFileName))
                {
                    throw new UnexpectedValueException("The corefile '". $strFile ."' is not available.");
                }
                
                self::includeFile($strAbsFileName);
            }
        }
        
        /**
         * Returns the absolute path of a file from the engine
         * 
         * @param       string              $strFileName        The filename which shall be determined
         * @return      string
         */
        public static function getAbsPath($strFileName)
        {
            // At this point default typecheck cannot be done since the class might not be loaded
            
            return self::$strPath . $strFileName;
        }
        
        /**
         * Returns the path to the engine directory
         * 
         * @return      string
         */
        public static function getPath()
        {
            return self::$strPath;
        }
        
        /**
         * Returns the current domain
         * 
         * @return      string
         */
        public static function getDomain()
        {
            return self::$strDomain;
        }
        
        /**
         * Includes a file and looks for the correct separator
         * 
         * @param       string              $strInclude
         * @param       bool                $bolAppendPath      If the engine path shall be appended to the include string
         * @return      bool
         */
        public static function includeFile($strInclude, $bolAppendPath = false)
        {
            // If we are on a windows system, we have to replace the default unix separators through the windows ones
            if (!self::$bolUNIX)
            {
                $strInclude = str_replace(self::UNIX_PATH_SEPARATOR, self::WINDOWS_PATH_SEPARATOR, $strInclude);
            }

            // Append the engine path?
            if ($bolAppendPath)
            {
                $strInclude = self::$strPath . $strInclude;
            }

            try
            {
                require_once($strInclude);

                return true;
            }
            catch (\Exception $objError)
            {
                return false;
            }
        }
        
        /**
         * Returns the default instance of the engine
         * 
         * @return      XTemplate\Engine
         */
        public static function getDefaultInstance()
        {
            return self::$objDefaultInstance;
        }
        
        /**
         * Returns the path separator
         * 
         * @return      string
         */
        public static function getPathSeparator()
        {
            return self::$strPathSeparator;
        }
        
        /**
         * Returns if PHP is in command line mode
         * 
         * @return      bool
         */
        public static function isInCLIMode()
        {
            return self::$bolCLI;
        }
        
        /**
         * Creates a new engine instance
         * 
         * @param       string              $strConfigFile      The path to the config file
         */
        public function __construct($strConfigFile)
        {
            Params::string($strConfigFile, __FUNCTION__, __CLASS__);
            
            $this->objConfig = new Config($strConfigFile, $this);
        }
        
        /**
         * Registrates an config interpretor. It's allowed to overwrite existing entries!
         * 
         * @param       string              $strTagName         The name of the config tag
         * @param       string              $strClassName       The classname of the interpretor
         */
        public function registrateConfigInterpretor($strTagName, $strClassName)
        {
            Params::string($strTagName, __CLASS__, __FUNCTION__);
            Params::string($strClassName, __CLASS__, __FUNCTION__);
            
            $this->arrConfigInterpretors[$strTagName] = $strClassName;
        }
        
        /**
         * Returns the whole list of registrated config interpretors. It's an array like: tagname => classname
         * 
         * @return      array
         */
        public function getRegistratedConfigInterpretors()
        {
            return $this->arrConfigInterpretors;
        }
        
        /**
         * Registrates a class overload. 
         * 
         * @param       string              $strOriginalClass   The name of the class to overload
         * @param       string              $strClass           The name of the class which overloads the class
         */
        public function registrateClassOverload($strOriginalClass, $strClass)
        {
            Params::string($strOriginalClass, __CLASS__, __FUNCTION__);
            Params::string($strClass, __CLASS__, __FUNCTION__);
            
            $this->arrClassOverloads[$strOriginalClass] = $strClass;
        }
        
        /**
         * Returns the correct classname of a class
         * 
         * @param       string              $strClassName       The name of the class which shall be proved
         * @return      string
         */
        public function getClassName($strClassName)
        {
            Params::string($strClassName, __CLASS__, __FUNCTION__);
            
            // Is there a class overload for this class?
            if (isset($this->arrClassOverloads[$strClassName]))
            {
                return $this->getClassName($this->arrClassOverloads[$strClassName]);
            }
            
            // The class was not overloaded
            return $strClassName;
        }
        
        /**
         * Creates a new object instance
         * 
         * @param       string              $strClassName       The classname
         * @param       mixed               ...                 Constructor arguments
         * @return      object
         * @throws      \ReflectionException
         */
        public function createInstance($strClassName)
        {
            $arrArgs = func_get_args();
            // Remove the first argument since it's the classname
            array_shift($arrArgs);
            
            // Get the actual classname
            $strPerformName = $this->getClassName($strClassName);
            
            $objRefClass = new \ReflectionClass($strPerformName);
            
            // Create the new instance
            return $objRefClass->newInstanceArgs($arrArgs);
        }
        
        /**
         * Returns the configuration object
         * 
         * @return      XTemplate\Core\Config
         */
        public function getConfig()
        {
            return $this->objConfig;
        }
    }
    Engine::init();
}
?>