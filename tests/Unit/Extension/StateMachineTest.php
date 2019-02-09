<?php

namespace SoliDryTest\Unit\Extensions;

use SoliDry\Exceptions\AttributesException;
use SoliDry\Extension\FsmTrait;
use SoliDry\Extension\StateMachine;
use SoliDryTest\_data\ArticleFixture;
use SoliDryTest\Unit\TestCase;

/**
 * Class StateMachineTest
 * @package rjapitest\Unit\Extensions
 *
 * @property StateMachine stateMachine
 */
class StateMachineTest extends TestCase
{
    use FsmTrait;

    private $stateMachine;
    private $entity;

    public function setUp()
    {
        parent::setUp();
        $this->entity       = 'article';
        $this->stateMachine = new StateMachine('article');
        try {
            $this->stateMachine->setStates('status');
        } catch (AttributesException $e) {
        }
    }

    /**
     * @test
     */
    public function it_is_stated()
    {
        $this->assertTrue($this->stateMachine->isStatedField('status'));
    }

    /**
     * @test
     * @expectedException \SoliDry\Exceptions\AttributesException
     */
    public function it_is_transitive()
    {
        $this->assertTrue($this->stateMachine->isTransitive('draft', 'published'));
        $this->stateMachine->setStates('foo');
    }

    /**
     * @test
     * @expectedException \SoliDry\Exceptions\AttributesException
     */
    public function it_sets_initial_and_gets_field()
    {
        $this->stateMachine->setInitial('status');
        $this->assertEquals($this->stateMachine->getInitial(), 'draft');
        $this->assertTrue($this->stateMachine->isInitial('draft'));
        $this->stateMachine->setInitial('foo');
    }

    /**
     * @test
     */
    public function it_gets_field()
    {
        $this->assertEquals('status', $this->stateMachine->getField());
    }

    /**
     * @test
     * @throws AttributesException
     */
    public function it_checks_fsm_create()
    {
        $jsonProps = [];
        $this->checkFsmCreate($jsonProps);
        $this->assertArraySubset(['status' => 'draft'], $jsonProps);
        // @todo: refactor die
//        $jsonProps = [
//            'status' => 'published'
//        ];
//        $this->checkFsmCreate($jsonProps);
    }

    /**
     * @test
     * @throws AttributesException
     */
    public function it_checks_fsm_update()
    {
        $model     = ArticleFixture::createAndGet();
        $jsonProps = [
            'status' => 'published'
        ];
        $this->checkFsmUpdate($jsonProps, $model);
        // @todo: refactor die
        // simulate err - non-transitive state
//        $jsonProps = [
//            'status' => 'archived'
//        ];
//        $this->checkFsmUpdate($jsonProps, $model);
        $this->stateMachine->setInitial('status');
        $this->assertEquals($model->status, $this->stateMachine->getInitial());
        ArticleFixture::delete($model->id);
    }
}