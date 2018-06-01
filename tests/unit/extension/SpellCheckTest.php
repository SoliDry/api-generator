<?php
/**
 * Created by PhpStorm.
 * User: arthurkushman
 * Date: 01/06/2018
 * Time: 13:18
 */

namespace rjapitest\unit\extensions;


use rjapi\extension\SpellCheck;
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
    private $spellCheck;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->spellCheck = new SpellCheck('article');
    }

    /**
     * @test
     */
    public function it_checks_text()
    {

    }

    /**
     * @test
     */
    public function it_is_enabled()
    {

    }

    /**
     * @test
     */
    public function it_gets_language()
    {

    }

    /**
     * @test
     */
    public function it_get_field()
    {

    }
}