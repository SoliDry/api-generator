<?php

namespace SoliDry;


use Illuminate\Support\ServiceProvider;
use SoliDry\Providers\ConsoleServiceProvider;

class LaravelApiServiceProvider extends ServiceProvider
{

    /**
     *  Registers api:generate console command for Laravel
     */
    public function register(): void
    {
        $this->app->register(ConsoleServiceProvider::class);
    }
}