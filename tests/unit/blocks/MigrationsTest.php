<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\FormRequestModel;
use rjapi\blocks\MigrationsAbstract;
use rjapi\RJApiGenerator;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Migrations;

/**
 * Class MigrationsTest
 * @package rjapitest\unit\blocks
 * @property Migrations migrations
 */
class MigrationsTest extends TestCase
{
    private $migrations;

    public function setUp()
    {
        parent::setUp();
        $gen = new RJApiGenerator();
        $gen->actionIndex('./raml/articles.raml');
        $gen->objectName = 'Article';
        $gen->version = 'v1';
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->middlewareDir = DirsInterface::MIDDLEWARE_DIR;
        $this->migrations = new Migrations($gen);
    }

    public function testCreateEntity()
    {
        $this->assertInstanceOf(MigrationsAbstract::class, $this->migrations);
        $this->migrations->create();
        $this->migrations->createPivot();
    }
}