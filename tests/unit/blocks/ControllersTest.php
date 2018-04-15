<?php

namespace rjapitest\unit\blocks;

use rjapi\blocks\Controllers;
use rjapi\RJApiGenerator;
use rjapi\types\ControllersInterface;
use rjapitest\unit\TestCase;

class ControllersTest extends TestCase
{
    /** @var Controllers $controller */
    private $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new Controllers(new RJApiGenerator());
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