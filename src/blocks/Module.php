<?php

namespace rjapi\blocks;

use rjapi\controllers\YiiTypesController;
use yii\console\Controller;

class Module
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

    public function createModule()
    {
        $this->setTag();
        $this->sourceCode .= YiiTypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir
                             . YiiTypesController::BACKSLASH . $this->generator->modulesDir . YiiTypesController::BACKSLASH
                             . $this->generator->version . YiiTypesController::SEMICOLON . PHP_EOL . PHP_EOL;

        $baseFullFormOut = \rjapi\extension\json\api\base\Module::class;
        $this->startClass(YiiTypesController::DEFAULT_MODULE, $baseFullFormOut);
        $this->endClass();

        $fileModule = FileManager::getModulePath($this->generator) . YiiTypesController::DEFAULT_MODULE
                      . YiiTypesController::PHP_EXT;
        FileManager::createFile($fileModule, $this->sourceCode);
    }
}