<?php

namespace SoliDryTest\Unit\Helpers;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Modules\V2\Entities\Article;
use Modules\V2\Http\Requests\ArticleFormRequest;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit_Framework_MockObject_MockObject;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\BaseModel;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Json;
use SoliDry\Types\ApiInterface;
use SoliDryTest\_data\ArticleFixture;
use SoliDryTest\Unit\TestCase;

/**
 * Class JsonTest
 * @package rjapitest\Unit\Helpers
 */
class JsonTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject | BaseFormRequest baseFormRequest */
    private $baseFormRequest = null;
    /** @var PHPUnit_Framework_MockObject_MockObject | BaseModel baseFormRequest */
    private $baseModel = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->baseModel       = new Article();
        $this->baseFormRequest = new ArticleFormRequest();
    }

    /**
     * @test
     */
    public function it_gets_resource_collection()
    {
        $resource = Json::getResource($this->baseFormRequest, $this->baseModel, Article::class, true);
        $this->assertInstanceOf(Collection::class, $resource);
    }

    /**
     * @test
     */
    public function it_gets_resource_item()
    {
        $resource = Json::getResource($this->baseFormRequest, $this->baseModel, Article::class);
        $this->assertInstanceOf(Item::class, $resource);
    }

    /**
     * @test
     */
    public function it_gets_attributes()
    {
        $attrsData = Json::getAttributes([
            ApiInterface::RAML_DATA => [
                ApiInterface::RAML_ATTRS => [
                    'title'       => 'Foo Bar Baz',
                    'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
                ]
            ]
        ]);
        $this->assertArraySubset([
            'title'       => 'Foo Bar Baz',
            'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
        ], $attrsData);
    }

    /**
     * @test
     */
    public function it_gets_data()
    {
        $data = Json::getData([
            ApiInterface::RAML_DATA => [
                ApiInterface::RAML_ATTRS => [
                    'title'       => 'Foo Bar Baz',
                    'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
                ]
            ]
        ]);
        $this->assertArraySubset([
            ApiInterface::RAML_ATTRS => [
                'title'       => 'Foo Bar Baz',
                'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
            ]
        ], $data);
    }

    /**
     * @test
     */
    public function it_gets_relationships()
    {
        $relations = Json::getRelationships([
            ApiInterface::RAML_DATA => [
                ApiInterface::RAML_RELATIONSHIPS => [
                    'type' => 'TagRelationships[]'
                ]
            ]
        ]);
        $this->assertArraySubset([
            'type' => 'TagRelationships[]'
        ], $relations);
    }

    /**
     * @test
     */
    public function it_returns_json_error()
    {
        $encodedJson = Json::outputErrors(
            [
                [
                    JSONApiInterface::ERROR_TITLE  => 'JSON API support disabled',
                    JSONApiInterface::ERROR_DETAIL => 'JSON API method myMethod'
                        .
                        ' was called. You can`t call this method while JSON API support is disabled.',
                ],
            ], true
        );
        $this->assertArraySubset(['errors' => [
            [
                JSONApiInterface::ERROR_TITLE  => 'JSON API support disabled',
                JSONApiInterface::ERROR_DETAIL => 'JSON API method myMethod'
                    .
                    ' was called. You can`t call this method while JSON API support is disabled.',
            ]]],
            json_decode($encodedJson, true)
        );
    }

    /**
     * @test
     */
    public function it_encodes_decodes_json()
    {
        $this->assertEquals('{"title":"Foo Bar Baz"}', Json::encode([
            'title' => 'Foo Bar Baz'
        ]));
        $this->assertEquals([
            'title' => 'Foo Bar Baz'
        ], Json::decode('{"title":"Foo Bar Baz"}'));
    }

    /**
     * @test
     */
    public function it_gets_relations()
    {
        ArticleFixture::createAndGet();
        $articles = ArticleFixture::getCollection([['topic_id', '>=', 1]]);
        $rels = Json::getRelations($articles, 'article');
        $this->assertInternalType(IsType::TYPE_ARRAY, $rels);
        ArticleFixture::truncate();
    }
}
