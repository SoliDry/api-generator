<?php

namespace SoliDryTest\Unit\Blocks;

use PHPUnit_Framework_MockObject_MockObject;
use SoliDry\Blocks\FormRequestModel;
use SoliDry\Blocks\FormRequest;
use SoliDry\ApiGenerator;
use SoliDry\Types\ConsoleInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ApiInterface;
use SoliDryTest\Unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class EntitiesTest
 * @package rjapitest\Unit\Blocks
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
        /** @var ApiGenerator|PHPUnit_Framework_MockObject_MockObject $gen */
        $gen = $this->createMock(ApiGenerator::class);
        $gen->method('options')->willReturn([
            ConsoleInterface::OPTION_REGENERATE => 1,
            ConsoleInterface::OPTION_MIGRATIONS => 1,
        ]);
        $gen                 = new ApiGenerator();
        $gen->objectName     = 'Article';
        $gen->version        = self::MODULE_NAME;
        $gen->modulesDir     = DirsInterface::MODULES_DIR;
        $gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $gen->httpDir        = DirsInterface::HTTP_DIR;
        $gen->formRequestDir = DirsInterface::FORM_REQUEST_DIR;
        $data                = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/oas/openapi.yaml'));
        $gen->types          = $data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        $gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->middleware    = new FormRequest($gen);
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