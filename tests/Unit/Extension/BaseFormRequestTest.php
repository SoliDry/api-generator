<?php

namespace SoliDryTest\Unit\Extensions;


use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Constraint\IsType;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\CustomSql;
use SoliDryTest\Unit\TestCase;

/**
 * Class CustomSqlTest
 * @package rjapitest\Unit\Extensions
 *
 * @property BaseFormRequest baseFormRequest
 */
class BaseFormRequestTest extends TestCase
{
    private $baseFormRequest;

    public function setUp()
    {
        parent::setUp();
        $this->baseFormRequest = new BaseFormRequest();
    }

    /**
     * @test
     */
    public function it_handles_request()
    {
        $this->assertInstanceOf(Request::class, $this->baseFormRequest->handle(new Request(), function($request) {
            return $request;
        }));
    }

    /**
     * @test
     */
    public function it_gets_query()
    {
        $this->assertInternalType(IsType::TYPE_ARRAY, $this->baseFormRequest->rules());
    }

    /**
     * @test
     */
    public function it_get_bindings()
    {
        $this->assertInternalType(IsTYpe::TYPE_ARRAY, $this->baseFormRequest->relations());
    }
}