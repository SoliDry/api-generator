<?php
/**
 * Created by PhpStorm.
 * User: arthurkushman
 * Date: 28.05.18
 * Time: 21:34
 */

namespace rjapitest\unit\helpers;

use rjapi\helpers\SqlOptions;
use rjapi\types\ModelsInterface;
use rjapitest\unit\TestCase;

/**
 * Class SqlOptionsTest
 * @package rjapitest\unit\helpers
 *
 * @property SqlOptions sqlOptions
 */
class SqlOptionsTest extends TestCase
{
    private $sqlOptions;

    public function setUp()
    {
        parent::setUp();
        $this->sqlOptions = new SqlOptions();
    }

    /**
     * @test
     */
    public function it_inits_with_default_props()
    {
        $this->assertSame($this->sqlOptions->getId(), 0);
        $this->assertSame($this->sqlOptions->getLimit(), ModelsInterface::DEFAULT_LIMIT);
        $this->assertSame($this->sqlOptions->getPage(), ModelsInterface::DEFAULT_PAGE);
        $this->assertArraySubset([], $this->sqlOptions->getOrderBy());
        $this->assertArraySubset(ModelsInterface::DEFAULT_DATA, $this->sqlOptions->getData());
        $this->assertArraySubset([], $this->sqlOptions->getFilter());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_id()
    {
        $this->sqlOptions->setId(123);
        $this->assertSame(123, $this->sqlOptions->getId());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_page()
    {
        $this->sqlOptions->setPage(12);
        $this->assertSame(12, $this->sqlOptions->getPage());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_limit()
    {
        $this->sqlOptions->setLimit(21);
        $this->assertSame(21, $this->sqlOptions->getLimit());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_data()
    {
        $this->sqlOptions->setData([
            'title', 'description'
        ]);
        $this->assertArraySubset([
            'title', 'description'
        ], $this->sqlOptions->getData());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_order()
    {
        $this->sqlOptions->setOrderBy(['title' => 'desc']);
        $this->assertArraySubset(['title' => 'desc'], $this->sqlOptions->getOrderBy());
    }

    /**
     * @test
     */
    public function it_sets_and_gets_filter()
    {
        $this->sqlOptions->setFilter(['title' => 'Foo Bar']);
        $this->assertArraySubset(['title' => 'Foo Bar'], $this->sqlOptions->getFilter());
    }
}
