<?php

namespace FatturaPa\Ui;

use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {    	
        include __DIR__.'/routes/web.php';        
        $this->app->make('FatturaPa\Ui\IndexController');		
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'ui');
		$this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/ui')], 'views');
		
		$this->publishes([base_path('vendor/fatturapa/ui') => public_path('vendor/ui')], 'ui');
        $this->publishes([__DIR__.'/public' => public_path('vendor/ui')], 'public');
    }
}
