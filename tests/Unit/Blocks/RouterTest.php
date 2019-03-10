<?php

namespace SoliDryTest\Unit\Blocks;

use SoliDry\Blocks\ContentManager;
use SoliDry\Blocks\Routes;
use SoliDry\Blocks\RoutesTrait;
use SoliDry\ApiGenerator;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ApiInterface;
use SoliDryTest\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigTest
 * @package rjapitest\Unit\Blocks
 * @property Routes router
 */
class RouterTest extends TestCase
{
    /** @var ApiGenerator $gen */
    private $gen;
    private $router;

    public function setUp(): void
    {
        parent::setUp();
        $this->gen                 = new ApiGenerator();
        $this->gen->modulesDir     = DirsInterface::MODULES_DIR;
        $this->gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->gen->httpDir        = DirsInterface::HTTP_DIR;
        $this->gen->version        = self::MODULE_NAME;
        $data                      = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/oas/openapi.yaml'));
        $this->gen->types          = $data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
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