<?php

namespace SoliDry\Blocks;

use SoliDry\Extension\BaseController;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\Console;
use SoliDry\ApiGenerator;
use SoliDry\Types\ControllersInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\PhpInterface;

/**
 * Class Controllers
 * @package SoliDry\Blocks
 */
class Controllers implements ControllersInterface
{
    use ContentManager;

    /** @var ApiGenerator generator */
    private $generator;
    private $sourceCode = '';
    private $className;

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

    /**
     * Creates the DefaultController and outputs path to the console
     */
    public function createDefault(): void
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

    private function resetContent()
    {
        $this->setBeforeProps($this->getEntityFile($this->generator->formatControllersPath(), DefaultInterface::CONTROLLER_POSTFIX));
        $this->setComment(DefaultInterface::PROPS_START, 0);
        $this->setAfterProps();
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

        // set props comments to preserve user-land code on regen
        $this->setComment(DefaultInterface::PROPS_START);
        $this->setComment(DefaultInterface::PROPS_END);

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

        // set props comments to preserve user-land code on regen
        $this->setComment(DefaultInterface::PROPS_START);
        $this->setComment(DefaultInterface::PROPS_END);

        $this->endClass();
    }
}