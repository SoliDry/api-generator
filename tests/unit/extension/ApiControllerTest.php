<?php

namespace rjapitest\unit\extension;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit_Framework_MockObject_MockObject;
use rjapi\exception\AttributesException;
use rjapi\extension\ApiController;
use rjapitest\unit\TestCase;

class ApiControllerTest extends TestCase
{
    private $apiController;
    /** @var \Illuminate\Routing\Route|PHPUnit_Framework_MockObject_MockObject $route */
    private $route;

    public function setUp()
    {
        parent::setUp();
        $this->route = $this->createMock(Route::class);
    }

    /**
     * @test
     */
    public function it_calls_index_without_tree_bit_mask_and_custom_sql()
    {
        $this->route->method('getActionName')->willReturn('ArticleController@index');
        $this->apiController = new ApiController($this->route);
        /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);
        try {
            $this->apiController->index($request);
        } catch (AttributesException $e) {
        }
    }
}