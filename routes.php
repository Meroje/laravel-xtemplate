<?php

Route::get('(:bundle)', function()
{
	return View::make('xtemplate::test');
});