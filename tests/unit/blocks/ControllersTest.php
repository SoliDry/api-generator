<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\Controllers;
use rjapi\RJApiGenerator;
use rjapi\types\ControllersInterface;
use rjapitest\unit\TestCase;

class ControllersTest extends TestCase
{
    private $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new Controllers(new RJApiGenerator());
    }

    public function testCreateWithDefault()
    {
        $this->assertInstanceOf(ControllersInterface::class, $this->controller);
        $this->controller->createDefault();
    }
}