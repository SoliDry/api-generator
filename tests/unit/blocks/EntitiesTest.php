<?php
namespace rjapitest\unit\blocks;


use rjapi\blocks\Entities;
use rjapi\RJApiGenerator;
use rjapitest\unit\TestCase;

/**
 * Class EntitiesTest
 * @package rjapitest\unit\blocks
 *
 * @property Entities entities
 */
class EntitiesTest extends TestCase
{
    private $entities;

    public function setUp()
    {
        parent::setUp();
        $gen = new RJApiGenerator();
        $gen->objectName = 'Article';
        $gen->version = 'v1';
        $this->entities = new Entities($gen);
    }

    public function testCreateEntity()
    {
        $this->entities->createPivot();
        $this->entities->create();
    }
}