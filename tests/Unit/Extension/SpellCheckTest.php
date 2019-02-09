<?php

namespace SoliDryTest\Unit\Extensions;


use SoliDry\Extension\SpellCheck;
use SoliDry\Extension\SpellCheckTrait;
use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;
use SoliDryTest\Unit\TestCase;

/**
 * Class SpellCheckTest
 *
 * @package rjapitest\Unit\Extensions
 *
 * @property SpellCheck spellCheck
 */
class SpellCheckTest extends TestCase
{
    use SpellCheckTrait;

    private const ENTITY = 'article';
    private const DESCRIPTION = 'description';

    private $spellCheck;
    private $entity;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->spellCheck = new SpellCheck(self::ENTITY);
        $this->entity = 'article';
    }

    /**
     * @test
     */
    public function it_checks_text()
    {
        $this->assertEmpty($this->spellCheck->check('It checks the text correctness.'));
        $this->assertArraySubset(['correctnesss'], $this->spellCheck->check('It checks the text correctnesss.'));
    }

    /**
     * @test
     */
    public function it_is_enabled()
    {
        $this->assertTrue($this->spellCheck->isEnabled());
    }

    /**
     * @test
     */
    public function it_gets_language()
    {
        $this->assertEquals($this->spellCheck->getLanguage(), ConfigInterface::DEFAULT_LANGUAGE);
    }

    /**
     * @test
     */
    public function it_get_field()
    {
        $this->spellCheck = new SpellCheck(self::ENTITY);
        $this->assertEquals($this->spellCheck->getField(), self::DESCRIPTION);
    }

    /**
     * @test
     */
    public function it_checks_spell_via_trait()
    {
        $this->assertArraySubset([], $this->spellCheck([
            'description' => 'Standard text for testing'
        ]));
    }
}