<?php
namespace rjapitest\unit\exceptions;

use rjapi\exceptions\BaseException;
use rjapitest\unit\TestCase;

class BaseExceptionTest extends TestCase
{
    /**
     * @test
     * @throws BaseException
     * @expectedException \rjapi\exceptions\BaseException
     */
    public function it_runs_base_exception()
    {
        $baseException = new BaseException('Foo Bar');
        $baseException->__toString();
        throw new BaseException('Foo Bar');
    }
}