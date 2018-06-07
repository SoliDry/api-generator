<?php

namespace rjapitest\unit\extensions;


use rjapi\extension\SpellCheck;
use rjapi\extension\SpellCheckTrait;
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