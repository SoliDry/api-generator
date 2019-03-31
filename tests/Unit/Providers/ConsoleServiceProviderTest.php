<?php

namespace SoliDryTest\Unit\Providers;


use SoliDry\ApiGenerator;
use SoliDry\Providers\ConsoleServiceProvider;
use SoliDryTest\Unit\TestCase;

/**
 * Class ConsoleServiceProviderTest
 *
 * @package SoliDryTest\Unit\Providers
 *
 * @property ConsoleServiceProvider $serviceProvider
 */
class ConsoleServiceProviderTest extends TestCase
{
    private $serviceProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->serviceProvider = new ConsoleServiceProvider(app());
    }

    /**
     * @test
     */
    public function it_register_and_provides()
    {
        $this->serviceProvider->register();
        $this->assertArraySubset([
            ApiGenerator::class,
        ], $this->serviceProvider->provides());
    }
}