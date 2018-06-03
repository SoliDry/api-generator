<?php
/**
 * Created by PhpStorm.
 * User: arthurkushman
 * Date: 03.06.18
 * Time: 14:48
 */

namespace rjapitest\unit\helpers;


use rjapi\helpers\MethodOptions;
use rjapi\types\MiddlewareInterface;
use rjapi\types\PhpInterface;
use rjapitest\unit\TestCase;

/**
 * Class MethodOptionsTest
 * @package rjapitest\unit\helpers
 *
 * @property MethodOptions methodOptions
 */
class MethodOptionsTest extends TestCase
{
    private $methodOptions;

    public function setUp()
    {
        parent::setUp();
        $this->methodOptions = new MethodOptions();
    }

    /**
     * @test
     */
    public function it_sets_and_gets_options()
    {
        $this->methodOptions->setModifier(PhpInterface::PHP_MODIFIER_PRIVATE);
        $this->assertEquals(PhpInterface::PHP_MODIFIER_PRIVATE, $this->methodOptions->getModifier());

        $this->methodOptions->setName('foo');
        $this->assertEquals('foo', $this->methodOptions->getName());

        $this->methodOptions->setReturnType('bool');
        $this->assertEquals('bool', $this->methodOptions->getReturnType());

        $this->methodOptions->setIsStatic(true);
        $this->assertTrue($this->methodOptions->isStatic());

        $this->methodOptions->setParams([
            MiddlewareInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => MiddlewareInterface::METHOD_PARAM_NEXT,
        ]);
        $this->assertArraySubset([
            MiddlewareInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => MiddlewareInterface::METHOD_PARAM_NEXT,
        ], $this->methodOptions->getParams());
    }
}