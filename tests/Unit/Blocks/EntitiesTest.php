<?php

namespace SoliDryTest\Unit\Blocks;

use Modules\V2\Entities\Article;
use Modules\V2\Http\Requests\ArticleFormRequest;
use SoliDry\Blocks\FormRequestModel;
use SoliDry\Blocks\FormRequest;
use SoliDry\ApiGenerator;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ApiInterface;
use SoliDryTest\Unit\TestCase;
use SoliDry\Blocks\Entities;
use Symfony\Component\Yaml\Yaml;

/**
 * Class EntitiesTest
 * @package rjapitest\Unit\Blocks
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
    public function setUp(): void
    {
        parent::setUp();
        /** @var ApiGenerator $gen */
        $gen = $this->createMock(ApiGenerator::class);
        $gen->method('formatEntitiesPath')->willReturn(self::DIR_OUTPUT);
        $gen->objectName = 'Article';
        $gen->version    = 'v2';
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->httpDir    = DirsInterface::HTTP_DIR;
        // it is crucial to create entities/middleware in other namespace, not breaking original
        $gen->entitiesDir    = 'TestEntities';
        $gen->formRequestDir = 'TestMiddleware';
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
        $this->entities      = new Entities($gen);
        $this->middleware    = new FormRequest($gen);
    }

    /**
     * @test
     */
    public function it_creates_middleware_and_entity()
    {
        // create Middleware for further entities to run
        $this->middleware->createEntity(self::DIR_OUTPUT, 'FormRequest');
        require_once __DIR__ . '/../../_output/ArticleFormRequest.php';
        $articleMiddleware = new \Modules\V2\Http\Requests\ArticleFormRequest();
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