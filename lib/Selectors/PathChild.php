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
     * This selector lets you select a certain position of the childtree in the dom:
     * e.g.
     * 
     * div:first-child would match <div class="match"></div><div class="dontmatch"></div>
     * 
     * The following W3C selectors are supported: 
     * - first-child
     * - last-child
     * - nth-child
     * - nth-last-child
     * - only-child
     * - empty
     * - disabled
     * - checked
     * 
     * @author      Tobias Pohlen
     * @version     0.4
     * @package     xtemplate.selectors
     */
    class PathChild extends PathSelectorBase
    {
        /**
         * Defines the regex pattern for any pseudo class argument
         */
        const REGEX = "/^(?<name>[A-Za-z_]{1}[A-Za-z0-9_\-]*)((\((?<argument>.*)\)))*/";
        const NAME = "name";
        const ARGUMENT = "argument";
        const FUNC_FLAG = "f:";
        const ARG_REPL  = "{arg}";
        
        /**
         * Define the allowed values
         * @var array
         */
        private static $arrXPathMapping = array(
            "first-child"       => "position()=1", 
            "last-child"        => "position()=last()", 
            "nth-child"         => "f:nthChild", 
            "nth-last-child"    => "f:nthLastChild", 
            "nth-of-type"       => "f:nthOfType", 
            "nth-last-of-type"  => "f:nthLastOfType", 
            "only-child"        => "position()=1 and position()=last()", 
            "empty"             => "self::*=''", 
            "disabled"          => "@disabled='disabled'",
            "checked"           => "@checked='checked'",
        );
        
        /**
         * Selector argument
         * @var string
         */
        private $strArg = "";
        /**
         * Model instance
         * @var XTemplate\Selectors\SelectorModel
         */
        private $objModel = null;
        
        /**
         * Selects the element
         * 
         * @param       string              $strOperator        The select operator (e.g. '#')
         * @param       string              $strArgument        Everything behinde the operator
         * @param       XTemplate\Selectors\SelectorModel   $objModel
         * @throws      XTemplate\Selectors\Exceptions\SelectorException
         */
        public function performSelector($strOperator, $strArgument, SelectorModel $objModel)
        {
            $this->objModel = $objModel;
            
            // Parse the given argument
            $arrMatch = array();
            $varResult = @ preg_match(self::REGEX, $strArgument, $arrMatch);
            
            if ($varResult === 0 || $varResult === false)
            {
                // The selector argument is invalid or the parsing failed
                throw new Exceptions\SelectorException("Invalid selector argument '". $strOperator . $strArgument ."'.");
            }
            
            // Is this argument registrated?
            if (!isset(self::$arrXPathMapping[$arrMatch[self::NAME]]))
            {
                // The argument is not implemented yet
                throw new Exceptions\SelectorException("Invalid selector argument '". $strOperator . $strArgument ."'.");
            }
            
            // Is there an argument?
            if (isset($arrMatch[self::ARGUMENT]))
            {
                $this->strArg = $arrMatch[self::ARGUMENT];
            }
            
            // Get the XPath mapping
            $strMapping = self::$arrXPathMapping[$arrMatch[self::NAME]];
            
            // Is this a function call?
            $intFuncFlagLength = strlen(self::FUNC_FLAG);
            if (substr($strMapping, 0, $intFuncFlagLength) === self::FUNC_FLAG)
            {
                // Get the correct function name
                $strFuncName = substr($strMapping, $intFuncFlagLength, strlen($strMapping) - $intFuncFlagLength);
                
                // Does this function exist?
                if (!method_exists($this, $strFuncName))
                {
                    throw new Exceptions\SelectorException("The parsing function '". $strFuncName ."' is not implemented yet.");
                }
                
                // Launch the parsing function
                $this->$strFuncName();
                return;
            }
            
            // This is no function call. Simply assign the XPath
            $objModel->pushAttribute(
                    str_replace(self::ARG_REPL, $this->strArg , self::$arrXPathMapping[$arrMatch[self::NAME]]));
        }
        
        /**
         * Creates the xpath for :nth-last-child.
         */
        private function nthLastChild()
        {
            // Remove the last path entry
            $this->objModel->popPath();
            // Add a non tag related entry
            $this->objModel->pushPath("/child::*");
            $this->objModel->pushAttribute("position()=last()-". (((int) $this->strArg) - 1));
        }
        
        /**
         * Creates the xpath for :nth-of-type.
         */
        private function nthOfType()
        {
            // Remove the last path entry
            $strOldPath = $this->objModel->popPath();
            // Add a non tag related entry
            $this->objModel->pushPath(
                "/child" . substr($strOldPath, strpos($strOldPath, "::"), strlen($strOldPath) - strpos($strOldPath, "::"))
            );
            $this->objModel->pushAttribute("position()=". $this->strArg);
        }
        
        /**
         * Creates the xpath for :nth-last-of-type.
         */
        private function nthLastOfType()
        {
            // Remove the last path entry
            $strOldPath = $this->objModel->popPath();
            // Add a non tag related entry
            $this->objModel->pushPath(
                "/child" . substr($strOldPath, strpos($strOldPath, "::"), strlen($strOldPath) - strpos($strOldPath, "::"))
            );
            $this->objModel->pushAttribute("position()=last()-". (((int) $this->strArg) - 1));
        }
        
        /**
         * Creates the xpath for :nth-child.
         */
        private function nthChild()
        {
            // Remove the last path entry
            $this->objModel->popPath();
            // Add a non tag related entry
            $this->objModel->pushPath("/child::*");
            $this->objModel->pushAttribute("position()=". (int) $this->strArg);
        }
    }
}
?>