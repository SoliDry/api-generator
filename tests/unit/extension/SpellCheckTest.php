<?php

namespace rjapitest\unit\extensions;


use rjapi\extension\SpellCheck;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;
use rjapitest\unit\TestCase;

/**
 * Class SpellCheckTest
 *
 * @package rjapitest\unit\extensions
 *
 * @property SpellCheck spellCheck
 */
class SpellCheckTest extends TestCase
{
    private const ENTITY = 'article';
    private const DESCRIPTION = 'description';

    private $spellCheck;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->spellCheck = new SpellCheck(self::ENTITY);
    }

    /**
     * @test
     */
    public function it_checks_text()
    {
        $this->assertEmpty($this->spellCheck->check('It checks the text correctness.'));
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
}