<?php
namespace SoliDryTest\Unit\Helpers;

use SoliDry\Helpers\ConfigHelper;
use SoliDry\Types\ConfigInterface;
use SoliDryTest\Unit\TestCase;

/**
 * Class ConfigHelperTest
 * @package rjapitest\Unit\Helpers
 * @property ConfigHelper $configHelper
 */
class ConfigHelperTest extends TestCase
{
    private $params = [
        'limit' => 15,
        'sort' => 'desc',
        'access_token' => 'db7329d5a3f381875ea6ce7e28fe1ea536d0acaf',
        'enabled' => true,
        'table' => 'user',
        'activate' => 30,
        'expires' => 3600,
    ];

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_gets_query_param()
    {
        $paramSort = 'sort';
        $sortData = ConfigHelper::getQueryParam($paramSort);
        $this->assertEquals($sortData, $this->params[$paramSort]);
        $this->assertNull(ConfigHelper::getQueryParam('foo'));
    }

    /**
     * @test
     */
    public function it_gets_config_key()
    {
        $this->assertEquals(ConfigHelper::getConfigKey(), self::CONFIG_KEY);
    }

    /**
     * @test
     */
    public function it_gets_module_name()
    {
        $this->assertEquals(ConfigHelper::getModuleName(), self::MODULE_NAME);
    }

    /**
     * @test
     */
    public function it_gets_nested_param()
    {
        $this->assertTrue(ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ENABLED));
        $this->assertTrue(ConfigHelper::getNestedParam(ConfigInterface::JWT, ConfigInterface::ENABLED, true));
    }
}