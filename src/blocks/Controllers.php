<?php

namespace rjapi\blocks;

use rjapi\extension\BaseController;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Controllers implements ControllersInterface
{
    use ContentManager;

    /** @var RJApiGenerator generator */
    private $generator = null;
    private $sourceCode = '';
    private $className = '';

    /**
     * Controllers constructor.
     *
     * @param RJApiGenerator $generator
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

    /**
     * @return bool - true if Controller has been created, false otherwise.
     */
    public function createDefault()
    {
        $fileController = $this->generator->formatControllersPath()
                          . PhpEntitiesInterface::SLASH
                          . $this->generator->defaultController
                          . DefaultInterface::CONTROLLER_POSTFIX
                          . PhpEntitiesInterface::PHP_EXT;

        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir . PhpEntitiesInterface::BACKSLASH . $this->generator->controllersDir
        );
        $baseFullMapper = BaseController::class;
        $baseMapperName = Classes::getName($baseFullMapper);

        $this->setUse($baseFullMapper, false, true);
        $this->startClass($this->generator->defaultController . DefaultInterface::CONTROLLER_POSTFIX, $baseMapperName);
        $this->endClass();
        $isCreated = FileManager::createFile($fileController, $this->sourceCode);
        if($isCreated)
        {
            Console::out($fileController . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir . PhpEntitiesInterface::BACKSLASH . $this->generator->controllersDir
        );
        $this->startClass(
            $this->className . DefaultInterface::CONTROLLER_POSTFIX,
            $this->generator->defaultController . DefaultInterface::CONTROLLER_POSTFIX
        );
        $this->endClass();
        $fileController = $this->generator->formatControllersPath()
                          . PhpEntitiesInterface::SLASH
                          . $this->className
                          . DefaultInterface::CONTROLLER_POSTFIX
                          . PhpEntitiesInterface::PHP_EXT;
        $isCreated      = FileManager::createFile(
            $fileController, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($fileController . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}