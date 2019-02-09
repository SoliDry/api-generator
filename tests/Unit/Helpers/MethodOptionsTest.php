<?php

namespace SoliDryTest\Unit\Helpers;

use SoliDry\Helpers\MethodOptions;
use SoliDry\Types\FromRequestInterface;
use SoliDry\Types\PhpInterface;
use SoliDryTest\Unit\TestCase;

/**
 * Class MethodOptionsTest
 * @package rjapitest\Unit\Helpers
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
            FromRequestInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => FromRequestInterface::METHOD_PARAM_NEXT,
        ]);
        $this->assertArraySubset([
            FromRequestInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => FromRequestInterface::METHOD_PARAM_NEXT,
        ], $this->methodOptions->getParams());
    }
}