<?php

namespace Fatturapa\Libsdi;

use Illuminate\Support\ServiceProvider;

class LibsdiServiceProvider extends ServiceProvider
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
        $this->app->make('Fatturapa\Libsdi\LibsdiController');
        $this->app->make('Fatturapa\Libsdi\InvoicesController');
        $this->app->make('Fatturapa\Libsdi\BaseController');
    }
}
