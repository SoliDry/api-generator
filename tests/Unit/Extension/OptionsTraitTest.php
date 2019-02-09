<?php

namespace SoliDryTest\Unit\Extensions;


use Illuminate\Http\Request;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Extension\OptionsTrait;
use SoliDry\Helpers\ConfigOptions;
use SoliDry\Types\ModelsInterface;
use SoliDryTest\Unit\TestCase;

/**
 * Class OptionsTraitTest
 * @package rjapitest\Unit\Extensions
 *
 * @property ConfigOptions configOptions
 */
class OptionsTraitTest extends TestCase
{
    use OptionsTrait;

    private $configOptions;
    private $entity;

    public function setUp()
    {
        parent::setUp();
        $this->entity = 'article'; // test cache etc
    }

    /**
     * @test
     */
    public function it_sets_default()
    {
        $this->setDefaults();
        $this->assertEquals(ModelsInterface::DEFAULT_PAGE, $this->defaultPage);
        // check query_params preset from config
        $this->assertEquals(15, $this->defaultLimit);
        $this->assertEquals('desc', $this->defaultSort);
    }

    /**
     * @test
     */
    public function it_sets_sql_options()
    {
        $req        = new Request();
        $sqlOptions = $this->setSqlOptions($req);
        $this->assertEquals(20, $sqlOptions->getLimit());
        $this->assertEquals(1, $sqlOptions->getPage());
    }

    /**
     * @test
     */
    public function it_sets_config_options()
    {
        $this->setConfigOptions(JSONApiInterface::URI_METHOD_INDEX);
        $this->assertEquals(JSONApiInterface::URI_METHOD_INDEX, $this->configOptions->getCalledMethod());
        $this->assertTrue($this->configOptions->isCached());
        // check fsm, spelling
        $this->setConfigOptions(JSONApiInterface::URI_METHOD_CREATE);
        $this->setConfigOptions(JSONApiInterface::URI_METHOD_UPDATE);
        $this->assertTrue($this->configOptions->isStateMachine());
        $this->assertTrue($this->configOptions->isSpellCheck());

        // test jwt etc
        $this->entity = 'user';
        $this->setConfigOptions(JSONApiInterface::URI_METHOD_INDEX);
        $this->assertTrue($this->configOptions->getIsJwtAction());
        // bit-mask
        $this->assertTrue($this->configOptions->isBitMask());
    }
}