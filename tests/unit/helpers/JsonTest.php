<?php

namespace rjapitest\unit\helpers;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Modules\V1\Entities\Article;
use Modules\V1\Http\Middleware\ArticleMiddleware;
use PHPUnit_Framework_MockObject_MockObject;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;
use rjapi\helpers\Json;
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
        $this->baseModel = new Article();
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
        $resource = Json::getResource($this->baseFormRequest, $this->baseModel, Article::class, false);
        $this->assertInstanceOf(Item::class, $resource);
    }
}
