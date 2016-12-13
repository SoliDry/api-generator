<?php
namespace rjapi;

use Illuminate\Support\ServiceProvider;
use rjapi\controllers\LaravelRJApiGenerator;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->commands([
            LaravelRJApiGenerator::class
        ]);
    }
}