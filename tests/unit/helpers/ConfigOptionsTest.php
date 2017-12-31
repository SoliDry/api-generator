<?php
namespace rjapitest\unit\helpers;

use rjapi\helpers\ConfigOptions;
use rjapitest\unit\TestCase;

/**
 * Class ConfigOptionsTest
 * @package rjapitest\unit\blocks
 * @property ConfigOptions configOptions
 */
class ConfigOptionsTest extends TestCase
{
    private $configOptions;

    public function setUp()
    {
        parent::setUp();
        $this->configOptions = new ConfigOptions();
    }

    /**
     * @test
     */
    public function it_sets_configuration_options()
    {
        $accessToken = sha1(time());
        $this->configOptions->setQueryAccessToken($accessToken);
        $this->assertEquals($this->configOptions->getQueryAccessToken(), $accessToken);

        $limit = mt_rand(5, 10);
        $this->configOptions->setQueryLimit($limit);
        $this->assertEquals($this->configOptions->getQueryLimit(), $limit);

        $sort = 'desc';
        $this->configOptions->setQuerySort($sort);
        $this->assertEquals($this->configOptions->getQuerySort(), $sort);

        $page = 1;
        $this->configOptions->setQueryPage($page);
        $this->assertEquals($this->configOptions->getQueryPage(), $page);

        $this->configOptions->setJwtIsEnabled(true);
        $this->assertTrue($this->configOptions->getJwtIsEnabled());

        $jwtTable = 'users';
        $this->configOptions->setJwtTable($jwtTable);
        $this->assertEquals($this->configOptions->getJwtTable(), $jwtTable);

        $this->configOptions->setIsJwtAction(true);
        $this->assertTrue($this->configOptions->getIsJwtAction());
    }
}