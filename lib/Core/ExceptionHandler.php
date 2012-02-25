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
     * This exceptionhandler is more for developing reasons. It gives you a nice output in case of an uncatched 
     * exception.
     * 
     * @author      Tobias Pohlen
     * @version     0.1
     * @package     xtemplate.core
     */
    class ExceptionHandler
    {
        private static $o = null;
        
        /**
         * Syntax highlighter: language constructs
         * @var array
         */
        private static $arrLanguageConstructs = array(
            "elseif","if", "else", "while", "do", "foreach", "for", "break", "continue", "switch", "declare", "return", 
            "require_once", "include_once", "goto", "require", "include", "new", "self", "static", "function", "public", 
            "private", "protected", "echo", "throw"
        );
        
        public function __construct()
        {
            self::$o = $this;
            set_exception_handler(array($this, "handleException"));
        }
        
        /**
         * Exception handler. The exception handler does not work with template since it would cause an endles recursion
         * if the template threw the exception. 
         * 
         * @param        \Exception        $objException
         */
        public static function handleException(\Exception $objException)
        {
            // Get the image path
            $strPath = substr(
                    \XTemplate\Engine::getPath(), 
                    strlen($_SERVER["DOCUMENT_ROOT"]), 
                    strlen(\XTemplate\Engine::getPath()) - strlen($_SERVER["DOCUMENT_ROOT"]));
            
            echo '<div style="border: solid 2px #9a9a9a; padding: 20px; margin: 30px; font-family: Courier New, Courier New, monospace; font-size: 14px; color: #040404;">';
            printf("<b>%s:</b><i>%s</i><br />%s<br /><br />", "Exception", get_class($objException), $objException->getMessage());
            $arrTrace = $objException->getTrace();
            
            self::printExceptionFile($objException->getFile(), $objException->getLine());
            
            // Remove this function from the trace entry
            foreach ($arrTrace as $arrEntry)
            {
                printf("%s: %s<br />\n", "Class", $arrEntry["class"]);
                printf("%s: %s<br />\n", "Method", $arrEntry["function"]);
                self::printExceptionFile($arrEntry["file"], $arrEntry["line"]);
            }
            echo '</div>';
        }
        
        /**
         * Prints a file content for the exception report
         * 
         * @param        string            $strFile
         * @param        int                $intLine
         */
        private static function printExceptionFile($strFile, $intLine)
        {
            // Try to read the file content
            $hdlFile = @ fopen($strFile, "r");
            if (!$hdlFile)
            {
                return;
            }
            
            $strContent = fread($hdlFile, @filesize($strFile));
            @fclose($hdlFile);
            
            // Could the content be read correctly?
            if (!$strContent)
            {
                return;
            }
            
            // Split the file into lines
            $arrLines = explode("\n", $strContent);
            
            // Print the lines - including some other lines
            
            // Define which lines shall be used
            $i = $intLine - 2;
            $j = $intLine + 2;
            
            if ($i < 0)
            {
                $i = 0;
            }
            if ($j > count($arrLines))
            {
                $j = count($arrLines);
            }
            
            printf("%s: %s<br /><br />\n", "File: ", $strFile);
            echo '<div style="padding: 10px; background-color: #fdfdfd; border: solid 2px #404040;">';
            for (;$i <= $j; $i++)
            {
                // Highlight the line in which the exception was thrown
                if ($i === $intLine)
                {
                    printf("<strong>%s: %s</strong><br />\n", $i, self::prepareCode($arrLines[$i - 1]));
                }
                else
                {
                    printf("%s: %s<br />\n", $i, self::prepareCode($arrLines[$i - 1]));
                }
            }
            
            echo "</div><br /><br />";
        }
        
        /**
         * Prepares a line of code to be shown in the exception handler. This also includes a very little syntax 
         * highlighter. 
         * 
         * @param       string                $strLine
         * @return      string
         */
        private static function prepareCode($strLine)
        {
            // Do a very little syntax highlighting
            $strLine = htmlspecialchars($strLine);
            
            // Handle <? php ? > tags
            $strLine = preg_replace('/(&lt;\?php|\?&gt;)/', '<strong>$0</strong>', $strLine);
            
            // Hightlight strings
            $strLine = preg_replace('/"[^"]+"/', '<span style="color: #00ff00;">$0</span>', $strLine);
            
            // Highlight some language constructs
            $strLine = preg_replace(
                    '/( |\t|^)('. implode('|', self::$arrLanguageConstructs) .')/', 
                    '<span style="color: #0000ff;">$0</span>', 
                    $strLine);
            
            // Highlight variables
            $strLine = preg_replace('/\$[A-Za-z0-0_]+/', '<span style="color: #ff0000;">$0</span>', $strLine);
            
            // Replace tabs with white spaces
            $strLine = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $strLine);
            
            return $strLine;
        }
    }
    new ExceptionHandler;
}
?>