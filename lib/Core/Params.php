<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core
{
    /**
     * This is a helper class to check method and function parameters of their correct type. Arrays and objects should
     * directly be checked by typehinting. 
     * 
     * @author      Tobias Pohlen
     * @version     0.4
     * @package     xtemplate.core
     */
    class Params
    {
        const ERROR_LEVEL = \E_USER_WARNING;
        
        /**
         * Returns the type of a variable as string
         * 
         * @param       mixed                   $varParam
         * @return      string
         */
        private static function getType($varParam)
        {
            if (is_string($varParam))
            {
                return "string";
            }
            
            if (is_int($varParam))
            {
                return "integer";
            }
            
            if (is_double($varParam))
            {
                return "double";
            }
            
            if (is_float($varParam))
            {
                return "float";
            }
            
            if (is_bool($varParam))
            {
                return "boolean";
            }
            
            if (is_array($varParam))
            {
                return "array";
            }
            
            if (is_object($varParam))
            {
                return "object";
            }
            
            if ($varParam === null)
            {
                return "null";
            }
            
            return "unkown";
        }
        
        /**
         * Triggers the error 
         * 
         * @param       &mixed                  $varParam
         * @param       string                  $strExpected        What the method originally expected
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        private static function triggerError($varParam, $strExpected, $strClassName, $strMethodName)
        {
            trigger_error(
                $strClassName ."->". $strMethodName ." expects a ". $strExpected .". ". self::getType($varParam) ." given.", 
                self::ERROR_LEVEL);
        }
        
        /**
         * Checks if a parameter is a string. 
         * 
         * @param       &string                 $varParam
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        public static function string(&$varParam, $strClassName, $strMethodName)
        {
            if (!is_string($varParam))
            {
                // Everything except arrays and objects without a toString method can be converted to string
                if (is_array($varParam) || (is_object($varParam) && !method_exists($varParam, "__toString")))
                {
                    self::triggerError($varParam, "string", $strClassName, $strMethodName);
                }
                $varParam = (string) $varParam;
            }
        }
        
        /**
         * Checks if a parameter is an integer. 
         * 
         * @param       &int                    $varParam
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        public static function int(&$varParam, $strClassName, $strMethodName)
        {
            if (!is_int($varParam))
            {
                // Only boolean can be converted into integer
                if (!is_bool($varParam))
                {
                    self::triggerError($varParam, "integer", $strClassName, $strMethodName);
                }
                $varParam = (int) $varParam;
            }
        }
        
        /**
         * Checks if a parameter is a double. 
         * 
         * @param       &double                 $varParam
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        public static function double(&$varParam, $strClassName, $strMethodName)
        {
            if (!is_double($varParam))
            {
                // Boolean, integer and float can be converted to double
                if (!is_int($varParam) && !is_float($varParam) && !is_bool($varParam))
                {
                    self::triggerError($varParam, "double", $strClassName, $strMethodName);
                }
                $varParam = (double) $varParam;
            }
        }
        
        /**
         * Checks if a parameter is a float. 
         * 
         * @param       &float                  $varParam
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        public static function float(&$varParam, $strClassName, $strMethodName)
        {
            if (!is_float($varParam))
            {
                // Only integer and boolean can be implicitly converted into float
                if (!is_int($varParam) && !is_bool($varParam))
                {
                    self::triggerError($varParam, "float", $strClassName, $strMethodName);
                }
                $varParam = (float) $varParam;
            }
        }
        
        /**
         * Checks if a parameter is a boolean. 
         * 
         * @param       &float                  $varParam
         * @param       string                  $strClassName
         * @param       string                  $strMethodName
         */
        public static function bool(&$varParam, $strClassName, $strMethodName)
        {
            // Nothing can be implicitly converted into boolean
            if (!is_bool($varParam))
            {
                self::triggerError($varParam, "boolean", $strClassName, $strMethodName);
                $varParam = (bool) $varParam;
            }
        }
        
        /**
         * Checks the type of a variable and throws an exception when the type is wrong
         * 
         * @param       mixed                   $varParams
         * @param       string                  $strTypeName
         * @throws      XTemplate\Exceptions\ModelException
         */
        public static function checkType($varParam, $strTypeName)
        {
            $strType = self::getType($varParam);
            if (strtolower($strType) != strtolower($strTypeName) && $strType !== "null" && $strType !== "object")
            {
                throw new \XTemplate\Exceptions\ModelException("Model must be a '". $strTypeName ."'. "
                        ."'". $strType ."' given.");
            }
            elseif ($strType === "object" && $varParam !== null && !$varParam instanceof $strTypeName)
            {
                throw new \XTemplate\Exceptions\ModelException("Model must be an instance of '". $strTypeName ."'. "
                        ."'". get_class($varParam) ."' given.");
            }
        }
    }
}
?>