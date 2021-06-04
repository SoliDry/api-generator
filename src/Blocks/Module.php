<?php

namespace SoliDry\Blocks;

use SoliDry\Helpers\Classes;
use SoliDry\Helpers\Console;
use SoliDry\ApiGenerator;
use SoliDry\Types\CommandsInterface;
use SoliDry\Types\ModulesInterface;
use SoliDry\Types\PhpInterface;

class Module
{
    use ContentManager;

    /**
     * @var string
     */
    protected string $sourceCode = '';

    /**
     * @var ApiGenerator
     */
    protected ApiGenerator $generator;

    /**
     * @var string
     */
    protected string $className;

    /**
     * Module constructor.
     * @param $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    /**
     * @param $generator
     */
    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $output = [];
        exec(CommandsInterface::LARAVEL_MODULE_MAKE . PhpInterface::SPACE . $this->generator->version, $output);
        exec(CommandsInterface::LARAVEL_MODULE_USE . PhpInterface::SPACE . $this->generator->version, $output);
        exec(CommandsInterface::LARAVEL_MODULE_LIST, $output);
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
                             . $this->quoteParam(ModulesInterface::KEY_MODULES)
                             . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . PhpInterface::OPEN_BRACKET . PHP_EOL . PhpInterface::TAB_PSR4
                             . PhpInterface::TAB_PSR4 . $this->quoteParam($this->generator->version)
                             . PhpInterface::COMMA . PHP_EOL . PhpInterface::TAB_PSR4
                             . PhpInterface::CLOSE_BRACKET . PHP_EOL
                             . PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON;
    }
}