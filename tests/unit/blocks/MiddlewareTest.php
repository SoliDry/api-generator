<?php

namespace rjapitest\unit\blocks;

use PHPUnit_Framework_MockObject_MockObject;
use rjapi\blocks\FormRequestModel;
use rjapi\blocks\FormRequest;
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
 * @property FormRequest middleware
 */
class MiddlewareTest extends TestCase
{
    private $middleware;

    /**
     * @throws \ReflectionException
     */
    public function setUp()
    {
        parent::setUp();
        /** @var RJApiGenerator|PHPUnit_Framework_MockObject_MockObject $gen */
        $gen = $this->createMock(RJApiGenerator::class);
        $gen->method('options')->willReturn([
            ConsoleInterface::OPTION_REGENERATE => 1,
            ConsoleInterface::OPTION_MIGRATIONS => 1,
        ]);
        $gen                 = new RJApiGenerator();
        $gen->objectName     = 'Article';
        $gen->version        = self::MODULE_NAME;
        $gen->modulesDir     = DirsInterface::MODULES_DIR;
        $gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $gen->httpDir        = DirsInterface::HTTP_DIR;
        $gen->formRequestDir = DirsInterface::FORM_REQUEST_DIR;
        $ramlData            = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/raml/articles.raml'));
        $gen->types          = $ramlData[RamlInterface::RAML_KEY_TYPES];
        $gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->middleware = new FormRequest($gen);
    }

    /**
     * @test
     */
    public function it_creates_middleware_entity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->middleware);
        $this->middleware->createEntity(self::DIR_OUTPUT);
        $this->middleware->recreateEntity(self::DIR_OUTPUT);
        $this->middleware->createAccessToken();
    }
}