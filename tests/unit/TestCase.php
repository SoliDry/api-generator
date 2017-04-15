<?php
namespace rjapitest\unit;

use Illuminate\Foundation\Testing\TestCase as TestCaseLaravel;

abstract class TestCase extends TestCaseLaravel
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
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $app->make('config');
        return $app;
    }
}