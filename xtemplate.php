<?php

namespace XTemplate
{
	use \XTemplate\Core\Helper,\Laravel;
	
	/**
	 * This class gives you a little bit more comfort. You just have to add a TEMPLATE constant to your class with the 
	 * path to your template relative to your class file. 
	 * 
	 * @author      Tobias Pohlen
	 * @version     0.1
	 * @package     xtemplate
	 */
	abstract class XTemplate extends View
	{
		/**
		 * Init method which can be overwritten
		 */
		protected function init()
		{}
		
		/**
		 * Creates a new view object
		 */
		public function __construct(Engine $objEngine = null)
		{
			// Determine the path to the template file
			$objRefClass = new \ReflectionClass($this);
			$strPathSeparator = Engine::getPathSeparator();
			
			$arrFolder = explode($strPathSeparator, dirname($objRefClass->getFileName()));
			$strFolder = implode($strPathSeparator, $arrFolder) . $strPathSeparator;

			$view = str_replace('.', '/', static::TEMPLATE);

			$root = Laravel\Bundle::path(Laravel\Bundle::name($view)).'templates/';

			$path = $root.Laravel\Bundle::element($view).Laravel\Config::get('xtemplate::xtemplate.tpl');
			
			parent::__construct($path, $objEngine);
		}
	}
}
?>