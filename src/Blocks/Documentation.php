<?php

namespace SoliDry\Blocks;

use SoliDry\ApiGenerator;
use SoliDry\Controllers\BaseCommand;
use SoliDry\Helpers\Classes;
use SoliDry\Types\DefaultInterface;

/**
 * Class Documentation
 *
 * @package SoliDry\Blocks
 *
 * @property BaseCommand generator
 */
abstract class Documentation
{
    use ContentManager;

    protected $generator;
    protected $sourceCode = '';
    protected $className;

    /**
     * Controllers constructor.
     *
     * @param ApiGenerator $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    protected function setDefaultDocs()
    {
        $this->setComment(DefaultInterface::METHOD_START);



        $this->setComment(DefaultInterface::METHOD_END);
    }
}