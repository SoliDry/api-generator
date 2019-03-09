<?php

namespace SoliDryTest\Unit\Extensions;


use PHPUnit\Framework\Constraint\IsType;
use SoliDry\Extension\CustomSql;
use SoliDryTest\Unit\TestCase;

/**
 * Class CustomSqlTest
 * @package rjapitest\Unit\Extensions
 *
 * @property CustomSql customSql
 */
class CustomSqlTest extends TestCase
{
    private $customSql;

    public function setUp(): void
    {
        parent::setUp();
        $this->createConfig();
        $this->customSql = new CustomSql('article');
    }

    /**
     * @test
     */
    public function it_is_enabled()
    {
        $this->assertTrue($this->customSql->isEnabled());
    }

    /**
     * @test
     */
    public function it_gets_query()
    {
        $this->assertInternalType(IsType::TYPE_STRING, $this->customSql->getQuery());
    }

    /**
     * @test
     */
    public function it_get_bindings()
    {
        $this->assertInternalType(IsTYpe::TYPE_ARRAY, $this->customSql->getBindings());
    }
}