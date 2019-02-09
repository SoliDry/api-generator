<?php
namespace SoliDryTest\Unit\Exceptions;

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
}