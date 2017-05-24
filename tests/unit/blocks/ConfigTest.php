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
    private $config;

    public function setUp()
    {
        parent::setUp();
        $gen = new RJApiGenerator();
        $gen->modulesDir = DirsInterface::MODULES_DIR;
        $gen->version = 'V1';
        $this->config = new Config($gen);
    }

    public function testSetName()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
        $this->config->create();
    }
}