<?php
/**
 * x:template - PHP based template engine
 * Copyright (c) 2011 by Tobias Pohlen <tobias.pohlen@xtemplate.net>
 * 
 * Released under the GPL License.
 */
namespace XTemplate\Parsers
{
    use \XTemplate\Core\Classes;
    
    /**
     * This post rendering parser removes all xid attributes from the rendered template. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.parsers
     */
    class RemoveXID extends Base
    {
        /**
         * Removes the XID attributes
         * 
         * @throws      XTemplate\Exceptions\ParsingException
         */
        protected function parse()
        {
            // Prepare the xpath select
            $strXPath = "//*[@xid]";
            $objXPath = $this->objTemplate->getEngineInstance()->createInstance(Classes::DOM_XPATH, $this->objTemplate);
            
            $objResult = $objXPath->query($strXPath);
            
            if ($objResult === false)
            {
                throw new Exceptions\ParsingException("Could not perform xpath to remove the xid attr.");
            }
            
            // Transfer the length to a variable since it is computed each time it is called
            $intLength = $objResult->length;
            for ($i = 0; $i < $intLength; $i++)
            {
                // Remove the attribute
                $objResult->item($i)->removeAttribute("xid");
            }
        }
    }
}
?>