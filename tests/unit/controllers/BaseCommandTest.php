<?php
namespace rjapitest\unit\controllers;

use Illuminate\Console\Command;
use rjapi\controllers\BaseCommand;
use rjapi\types\ConsoleInterface;
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
     * @throws \rjapi\exceptions\DirectoryException
     */
    public function it_creates_sources_from_raml()
    {
        $this->baseCommand->actionIndex(__DIR__ . '/../../functional/raml/articles.raml');
        $this->assertInstanceOf(BaseCommand::class, $this->baseCommand);
    }
}