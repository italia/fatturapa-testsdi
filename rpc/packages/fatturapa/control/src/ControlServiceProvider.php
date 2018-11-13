<?php

namespace FatturaPa\Control;

use Illuminate\Support\ServiceProvider;

class ControlServiceProvider extends ServiceProvider
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
        $this->app->make('FatturaPa\Control\NotificationsController');
        $this->app->make('FatturaPa\Control\InvoicesController');
        $this->app->make('FatturaPa\Control\BaseController');
        $this->app->make('FatturaPa\Control\ChannelsController');
        $this->app->make('FatturaPa\Control\ActorsController');
    }
}
