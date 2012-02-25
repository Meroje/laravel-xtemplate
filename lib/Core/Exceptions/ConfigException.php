<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Core\Exceptions
{
    /**
     * This exception is mostly thrown by the config interpretors. So if it's thrown you should check your configuration
     * for any errors. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class ConfigException extends Exception {}
}
?>