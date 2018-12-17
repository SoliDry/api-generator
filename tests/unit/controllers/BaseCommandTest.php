<?php
namespace rjapitest\unit\controllers;

use rjapi\controllers\BaseCommand;
use rjapitest\unit\TestCase;

/**
 * Class BaseCommandTest
 * @package rjapitest\unit\controllers
 *
 * @property BaseCommand baseCommand
 */
class BaseCommandTest extends TestCase
{
    private $baseCommand;

    public function setUp()
    {
        parent::setUp();
        $this->baseCommand = new BaseCommand();
    }

    /**
     * @test
     * @throws \rjapi\exceptions\SchemaException
     */
    public function it_creates_sources_from_raml()
    {
        $this->baseCommand->actionIndex(__DIR__ . '/../../functional/oas/openapi.yaml');
        $this->assertInstanceOf(BaseCommand::class, $this->baseCommand);
    }
}