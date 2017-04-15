<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\FormRequestModel;
use rjapi\RJApiGenerator;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Entities;

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
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->entitiesDir = DirsInterface::ENTITIES_DIR;
        $this->entities = new Entities($gen);
    }

    public function testCreateEntity()
    {
        $this->assertInstanceOf(FormRequestModel::class, $this->entities);
        $this->entities->createPivot();
        $this->entities->create();
    }
}