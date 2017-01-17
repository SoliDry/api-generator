<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
//        require_once __DIR__ . '/../../vendor/laravel/framework/src/Illuminate/Foundation/helpers.php';
        $app = require __DIR__ . '/../../bootstrap/app.php';
//        config(['app.timezone' => 'Europe/Moscow']);
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }
}