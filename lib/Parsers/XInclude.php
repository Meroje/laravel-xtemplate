<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Parsers
{
    use \XTemplate\Parser;
    use \XTemplate\Core\Constants;
    use \XTemplate\Core\Helper;
    
    /**
     * This parser allows you to use xinclude tags in your templates to include other template files. 
     * 
     * @author      Tobias Pohlen
     * @version     0.3
     * @package     xtemplate.parsers
     */
    class XInclude extends Base
    {
        const XINCLUDE_NS_URL   = "http://www.w3.org/2001/XInclude";
        const XINCLUDE_NS       = "xi";
        const TAGNAME           = "xinclude";
        const TAGNAME_XI        = "include";
        const ATTRIBUTE         = "href";
        
        /**
         * Substitues the x:include tags in the template
         * 
         * @throws      XTemplate\Exceptions\ParsingException
         */
        protected function parse()
        {
            // Get all x:include tags
            $objIncludeNodes = $this->objTemplate->getElementsByTagName(self::TAGNAME);
            
            // Put the length into a variable since it is computed every single call
            $intLength = $objIncludeNodes->length;
            
            if ($intLength > 0)
            {
                $i = 0;
                // This is the xtemplate include node "x:include"
                $objXTemplateInclude = $objIncludeNodes->item($i);

                // Create an xi:include node to include other xml files
                $objNewInclude = $this->objTemplate->createElementNS(
                        self::XINCLUDE_NS_URL, 
                        self::XINCLUDE_NS .':'. self::TAGNAME_XI);

                // Append the path to the include file
                $objNewInclude->setAttribute(self::ATTRIBUTE, $objXTemplateInclude->getAttribute(self::ATTRIBUTE));

                // Insert the new one before the xtemplate one
                $this->objTemplate->documentElement->insertBefore($objNewInclude, $objXTemplateInclude);

                // Remove the old include
                $this->objTemplate->documentElement->removeChild($objXTemplateInclude);

                // Perform the native xinclude procedure
                $this->objTemplate->xinclude();
            }
            
            if ($intLength > 1)
            {
                // You cannot replace the xinlcude tags in a loop since the dom is modified and the refrences get lost
                $this->parse();
            }
        }
    }
}
?>