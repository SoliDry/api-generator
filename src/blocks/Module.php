<?php

namespace rjapi\blocks;

use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;
use rjapi\types\CommandsInterface;
use rjapi\types\ModulesInterface;
use rjapi\types\PhpInterface;

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
        exec(CommandsInterface::LARAVEL_MODULE_MAKE . PhpInterface::SPACE . $this->generator->version, $output);
        exec(CommandsInterface::LARAVEL_MODULE_USE . PhpInterface::SPACE . $this->generator->version, $output);
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
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE
                             . PhpInterface::OPEN_BRACKET . PHP_EOL . PhpInterface::TAB_PSR4
                             . PhpInterface::QUOTES . ModulesInterface::KEY_MODULES . PhpInterface::QUOTES
                             . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . PhpInterface::OPEN_BRACKET . PHP_EOL . PhpInterface::TAB_PSR4
                             . PhpInterface::TAB_PSR4 . PhpInterface::QUOTES
                             . $this->generator->version . PhpInterface::QUOTES . PhpInterface::COMMA
                             . PHP_EOL . PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACKET . PHP_EOL
                             . PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON;
    }
}