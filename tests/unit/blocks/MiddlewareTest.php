<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\FormRequestModel;
use rjapi\blocks\Middleware;
use rjapi\RJApiGenerator;
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
        $gen = new RJApiGenerator();
        $gen->actionIndex('./raml/articles.raml');
        $gen->objectName = 'Article';
        $gen->version = 'V1';
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->middlewareDir = DirsInterface::MIDDLEWARE_DIR;
        $this->middleware = new Middleware($gen);
    }

    public function testCreateEntity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->middleware);
        $this->middleware->create();
        $this->middleware->createAccessToken();
    }
}