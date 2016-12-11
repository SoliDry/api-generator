<?php
namespace rjapi;

use Illuminate\Support\ServiceProvider;
use rjapi\controllers\LaravelTypesController;
use yii\console\Application;

class LaravelServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        self::boot();
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
            LaravelTypesController::class
        ]);
    }
}