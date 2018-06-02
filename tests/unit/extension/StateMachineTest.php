<?php

namespace rjapitest\unit\extensions;

use rjapi\exceptions\AttributesException;
use rjapi\extension\StateMachine;
use rjapitest\unit\TestCase;

/**
 * Class StateMachineTest
 * @package rjapitest\unit\extensions
 *
 * @property StateMachine stateMachine
 */
class StateMachineTest extends TestCase
{
    private $stateMachine;

    public function setUp()
    {
        parent::setUp();
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
     * @expectedException \rjapi\exceptions\AttributesException
     */
    public function it_is_transitive()
    {
        $this->assertTrue($this->stateMachine->isTransitive('draft', 'published'));
        $this->stateMachine->setStates('foo');
    }

    /**
     * @test
     * @expectedException \rjapi\exceptions\AttributesException
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
}