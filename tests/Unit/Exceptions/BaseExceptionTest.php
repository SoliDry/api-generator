<?php
namespace SoliDryTest\Unit\Exceptions;

use Illuminate\Http\JsonResponse;
use SoliDry\Exceptions\BaseException;
use SoliDryTest\Unit\TestCase;

class BaseExceptionTest extends TestCase
{
    /**
     * @test
     * @throws BaseException
     * @expectedException \SoliDry\Exceptions\BaseException
     */
    public function it_runs_base_exception()
    {
        $baseException = new BaseException('Foo Bar');
        $baseException->__toString();
        throw new BaseException('Foo Bar');
    }

    /**
     * @test
     */
    public function it_returns_render_response()
    {
        $baseException = new BaseException('Foo Bar');
        $resp = $baseException->render($this->request());
        $this->assertInstanceOf(JsonResponse::class, $resp);
    }
}