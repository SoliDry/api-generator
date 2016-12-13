<?php

namespace rjapi\blocks;

use rjapi\controllers\LaravelRJApiGenerator;
use rjapi\extension\json\api\rest\mapper\BaseMapperController;
use rjapi\controllers\YiiRJApiGenerator;
use rjapi\helpers\Classes;

class Controllers implements ControllersInterface
{
    use ContentManager;

    /** @var YiiRJApiGenerator | LaravelRJApiGenerator generator */
    private $generator  = null;
    private $sourceCode = '';

    /**
     * Controllers constructor.
     * @param LaravelRJApiGenerator | YiiRJApiGenerator $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
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
                          . DefaultInterface::DEFAULT_POSTFIX
                          . PhpEntitiesInterface::PHP_EXT;

        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $baseFullMapper = BaseMapperController::class;
        $baseMapperName = Classes::getName($baseFullMapper);

        $this->setUse($baseFullMapper);
        $this->startClass($this->generator->defaultController . DefaultInterface::DEFAULT_POSTFIX, $baseMapperName);
        $this->endClass();
        FileManager::createFile($fileController, $this->sourceCode);
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $this->startClass(
            $this->generator->objectName . DefaultInterface::DEFAULT_POSTFIX,
            $this->generator->defaultController . DefaultInterface::DEFAULT_POSTFIX
        );
        $this->endClass();
        $fileController = $this->generator->formatControllersPath()
                          . PhpEntitiesInterface::SLASH
                          . $this->generator->objectName
                          . DefaultInterface::DEFAULT_POSTFIX
                          . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($fileController, $this->sourceCode);
    }
}