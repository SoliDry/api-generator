<?php

namespace rjapitest\unit\blocks;

use Modules\V2\Entities\Article;
use Modules\V2\Http\Middleware\ArticleTestMiddleware;
use rjapi\blocks\FormRequestModel;
use rjapi\blocks\FormRequest;
use rjapi\ApiGenerator;
use rjapi\types\DirsInterface;
use rjapi\types\ApiInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Entities;
use Symfony\Component\Yaml\Yaml;

/**
 * Class EntitiesTest
 * @package rjapitest\unit\blocks
 *
 * @property Entities entities
 * @property FormRequest middleware
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
        /** @var ApiGenerator $gen */
        $gen = $this->createMock(ApiGenerator::class);
        $gen->method('formatEntitiesPath')->willReturn(self::DIR_OUTPUT);
        $gen->objectName = 'Article';
        $gen->version    = 'v2';
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->httpDir = DirsInterface::HTTP_DIR;
        // it is crucial to create entities/middleware in other namespace, not breaking original
        $gen->entitiesDir    = 'TestEntities';
        $gen->formRequestDir = 'TestMiddleware';
        $ramlData            = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/raml/articles.raml'));
        $gen->types          = $ramlData[ApiInterface::RAML_KEY_TYPES];
        $gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->entities      = new Entities($gen);
        $this->middleware   = new FormRequest($gen);
    }

    /**
     * @test
     */
    public function it_creates_middleware_and_entity()
    {
        // create Middleware for further entities to run
        $this->middleware->createEntity(self::DIR_OUTPUT, 'Middleware');
        require_once __DIR__ . '/../../_output/ArticleMiddleware.php';
        $articleMiddleware = new \Modules\V2\Http\Middleware\ArticleMiddleware();
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
        $this->assertTrue(file_exists(self::DIR_OUTPUT . 'Article.php'));
        // check props
        require_once __DIR__ . '/../../_output/Article.php';
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