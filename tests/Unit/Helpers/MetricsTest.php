<?php

namespace SoliDryTest\Unit\Helpers;

use PHPUnit\Framework\Constraint\IsType;
use SoliDry\Helpers\Metrics;
use SoliDryTest\Unit\TestCase;

class MetricsTest extends TestCase
{
    public function setUp(): void
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