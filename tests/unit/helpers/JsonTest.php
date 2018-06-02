<?php

namespace rjapitest\unit\helpers;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Modules\V2\Entities\Article;
use Modules\V2\Http\Middleware\ArticleMiddleware;
use PHPUnit_Framework_MockObject_MockObject;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Json;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;

/**
 * Class JsonTest
 * @package rjapitest\unit\helpers
 */
class JsonTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject | BaseFormRequest baseFormRequest */
    private $baseFormRequest = null;
    /** @var PHPUnit_Framework_MockObject_MockObject | BaseModel baseFormRequest */
    private $baseModel = null;

    public function setUp()
    {
        parent::setUp();
        $this->baseModel       = new Article();
        $this->baseFormRequest = new ArticleMiddleware();
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
            RamlInterface::RAML_DATA => [
                RamlInterface::RAML_ATTRS => [
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
            RamlInterface::RAML_DATA => [
                RamlInterface::RAML_ATTRS => [
                    'title'       => 'Foo Bar Baz',
                    'description' => 'Foo Bar Baz Foo Bar Baz Foo Bar Baz',
                ]
            ]
        ]);
        $this->assertArraySubset([
            RamlInterface::RAML_ATTRS => [
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
            RamlInterface::RAML_DATA => [
                RamlInterface::RAML_RELATIONSHIPS => [
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

//    /**
//     * @test
//     */
//    public function it_gets_relations()
//    {
//        $collection = new Collection();
////        $items = new class extends \ArrayAccessible {
////
////        };
////        $items->offsetSet(0, 'title')
//        $collection->add([
//            'title' => 'Foo Bar Baz'
//        ]);
//        $rels = Json::getRelations($collection, 'article');
//        print_r($rels);
//    }
}
