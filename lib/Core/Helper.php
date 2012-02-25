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
     * This is a helper class which contains some useful static methods. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Helper
    {
        /**
         * Explodes a string, trims the array entries and removes empty entries. 
         * 
         * @param       string              $strInput
         * @param       string              $strExplode
         * @return      array
         */
        public static function trimExplode($strInput, $strExplode)
        {
            Params::string($strInput, __CLASS__, __FUNCTION__);
            Params::string($strExplode, __CLASS__, __FUNCTION__);
            
            $arrSplit  = explode($strExplode, $strInput);
            $arrReturn = array();
            
            foreach ($arrSplit as $strEntry)
            {
                // Remove useless whitespace
                $strEntry = trim($strEntry);
                
                // Is this entry valid?
                if ($strEntry !== "")
                {
                    $arrReturn[] = $strEntry;
                }
            }
            
            return $arrReturn;
        }
        
        /**
         * Creates a path relative from a specific one. e.g. /var/www and ../index.html becomes /var/index.html
         * 
         * @param       string              $strBase
         * @param       string              $strRel
         * @return      string
         */
        public static function createRelPath($strBase, $strRel)
        {
            Params::string($strBase, __CLASS__, __FUNCTION__);
            Params::string($strRel, __CLASS__, __FUNCTION__);
            
            $strPathSeparator = \XTemplate\Engine::getPathSeparator();
            
            $intOffset = 0;
            $strPath = strrev($strBase);
            while (substr($strRel, $intOffset, 3) == "..".$strPathSeparator && $intOffset < strlen($strRel))
            {
                $strPath = substr($strPath, strpos($strPath, $strPathSeparator), strlen($strPath) - strpos($strPath, $strPathSeparator));
                $intOffset += 3;
            }
            $strPath = strrev($strPath);

            if ($strPath[strlen($strPath) - 1] !== $strPathSeparator)
            {
                $strPath .= $strPathSeparator;
            }

            $strPath .= substr($strRel, $intOffset, strlen($strRel) - $intOffset);
            
            return $strPath;
        }
    }
}
?>