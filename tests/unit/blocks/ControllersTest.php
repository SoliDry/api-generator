<?php

namespace rjapitest\unit\blocks;

use Illuminate\Routing\Route;
use Modules\V2\Http\Controllers\ArticleController;
use rjapi\blocks\Controllers;
use rjapi\extension\ApiController;
use rjapi\RJApiGenerator;
use rjapi\types\ControllersInterface;
use rjapi\types\DirsInterface;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;
use Symfony\Component\Yaml\Yaml;

class ControllersTest extends TestCase
{
    /** @var Controllers $controller */
    private $controller;

    public function setUp()
    {
        parent::setUp();
        $gen                 = new RJApiGenerator();
        $gen->objectName     = 'Article';
        $gen->version        = self::MODULE_NAME;
        $gen->modulesDir     = DirsInterface::MODULES_DIR;
        $gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $gen->httpDir        = DirsInterface::HTTP_DIR;
        $ramlData            = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/raml/articles.raml'));
        $gen->types          = $ramlData[RamlInterface::RAML_KEY_TYPES];
        $this->controller    = new Controllers($gen);
    }

    /**
     * @test
     */
    public function it_creates_entity()
    {
        $this->controller->createEntity('./tests/_output', 'ControllerTest');
        $this->assertTrue(file_exists(self::DIR_OUTPUT . 'ArticleControllerTest.php'));
        require_once __DIR__ . '/../../_output/ArticleControllerTest.php';
        $articleController = new ArticleController(new Route(['POST', 'GET'], '', function () {
        }));
        $this->assertInstanceOf(ApiController::class, $articleController);
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