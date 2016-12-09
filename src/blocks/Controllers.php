<?php

namespace rjapi\blocks;

use rjapi\extension\json\api\rest\mapper\BaseMapperController;
use rjapi\controllers\YiiTypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Controllers implements ControllersInterface
{
    use ContentManager;

    /** @var YiiTypesController generator */
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
                          . YiiTypesController::SLASH
                          . $this->generator->defaultController
                          . YiiTypesController::DEFAULT_POSTFIX
                          . YiiTypesController::PHP_EXT;

        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $baseFullMapper = BaseMapperController::class;
        $baseMapperName = StringHelper::basename($baseFullMapper);

        $this->setUse($baseFullMapper);
        $this->startClass($this->generator->defaultController . YiiTypesController::DEFAULT_POSTFIX, $baseMapperName);
        $this->endClass();
        FileManager::createFile($fileController, $this->sourceCode);
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace($this->generator->controllersDir);
        $this->startClass(
            $this->generator->objectName . YiiTypesController::DEFAULT_POSTFIX,
            $this->generator->defaultController . YiiTypesController::DEFAULT_POSTFIX
        );
        $this->endClass();
        $fileController = $this->generator->formatControllersPath()
                          . YiiTypesController::SLASH
                          . $this->generator->objectName
                          . YiiTypesController::DEFAULT_POSTFIX
                          . YiiTypesController::PHP_EXT;
        FileManager::createFile($fileController, $this->sourceCode);
    }
}