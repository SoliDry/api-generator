<?php

namespace SoliDry\Providers;


use Illuminate\Support\ServiceProvider;
use SoliDry\ApiGenerator;

class ConsoleServiceProvider extends ServiceProvider
{

    protected $commands = [
        ApiGenerator::class,
    ];

    /**
     * Register the commands.
     */
    public function register()
    {
        $this->commands($this->commands);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return $this->commands;
    }
}