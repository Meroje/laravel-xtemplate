<?php

class Test extends XTemplate
{
	const TEMPLATE = "template";

	/**
	 * Render the template
	 */
	protected function _render()
	{
		// Access the div element via CSS selector ".hello-world"
		$this[".hello-world"] = "Hello World!";
	}
}