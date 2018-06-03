<?php

namespace rjapitest\unit\blocks;

use Modules\V2\Entities\Article;
use Modules\V2\Http\Middleware\ArticleTestMiddleware;
use rjapi\blocks\FormRequestModel;
use rjapi\blocks\Middleware;
use rjapi\RJApiGenerator;
use rjapi\types\DirsInterface;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Entities;
use Symfony\Component\Yaml\Yaml;

/**
 * Class EntitiesTest
 * @package rjapitest\unit\blocks
 *
 * @property Entities entities
 * @property Middleware middleware
 */
class EntitiesTest extends TestCase
{
    private $entities;
    private $middleware;

    /**
     * @throws \ReflectionException
     */
    public function setUp()
    {
        parent::setUp();
//        $gen                = new RJApiGenerator();
        $gen = $this->createMock(RJApiGenerator::class);
        $gen->method('formatEntitiesPath')->willReturn(self::DIR_OUTPUT);
        $gen->objectName    = 'ArticleTest';
        $gen->version       = 'v2';
        $gen->modulesDir    = DirsInterface::MODULES_DIR;
        $gen->entitiesDir   = DirsInterface::ENTITIES_DIR;
        $gen->httpDir       = DirsInterface::HTTP_DIR;
        $gen->middlewareDir = DirsInterface::MIDDLEWARE_DIR;
        $gen->types         = [
            'SID'               => [
                'type'      => 'string',
                'required'  => true,
                'maxLength' => 128,
            ],
            'ArticleAttributes' => [
                'description' => 'Article attributes description',
                'type'        => 'object',
                'properties'  => [
                    'title' => [
                        'required'  => true,
                        'type'      => 'string',
                        'minLength' => 16,
                        'maxLength' => 256,
                        'facets'    => [
                            'index' => [
                                'idx_title' => 'index'
                            ]
                        ]
                    ]
                ]
            ],
            'ArticleTest'       => [
                'type'       => 'object',
                'properties' => [
                    'type'          => 'Type',
                    'id'            => 'SID',
                    'attributes'    => 'ArticleAttributes',
                    'relationships' => [
                        'type' => 'TagRelationships[] | TopicRelationships',
                    ]
                ]
            ]
        ];
        $gen->objectProps   = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->entities     = new Entities($gen);
        $this->middleware   = new Middleware($gen);
    }

    /**
     * @test
     */
    public function it_creates_middleware_and_entity()
    {
        // create Middleware for further entities to run
        $this->middleware->createEntity(self::DIR_OUTPUT, 'Middleware');
        $this->assertTrue(file_exists(self::DIR_OUTPUT . 'ArticleTestMiddleware.php'));
        require_once __DIR__ . '/../../_output/ArticleTestMiddleware.php';
        $articleMiddleware = new ArticleTestMiddleware();
        $this->assertNull($articleMiddleware->id);
        $this->assertNull($articleMiddleware->title);
        $this->assertTrue($articleMiddleware->authorize());
        $this->assertArraySubset([
            'title' => 'required|string|min:16|max:256|',
        ], $articleMiddleware->rules());
        $this->assertArraySubset(
            [
                'tag',
                'topic',
            ], $articleMiddleware->relations());

        $this->entities->createEntity(self::DIR_OUTPUT);
        $this->entities->recreateEntity(self::DIR_OUTPUT);
        $this->assertTrue(file_exists(self::DIR_OUTPUT . 'ArticleTest.php'));
        // check props
        require_once __DIR__ . '/../../_output/ArticleTest.php';
        $article = new Article();
        $this->assertFalse($article->incrementing);
        $this->assertFalse($article->timestamps);
        $extArticle = new class extends Article
        {
            public function getPrimaryKey()
            {
                return $this->primaryKey;
            }

            public function getTable()
            {
                return $this->table;
            }
        };
        $this->assertEquals('id', $extArticle->getPrimaryKey());
        $this->assertEquals('article', $extArticle->getTable());
    }

    /**
     * @test
     */
    public function it_creates_pivot_entity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->entities);
        $this->entities->createPivot();
    }
}