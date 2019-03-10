<?php
namespace SoliDryTest\Unit\Controllers;

use SoliDry\Controllers\BaseCommand;
use SoliDryTest\Unit\TestCase;

/**
 * Class BaseCommandTest
 * @package rjapitest\Unit\Controllers
 *
 * @property BaseCommand baseCommand
 */
class BaseCommandTest extends TestCase
{
    private $baseCommand;

    public function setUp(): void
    {
        parent::setUp();
        $this->baseCommand = new BaseCommand();
    }

    /**
     * @test
     * @throws \SoliDry\Exceptions\SchemaException
     */
    public function it_creates_sources_from_raml()
    {
        $this->baseCommand->actionIndex(__DIR__ . '/../../functional/oas/openapi.yaml');
        $this->assertInstanceOf(BaseCommand::class, $this->baseCommand);
    }
}