<?php

namespace rjapitest\unit\extensions;

use rjapi\extension\BitMask;
use rjapitest\unit\TestCase;

/**
 * Class BitMaskTest
 * @package rjapitest\unit\extensions
 *
 * @property BitMask bitMask
 */
class BitMaskTest extends TestCase
{
    private const FIELD = 'permissions';
    private $bitMask;

    public function setUp()
    {
        parent::setUp();
        $this->bitMask = new BitMask([
            self::FIELD => [
                'enabled' => true,
                'flags'   => [
                    'publisher'      => 1,
                    'editor'         => 2,
                    'manager'        => 4,
                    'photo_reporter' => 8,
                    'admin'          => 16,
                ],
            ],
        ]);
    }

    /**
     * @test
     * @throws \rjapi\exceptions\AttributesException
     * @expectedException \rjapi\exceptions\AttributesException
     */
    public function it_gets_flags_and_fields()
    {
        $this->assertArraySubset([
            'publisher'      => 1,
            'editor'         => 2,
            'manager'        => 4,
            'photo_reporter' => 8,
            'admin'          => 16,
        ], $this->bitMask->getFlags());
        $this->assertTrue($this->bitMask->isEnabled());
        $this->assertFalse($this->bitMask->isHidden());
        $this->assertEquals(self::FIELD, $this->bitMask->getField());
        // testing that it throws exception
        $this->bitMask = new BitMask([
            self::FIELD => [
                'enabled'        => true,
                'publisher'      => 1,
                'editor'         => 2,
                'manager'        => 4,
                'photo_reporter' => 8,
                'admin'          => 16,
            ],
        ]);
        $this->bitMask->getFlags();
    }
}