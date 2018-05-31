<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\Config;
use rjapi\RJApiGenerator;
use rjapi\types\ConfigInterface;
use rjapi\types\DirsInterface;
use rjapi\types\RamlInterface;
use rjapitest\unit\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigTest
 * @package rjapitest\unit\blocks
 * @property Config config
 */
class ConfigTest extends TestCase
{
    /** @var RJApiGenerator $gen */
    private $gen;
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->gen                 = new RJApiGenerator();
        $this->gen->modulesDir     = DirsInterface::MODULES_DIR;
        $this->gen->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->gen->httpDir        = DirsInterface::HTTP_DIR;
        $this->gen->version        = 'V2';
        $ramlData                  = Yaml::parse(file_get_contents(__DIR__ . '/../../functional/raml/articles.raml'));
        $this->gen->types = $ramlData[RamlInterface::RAML_KEY_TYPES];
        $this->gen->objectProps = [
            'type'          => 'Type',
            'id'            => 'ID',
            'attributes'    => 'ArticleAttributes',
            'relationships' => [
                'type' => 'TagRelationships[] | TopicRelationships',
            ]
        ];
        $this->config           = new Config($this->gen);
    }

    /**
     * @test
     */
    public function it_creates_config()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
        // todo: mock ConfigInterface::DEFAULT_ACTIVATE for jwt to run without failing
        $this->config->create();
        $this->assertTrue(file_exists($this->gen->formatConfigPath() . 'config.php'));
    }
}