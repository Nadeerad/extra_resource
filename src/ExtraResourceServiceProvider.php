<?php

namespace Nadeera\ExtraResource;

use Illuminate\Support\ServiceProvider;
use Commands\generateExtraResource;
/**
 *
 */
class ExtraResourceServiceProvider extends ServiceProvider
{

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');

		if ($this->app->runningInConsole()) {
	        $this->commands([
	            Commands\generateExtraResource::class
	        ]);
	    }
	}


	public function register()
	{

	}
}
