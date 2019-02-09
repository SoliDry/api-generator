<?php

namespace SoliDryTest\Unit\Extensions;

use Illuminate\Database\Eloquent\Collection;
use SoliDry\Extension\BitMask;
use SoliDry\Extension\BitMaskTrait;
use SoliDryTest\_data\UserFixture;
use SoliDryTest\Unit\TestCase;

/**
 * Class BitMaskTest
 * @package rjapitest\Unit\Extensions
 *
 * @property BitMask bitMask
 */
class BitMaskTraitTest extends TestCase
{
    use BitMaskTrait;

    private const FIELD = 'permissions';
    private $bitMask;
    private $users;
    private $user;
    private $model;

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
        $this->users[] = UserFixture::createAndGet();
        $this->user    = UserFixture::createAndGet();
        $this->model   = $this->user;
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_flags_index()
    {
        $maskData = $this->setFlagsIndex(new Collection($this->users));
        $this->assertInstanceOf(Collection::class, $maskData);
        $this->assertEquals(1, $maskData[0]['editor']);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_flags_view()
    {
        $maskData = $this->setFlagsView($this->user);
        $this->assertEquals(1, $maskData['editor']);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_flags_create()
    {
        $maskData = $this->setFlagsCreate();
        $this->assertEquals(1, $maskData['editor']);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_flags_update()
    {
        $this->setFlagsUpdate($this->model);
        $this->assertEquals(1, $this->model['editor']);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_mask_create()
    {
        $this->setMaskCreate([
            'publisher'      => 0,
            'editor'         => 0,
            'manager'        => 0,
            'photo_reporter' => 0,
            'admin'          => 1,
        ]);
        $this->assertEquals(16, $this->model->permissions);
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_mask_update()
    {
        $this->setMaskUpdate($this->model,
            [
                'publisher'      => 0,
                'editor'         => 0,
                'manager'        => 0,
                'photo_reporter' => 0,
                'admin'          => 1,
            ]);
        $this->assertEquals(16, $this->model->permissions);
    }

    public function tearDown()
    {
        UserFixture::delete($this->user->id);
        UserFixture::delete($this->users[0]->id);
        parent::tearDown();
    }
}