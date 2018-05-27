<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\Config;
use rjapi\blocks\ContentManager;
use rjapi\RJApiGenerator;
use rjapi\types\ConfigInterface;
use rjapi\types\DirsInterface;
use rjapitest\unit\TestCase;

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
        $this->gen = new RJApiGenerator();
        $this->gen->modulesDir = DirsInterface::MODULES_DIR;
        $this->gen->version = 'V2';
        $this->config = new Config($this->gen);
    }

    /**
     * @test
     */
    public function it_creates_config()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
        $this->config->create();
        $this->assertTrue(file_exists($this->gen->formatConfigPath() . 'config.php'));
    }
}