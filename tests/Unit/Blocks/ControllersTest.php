<?php

namespace SoliDryTest\Unit\Blocks;

use SoliDry\Blocks\Controllers;
use SoliDry\ApiGenerator;
use SoliDry\Types\ControllersInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ApiInterface;
use SoliDryTest\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

class ControllersTest extends TestCase
{
    /** @var Controllers $controller */
    private $controller;

    public function setUp()
    {
        parent::setUp();
        $gen                 = new ApiGenerator();
        $gen->objectName     = 'Article';
        $gen->version        = self::MODULE_NAME;
        $gen->modulesDir     = DirsInterface::MODULES_DIR;
        $gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $gen->httpDir        = DirsInterface::HTTP_DIR;
        $data                = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/oas/openapi.yaml'));
        $gen->types          = $data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        $this->controller    = new Controllers($gen);
    }

    /**
     * @test
     */
    public function it_creates_default_controller()
    {
        $this->assertInstanceOf(ControllersInterface::class, $this->controller);
        $this->controller->createDefault();
    }
}