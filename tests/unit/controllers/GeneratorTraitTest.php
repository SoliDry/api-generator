<?php

namespace rjapitest\unit\controllers;


use rjapi\controllers\GeneratorTrait;
use rjapi\types\ConsoleInterface;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;

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

    public function setUp()
    {
        parent::setUp();
        $this->options = [ // merge last option
                             ConsoleInterface::OPTION_MERGE => ConsoleInterface::MERGE_DEFAULT_VALUE,
        ];
        $this->files   = [
            __DIR__ . '/../../functional/raml/articles.raml',
        ];
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
        $this->options = [ // merge last option
                           ConsoleInterface::OPTION_MERGE => date('Y-m-d H:i:s', time() - 3600),
        ];
        $this->setMergedTypes();
        $this->assertNotEmpty($this->types);
    }
}