<?php

namespace rjapitest\unit\blocks;

use rjapi\blocks\ContentManager;
use rjapi\blocks\Routes;
use rjapi\blocks\RoutesTrait;
use rjapi\RJApiGenerator;
use rjapi\types\DirsInterface;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigTest
 * @package rjapitest\unit\blocks
 * @property Routes router
 */
class RouterTest extends TestCase
{
    /** @var RJApiGenerator $gen */
    private $gen;
    private $router;

    public function setUp()
    {
        parent::setUp();
        $this->gen                 = new RJApiGenerator();
        $this->gen->modulesDir     = DirsInterface::MODULES_DIR;
        $this->gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->gen->httpDir        = DirsInterface::HTTP_DIR;
        $this->gen->version        = self::MODULE_NAME;
        $ramlData                  = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/raml/articles.raml'));
        $this->gen->types          = $ramlData[RamlInterface::RAML_KEY_TYPES];
        $this->gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->router              = new Routes($this->gen);
        $this->router->setCodeState($this->gen);
    }

    /**
     * @test
     */
    public function it_creates_routes()
    {
        $this->router->create();
        $this->assertArraySubset([
            ContentManager::class => ContentManager::class,
            RoutesTrait::class    => RoutesTrait::class,
        ], class_uses($this->router));
    }
}