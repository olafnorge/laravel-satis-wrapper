<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use URL;

class AppServiceProvider extends ServiceProvider {


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // set http schema (force f.e. https)
        URL::forceScheme(config('app.force_scheme'));
    }
}
