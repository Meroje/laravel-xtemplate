<?php namespace XTemplate; use Laravel;

class ViewLoader extends Laravel\View {

	/**
	 * Get the path to a given view on disk.
	 *
	 * @param  string  $view
	 * @return string
	 */
	protected function path($view) {
		$view = str_replace('.', '/', $view);

		$root = Laravel\Bundle::path(Laravel\Bundle::name($view)).'views/';

		if (file_exists($path = $root.Laravel\Bundle::element($view).Laravel\Config::get('xtemplate::xtemplate.extension')))
		{
			return $path;
		}

		throw new \Exception("View [$view] does not exist.");
	}

	/**
	 * Get the evaluated string content of the view.
	 *
	 * @return string
	 */
	public function render() {
		// Events ^^
		Laravel\Event::fire("laravel.composing: {$this->view}", array($this));

		$class = Laravel\Str::classify(Laravel\Bundle::element($this->view));

		require $this->path($this->view);

		$view = new $class();

		// Render the view
		try
		{
			$view->render();
		}
		catch (XTemplate\Exceptions\RenderException $objException)
		{
			die ("The view could not be rendered: ". $objException->getMessage());
		}

		return $view;
	}

}
