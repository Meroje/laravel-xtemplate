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
     * This static class contains all classnames of the engine as string. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class Classes
    {
        const CORE_ENGINE = "XTemplate\Core\Engine";
        const CORE_CONFIG = "XTemplate\Core\Config";
        const CORE_ENGINEPARTIMPL = "XTemplate\Core\EnginePartImpl";
        const CORE_XML = "XTemplate\Core\XML";
        const CORE_PARAMS = "XTemplate\Core\Params";
        
        const CORE_EXCEPTIONS_COREEXCEPTION = "XTemplate\Core\Exceptions\CoreException";
        const CORE_EXCEPTIONS_EXCEPTION = "XTemplate\Core\Exceptions\Exception";
        const CORE_EXCEPTIONS_INITEXCEPTION = "XTemplate\Core\Exceptions\InitException";
        
        const CORE_INTERFACES_ENGINEPART = "XTemplate\Core\Interfaces\EnginePart";
        
        const SECTION = "XTemplate\Section";
        const DOM_XPATH = "DOMXPath";
        const ELEMENTLIST = "XTemplate\ElementList";
        
        const SELECTORS_TAG = "XTemplate\Selectors\Tag";
        
        const EBNF_DEF_EPSILON = "XTemplate\Parser\EBNF\Definitions\Epsilon";
        const EBNF_DEF_MULTIPLE = "XTemplate\Parser\EBNF\Definitions\Multiple";
        const EBNF_DEF_NONDUTY = "XTemplate\Parser\EBNF\Definitions\NonDuty";
        const EBNF_DEF_NONTERMINAL = "XTemplate\Parser\EBNF\Definitions\NonTerminal";
        const EBNF_DEF_REGEX = "XTemplate\Parser\EBNF\Definitions\RegEx";
        const EBNF_DEF_SELECTION = "XTemplate\Parser\EBNF\Definitions\Selection";
        const EBNF_DEF_TERMINAL = "XTemplate\Parser\EBNF\Definitions\Terminal";
    }
}
?>