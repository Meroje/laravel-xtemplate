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
     * This is an extension of the SimpleXMLElement class which contains some useful functions. 
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class XML extends \SimpleXMLElement
    {
        /**
         * Returns a new XML instance from a xml file
         * 
         * @param       string              $strFile            The path to the XML file
         * @return      XTemplate\Core\XML
         * @throws      XTemplate\Core\Exceptions\CoreException
         */
        public static function getInstance($strFile)
        {
            Params::string($strFile, __CLASS__, __FUNCTION__);
            
            $objXML = @ simplexml_load_file($strFile, Classes::CORE_XML);
            if (!$objXML instanceof XML)
            {
                // The file could not be loaded
                throw new Exceptions\CoreException("The xml file '". $strFile ."' could not be loaded as XML.");
            }
            
            return $objXML;
        }
        
        /**
         * Returns the value of an attribute
         * 
         * @param       string        $strName            Name of the attribute
         * @param       string        $strNameSpace        Namespace name
         * @return      string
         */
        public function getAttribute ($strName, $strNameSpace = "")
        {
            if ($this->attributes($strNameSpace, true)->$strName === null)
            {
                return null;
            }
            return (string) $this->attributes($strNameSpace, true)->$strName;
        }
        
        /**
         * Removes an attribute from the element
         * 
         * @param       string        $strName            Name of the attribute
         * @param       string        $strNameSpace        Namespace name
         */
        public function removeAttribute($strName, $strNameSpace = "")
        {
            try 
            {
                unset($this->attributes($strNameSpace, true)->$strName);
            }
            catch (Exception $objError){}
        }
        
        /**
         * Puts a whole XML tree into the document
         * Does not work with namespace elements. They get dropped. 
         *
         * @param       XML            $objTree            The root node of the tree which actual shall be inserted
         * @param       XML            $objPosition        XML node where the tree shall be placed before
         * @param       book        $bolReplace            If true, the position marker will be replaced
         * @return      bool
         */
        public function insertXMLTree (XML $objTree, XML $objPosition = null, $bolReplace = false)
        {
            // If the tree hasn't got any children, break down
            if ($objTree->count() == 0)
            {
                return false;
            }
            
            // Did the user give a position element?
            if ($objPosition === null)
            {
                // No, create one
                $objPosition = $this->addChild("temp");
                $bolReplace = true;
            }
            
            foreach ($objTree->children () as $objChild)
            {
                $arrDomDocNodes = self::getSameDocDomNodes($objPosition, $objChild);
                $objDomPos = $arrDomDocNodes[0];
                $objDomChild = $arrDomDocNodes[1];
                
                
                $objNew = $objDomPos->parentNode->insertBefore($objDomChild, $objDomPos);
            }
            
            if ($bolReplace)
            {
                $objPosition->remove();
            }
            
            StopWatch::addTime("insertXMLTree End");
            return true;
        }
        
        /**
         * Creates a childnode with a CDATA section in it to put there any content which you want
         * 
         * @param       string            $strName        Name of the new childnode
         * @param       string            $strValue        Content of the CDATA section
         * @return      XML
         */
        public function addCDataChild($strName, $strValue)
        {
            $objNodeOld  = dom_import_simplexml ($this);
            $objNodeNew  = new \DOMNode ();
            $objDom      = new \DOMDocument ();
            
            $objDataNode = $objDom->appendChild ($objDom->createElement($strName));
            $objDataNode->appendChild ($objDom->createCDATASection($strValue));
            $objNodeTarget = $objNodeOld->ownerDocument->importNode ($objDataNode, true);
            
            $objNodeOld->appendChild ($objNodeTarget);
            return simplexml_import_dom ($objNodeTarget, __CLASS__);
        }
    
        /**
         * Appends a chilnode to this document
         * 
         * @param       XML                $objChild        The childnode which shall be appended to this document
         */
        public function appendChild(XML &$objChild)
        {
            $arrDocDomNodes = self::getSameDocDomNodes($this, $objChild);
            $objDocNode = $arrDocDomNodes[0];
            $objDocChild = $arrDocDomNodes[1];
            
            $objDocNode->appendChild($objDocChild);
            $objChild = simplexml_import_dom($objDocChild, __CLASS__);
        }
    
        /**
         * Removes a childnode form this element
         * 
         * @param       XML                $objChild        Childnode which shall be removed
         */
        public function removeChild(XML $objChild)
        {
            $objNode = dom_import_simplexml($this);
            
            $objChild = dom_import_simplexml($objChild);
            $objNode->removeChild($objChild);
        }
        
        /**
         * Replaces a childnode in this document
         * 
         * @param       XML                $objOld            The childnode which shall be replaced
         * @param       XML                $objNew            The new childnode
         */
        public function replaceChild(XML $objOld, XML &$objNew)
        {
            $arrDomDocNodes = self::getSameDocDomNodes($objOld, $objNew);
            $objOld = $arrDomDocNodes[0];
            $objDocNew = $arrDomDocNodes[1];
            
            $objOld->parentNode->replaceChild($objDocNew, $objOld);
            $objNew = simplexml_import_dom($objDocNew, __CLASS__);
        }
    
        /**
         * Removes the current node from the owner document
         */
        public function remove()
        {
            $objNode = dom_import_simplexml($this);
            $objNode->parentNode->removeChild($objNode);
        }
    
        /**
         * Replaces the current node witht the new node in the owner document
         * @param       XML                $objNew            New childnode
         */
        public function replace(XML &$objNew)
        {
            $arrDomDocNodes = self::getSameDocDomNodes($this, $objNew);
            $objDocThis = $arrDomDocNodes[0];
            $objDocNew  = $arrDomDocNodes[1];
            if ($objDocThis->parentNode == null)
            {
                Functions::addError("Could not replace XML node. ");
            }
            $objDocThis->parentNode->replaceChild($objDocNew, $objDocThis);
            $objNew = simplexml_import_dom($objDocNew, __CLASS__);
        }
            
        /**
         * Static utility method to get two dom elements and ensure that the second one
         * is part of the same document than the first one. 
         * 
         * @param       XML                $objNode1
         * @param       XML                $objNode2
         * @return      array                            array(node1, node2)
         */
        protected static function getSameDocDomNodes(XML $objNode1, XML $objNode2)
        {
            $objNode1 = dom_import_simplexml($objNode1);
            $objNode2 = dom_import_simplexml($objNode2);
            if(!$objNode1->ownerDocument->isSameNode($objNode2->ownerDocument))
            {
                $objNode2 = $objNode1->ownerDocument->importNode($objNode2, true);
            }
            return array($objNode1, $objNode2);
        }
        
        /**
         * Creates an array form this xml tree
         * 
         * @return       array
         */
        public function toArray()
        {
            $arrReturn = array();
            
            foreach ($this->children() as $objChild)
            {
                $strName = (string) $objChild->getName();
                
                // Does this child has got any children?
                if ($objChild->count() > 0)
                {
                    $arrReturn[$strName] = $objChild->toArray();
                }
                else
                {
                    $varValue = (string) $objChild;
                    
                    // No, create a subchild
                    switch ($objChild->getAttribute("type"))
                    {
                        case "int":
                        case "integer":
                            $arrReturn[$strName] = (int) $objChild;
                            break;
                        
                        case "bool":
                        case "bol":
                        case "boolean":
                        case "bln":
                            if ($varValue == "true" || $varValue == "1")
                            {
                                $arrReturn[$strName] = true;
                            }
                            else
                            {
                                $arrReturn[$strName] = false;
                            }
                            break;
                        
                        case "double":
                        case "float":
                        case "dbl":
                        case "flt":
                            $arrReturn[$strName] = (float) $varValue;
                            break;
                        
                        case "":
                        case "string":
                        case "str":
                        default:
                            $arrReturn[$strName] = (string) $objChild;
                            break;
                    }
                }
            }
            
            return $arrReturn;
        }
        
        /**
         * Creates a new XML object from an array
         * 
         * @param       array            $arrElement
         * @param       string            $strRoot        Root node name
         * @return      XML
         */
        public static function fromArray(array $arrElement, $strRoot = "root", XML $objXML = null)
        {
            if ($objXML === null)
            {
                // This is the first level of the recursion
                // create a new XML object
                $objXML = new XML("<". $strRoot ." />");
            }
            
            foreach ($arrElement as $strName => $varSub)
            {
                // It might be that there are integer key
                // Add a char before a number
                if (preg_match('/^[0-9]+.*/', $strName))
                {
                    $strName = "xml" . $strName;
                }
                
                if (!is_array($varSub))
                {
                    $objXML->addCDataChild($strName, $varSub);
                }
                else
                {
                    $objNew = $objXML->addChild($strName);
                    self::fromArray($varSub, "", $objNew);
                }
            }
            
            return $objXML;
        }
    }
}
?>