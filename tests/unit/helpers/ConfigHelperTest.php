<?php
namespace rjapitest\unit\helpers;

use rjapi\helpers\ConfigHelper;
use rjapitest\unit\TestCase;

/**
 * Class ConfigHelperTest
 * @package rjapitest\unit\helpers
 * @property ConfigHelper $configHelper
 */
class ConfigHelperTest extends TestCase
{
    private $configHelper;

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
        $this->configHelper = new ConfigHelper();
    }

    public function testGetters()
    {
        $paramSort = 'sort';
        $sortData = ConfigHelper::getQueryParam($paramSort);
        $this->assertEquals($sortData, $this->params[$paramSort]);
    }
}