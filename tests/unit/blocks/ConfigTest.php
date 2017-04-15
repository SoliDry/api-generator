<?php
namespace rjapitest\unit\blocks;

use rjapi\blocks\Config;
use rjapi\blocks\ContentManager;
use rjapi\RJApiGenerator;
use rjapi\types\ConfigInterface;
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
        $this->config = new Config(new RJApiGenerator());
    }

    public function testSetName()
    {
        $this->assertInstanceOf(ConfigInterface::class, $this->config);
        $this->config->create();
    }
}