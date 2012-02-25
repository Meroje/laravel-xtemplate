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
     * This exception is thrown if any exception happens during the init process. If so, you should check your x:template
     * version for any updates and the rights of your filesystem. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class InitException extends Exception {}
}
?>