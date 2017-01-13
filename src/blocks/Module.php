<?php

namespace rjapi\blocks;

use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Module
{
    use ContentManager;

    protected $sourceCode = '';
    /** @var RJApiGenerator generator */
    protected $generator = null;
    protected $className = null;

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $output = [];
        exec(CommandsInterface::LARAVEL_MODULE_MAKE . PhpEntitiesInterface::SPACE . $this->generator->version, $output);
        exec(CommandsInterface::LARAVEL_MODULE_USE . PhpEntitiesInterface::SPACE . $this->generator->version, $output);
        foreach($output as $str)
        {
            Console::out($str, Console::COLOR_GREEN);
        }
        $this->setTag();
        $this->createModuleContent();
        FileManager::createModuleConfig($this->sourceCode);
    }

    private function createModuleContent()
    {
        $this->sourceCode .= PhpEntitiesInterface::PHP_RETURN . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::OPEN_BRACKET . PHP_EOL . PhpEntitiesInterface::QUOTES
                             . ModulesInterface::KEY_MODULES . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::DOUBLE_ARROW . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::OPEN_BRACKET . PHP_EOL . PhpEntitiesInterface::QUOTES
                             . $this->generator->version . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA
                             . PHP_EOL . PhpEntitiesInterface::CLOSE_BRACKET . PHP_EOL
                             . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::SEMICOLON;
    }
}