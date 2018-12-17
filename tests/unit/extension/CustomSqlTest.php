<?php

namespace rjapitest\unit\extensions;


use PHPUnit\Framework\Constraint\IsType;
use rjapi\extension\CustomSql;
use rjapitest\unit\TestCase;

/**
 * Class CustomSqlTest
 * @package rjapitest\unit\extensions
 *
 * @property CustomSql customSql
 */
class CustomSqlTest extends TestCase
{
    private $customSql;

    public function setUp()
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