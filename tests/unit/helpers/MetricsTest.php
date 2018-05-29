<?php

namespace rjapitest\unit\helpers;

use PHPUnit\Framework\Constraint\IsType;
use rjapi\helpers\Metrics;
use rjapitest\unit\TestCase;

class MetricsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_creates_millitime()
    {
        $millis = Metrics::millitime();
        $this->assertInternalType(IsType::TYPE_STRING, $millis);
        $this->assertTrue(time() < $millis);
    }
}