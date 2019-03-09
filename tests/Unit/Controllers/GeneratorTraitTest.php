<?php

namespace SoliDryTest\Unit\Controllers;


use SoliDry\Controllers\GeneratorTrait;
use SoliDry\Types\ConsoleInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\PhpInterface;
use SoliDryTest\Unit\TestCase;

class GeneratorTraitTest extends TestCase
{
    use GeneratorTrait;

    public  $types          = [];
    public  $currentTypes   = [];
    public  $historyTypes   = [];
    public  $mergedTypes    = [];
    public  $diffTypes      = [];
    public  $frameWork      = '';
    public  $objectProps    = [];
    public  $generatedFiles = [];
    public  $relationships  = [];
    private $files          = [];

    private $options;

    public function setUp(): void
    {
        parent::setUp();
        $this->options = [ // merge last option
                             ConsoleInterface::OPTION_MERGE => ConsoleInterface::MERGE_DEFAULT_VALUE,
        ];
        $this->files   = [
            __DIR__ . '/../../functional/oas/openapi.yaml',
        ];
    }

    public function formatGenPathByDir(): string
    {
        return DirsInterface::GEN_DIR . PhpInterface::SLASH . $this->genDir . PhpInterface::SLASH;
    }

    /**
     * @test
     */
    public function it_sets_merge_types()
    {
        // merge last
        $this->setMergedTypes();
        $this->assertNotEmpty($this->types);
        // merge step
        $this->options = [ // merge last option
                           ConsoleInterface::OPTION_MERGE => 2
        ];
        $this->setMergedTypes();
        $this->assertNotEmpty($this->types);
        $this->options = [ // merge time option
                           ConsoleInterface::OPTION_MERGE => date('Y-m-d H:i:s', time() - 3600),
        ];
        $this->setMergedTypes();
        $this->assertNotEmpty($this->types);
    }
}