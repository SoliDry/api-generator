<?php

namespace rjapitest\unit\blocks;

use PHPUnit_Framework_MockObject_MockObject;
use rjapi\blocks\MigrationsAbstract;
use rjapi\ApiGenerator;
use rjapi\types\ConsoleInterface;
use rjapi\types\DirsInterface;
use rjapi\types\ApiInterface;
use rjapitest\unit\TestCase;
use rjapi\blocks\Migrations;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MigrationsTest
 * @package rjapitest\unit\blocks
 *
 * @property Migrations migrations
 * @property ApiGenerator gen
 */
class MigrationsTest extends TestCase
{
    private $migrations;
    private $gen;

    /**
     * @throws \ReflectionException
     */
    public function setUp()
    {
        parent::setUp();
        /** @var ApiGenerator|PHPUnit_Framework_MockObject_MockObject $gen */
        $this->gen = $this->createMock(ApiGenerator::class);
        $this->gen->method('options')->willReturn([
            ConsoleInterface::OPTION_REGENERATE => 1,
            ConsoleInterface::OPTION_MIGRATIONS => 1,
        ]);
        $this->gen->method('formatMigrationsPath')->willReturn(self::DIR_OUTPUT);
        $data                      = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/oas/openapi.yaml'));
        $this->gen->types          = $data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        $this->gen->objectProps    = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->gen->objectName     = 'Article';
        $this->gen->version        = self::MODULE_NAME;
        $this->gen->modulesDir     = DirsInterface::MODULES_DIR;
        $this->gen->formRequestDir = DirsInterface::FORM_REQUEST_DIR;
        $this->gen->migrationsDir  = DirsInterface::MIGRATIONS_DIR;
        $this->migrations          = new Migrations($this->gen);
    }

    /**
     * @test
     */
    public function it_creates_entity()
    {
        $this->migrations->setCodeState($this->gen);
        $this->assertInstanceOf(MigrationsAbstract::class, $this->migrations);
        $this->migrations->create();
        $this->migrations->createPivot();
    }

    /**
     * @test
     */
    public function it_resets_content()
    {
        $this->gen->isMerge   = true;
        $this->gen->diffTypes = [
            'ArticleAttributes' => [
                'title' => [
                    'required'  => 'true',
                    'type'      => 'string',
                    'minLength' => 32,
                    'maxLength' => 128,
                ]
            ]
        ];
        $this->migrations     = new Migrations($this->gen);
        $this->migrations->create();
        $this->assertNotEmpty(glob(self::DIR_OUTPUT . '*add_column_title_to_article.php'));
    }

    public static function tearDownAfterClass()
    {
        $files = glob(self::DIR_OUTPUT . '*add_column_title_to_article.php');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}