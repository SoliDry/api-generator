<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\json\api\rest\mapper\BaseMapperController;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Controllers implements ControllersInterface
{
    use ContentManager;

    /** @var TypesController generator */
    private $generator  = null;
    private $sourceCode = '';

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState(Controller $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return bool - true if Controller has been created, false otherwise.
     */
    public function createDefault()
    {
        $fileController = $this->generator->formatControllersPath()
                          . TypesController::SLASH
                          . $this->generator->defaultController
                          . TypesController::DEFAULT_POSTFIX
                          . TypesController::PHP_EXT;

        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $baseFullMapper = BaseMapperController::class;
        $baseMapperName = StringHelper::basename($baseFullMapper);

        $this->setUse($baseFullMapper);
        $this->startClass($this->generator->defaultController . TypesController::DEFAULT_POSTFIX, $baseMapperName);
        $this->endClass();
        FileManager::createFile($fileController, $this->sourceCode);
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $this->startClass(
            $this->generator->objectName . TypesController::DEFAULT_POSTFIX,
            $this->generator->defaultController . TypesController::DEFAULT_POSTFIX
        );
        $this->endClass();
        $fileController = $this->generator->formatControllersPath()
                          . TypesController::SLASH
                          . $this->generator->objectName
                          . TypesController::DEFAULT_POSTFIX
                          . TypesController::PHP_EXT;
        FileManager::createFile($fileController, $this->sourceCode);
    }
}