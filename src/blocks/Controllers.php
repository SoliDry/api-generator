<?php

namespace rjapi\blocks;

use rjapi\extension\BaseController;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;
use rjapi\types\ControllersInterface;
use rjapi\types\DefaultInterface;
use rjapi\types\PhpInterface;

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
     * Creates the DefaultController and outputs path to the console
     */
    public function createDefault()
    {
        $this->setDefaultContent();
        $fileController = $this->generator->formatControllersPath()
            . PhpInterface::SLASH
            . $this->generator->defaultController
            . DefaultInterface::CONTROLLER_POSTFIX
            . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile($fileController, $this->sourceCode);
        if($isCreated)
        {
            Console::out($fileController . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     * Creates *Controller and outputs path to the console
     */
    public function create()
    {
        $this->setContent();
        $fileController = $this->generator->formatControllersPath()
                          . PhpInterface::SLASH
                          . $this->className
                          . DefaultInterface::CONTROLLER_POSTFIX
                          . PhpInterface::PHP_EXT;
        $isCreated      = FileManager::createFile(
            $fileController, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($fileController . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     *  Sets *Controller content
     */
    private function setContent()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir . PhpInterface::BACKSLASH . $this->generator->controllersDir
        );
        $this->startClass(
            $this->className . DefaultInterface::CONTROLLER_POSTFIX,
            $this->generator->defaultController . DefaultInterface::CONTROLLER_POSTFIX
        );
        $this->endClass();
    }

    /**
     *  Sets the DefaultController content
     */
    private function setDefaultContent()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir . PhpInterface::BACKSLASH . $this->generator->controllersDir
        );
        $baseFullMapper = BaseController::class;
        $baseMapperName = Classes::getName($baseFullMapper);

        $this->setUse($baseFullMapper, false, true);
        $this->startClass($this->generator->defaultController . DefaultInterface::CONTROLLER_POSTFIX, $baseMapperName);
        $this->endClass();
    }
}