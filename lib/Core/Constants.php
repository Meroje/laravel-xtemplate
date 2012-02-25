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
     * This class contains some important constants for the whole engine
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Constants
    {
        const XML_NS = "x";
        const XML_NS_URL = "http://xtemplate.net/xmlns.html";
        
        const DEFAULT_CONFIG_FILE = "xml/config.xml";
        
        /**
         * Var types
         */
        const STRING        = "string";
        const INT           = "int";
        const BOOL          = "bool";
        const DOUBLE        = "double";
        const FLOAT         = "float";
        const DATA_ARRAY    = "array";
        
        const DOM_VERSION   = "1.0";
        const DOM_CHARSET   = "utf-8";
    }
}
?>