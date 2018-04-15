<?php

namespace rjapitest\unit\blocks;

use PHPUnit_Framework_MockObject_MockObject;
use rjapi\blocks\FormRequestModel;
use rjapi\blocks\Middleware;
use rjapi\RJApiGenerator;
use rjapi\types\ConsoleInterface;
use rjapi\types\DirsInterface;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class EntitiesTest
 * @package rjapitest\unit\blocks
 *
 * @property Middleware middleware
 */
class MiddlewareTest extends TestCase
{
    private $middleware;

    public function setUp()
    {
        parent::setUp();
        /** @var RJApiGenerator|PHPUnit_Framework_MockObject_MockObject $gen */
        $gen = $this->createMock(RJApiGenerator::class);
        $gen->method('options')->willReturn([
            ConsoleInterface::OPTION_REGENERATE => 1,
            ConsoleInterface::OPTION_MIGRATIONS => 1,
        ]);
        $this->middleware = new Middleware($gen);
    }

    /**
     * @test
     */
    public function it_creates_middleware_entity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->middleware);
        $this->middleware->createAccessToken();
    }
}