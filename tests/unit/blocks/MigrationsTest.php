<?php

namespace rjapitest\unit\blocks;

use PHPUnit_Framework_MockObject_MockObject;
use rjapi\blocks\MigrationsAbstract;
use rjapi\RJApiGenerator;
use rjapi\types\ConsoleInterface;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Migrations;

/**
 * Class MigrationsTest
 * @package rjapitest\unit\blocks
 * @property Migrations migrations
 */
class MigrationsTest extends TestCase
{
    private $migrations;

    public function setUp()
    {
        parent::setUp();
        /** @var RJApiGenerator|PHPUnit_Framework_MockObject_MockObject $gen */
        $gen = $this->createMock(RJApiGenerator::class);
        $gen->method('options')->willReturn([
            ConsoleInterface::OPTION_REGENERATE => 1,
            ConsoleInterface::OPTION_MIGRATIONS => 1,
        ]);
        $gen->types = [
            'ID' => [
                'type' => 'integer',
                'required' => 'true',
                'maximum' => 20,
            ],
            'ArticleAttributes' => [
                'description' => 'Article attributes description',
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'required' => true,
                        'type' => 'string',
                        'minLength' => 16,
                        'maxLength' => 256,
                        'facets' => [
                            'index' => [
                                'idx_title' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $gen->objectProps = [
            'type' => 'Type',
            'id' => 'ID',
            'attributes' => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $gen->objectName = 'Article';
        $gen->version = 'v2';
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->middlewareDir = DirsInterface::MIDDLEWARE_DIR;
        $gen->migrationsDir = DirsInterface::MIGRATIONS_DIR;
        $this->migrations = new Migrations($gen);
    }

    /**
     * @test
     */
    public function it_creates_entity()
    {
        $this->assertInstanceOf(MigrationsAbstract::class, $this->migrations);
        $this->migrations->create();
        $this->migrations->createPivot();
    }
}