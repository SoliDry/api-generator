<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

class Module
{
    use ContentManager;
    /** @var RJApiGenerator generator */
    private $generator  = null;
    private $sourceCode = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function createModule()
    {
        $this->setTag();
        $this->sourceCode .= PhpEntitiesInterface::PHP_NAMESPACE . ' ' . $this->generator->appDir
                             . PhpEntitiesInterface::BACKSLASH . $this->generator->modulesDir . PhpEntitiesInterface::BACKSLASH
                             . $this->generator->version . PhpEntitiesInterface::SEMICOLON . PHP_EOL . PHP_EOL;

        $baseFullFormOut = \rjapi\extension\json\api\base\Module::class;
        $this->startClass(DefaultInterface::DEFAULT_MODULE, $baseFullFormOut);
        $this->endClass();

        $fileModule = FileManager::getModulePath($this->generator) . DefaultInterface::DEFAULT_MODULE
                      . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($fileModule, $this->sourceCode);
    }
}