# XTemplate Bundle

A [XTemplate](http://xtemplate.net/) bundle for Laravel.
Thanks to [sparksp](https://github.com/sparksp) for this sample file.

## Installation

Install via the Artian CLI:

```sh
php artisan bundle:install xtemplate
```

Or download the zip and unpack into your **bundles** directory.

## Bundle Registration

You need to register XTemplate with your application before you can use it. Simply edit application/bundles.php and add the following to the array:

```php
	'xtemplate' => array(
		'auto' => true,
		'autoloads' => array(
			'map' => array(
				'XTemplate\\ViewLoader' => '(:bundle)/viewloader.php',
				'XTemplate\\XTemplate' => '(:bundle)/xtemplate.php',
			)
		)
	),
```

You will also need to set some aliases for convenience:

```php
		'View'       => 'XTemplate\\ViewLoader',
		'XTemplate'  => 'XTemplate\\XTemplate',
```

## Guide

Your **views** folder will be used for XTemplate classes, with the original dot-slash notation untouched. You have to name your classes following Laravel rules, ie: for the path **home.index** the class name is **Home_Index**.

```php
class Home_Index extends XTemplate
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
```

The **TEMPLATE** constant is from the **ComfortView** class, it is used to set the file XTemplate will use, in the folder **templates**. You need to create this folder next to **views** in your bundle or application. The **TEMPLATE** file is located followinf original Laravel loading of views.