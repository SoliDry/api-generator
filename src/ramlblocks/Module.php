<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;

class Module
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

    public function createModule()
    {
        $this->setTag();
        $this->sourceCode .= TypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir
                             . TypesController::BACKSLASH . $this->generator->modulesDir . TypesController::BACKSLASH
                             . $this->generator->version . TypesController::SEMICOLON . PHP_EOL . PHP_EOL;

        $baseFullFormOut = \tass\extension\json\api\base\Module::class;
        $this->startClass(TypesController::DEFAULT_MODULE, $baseFullFormOut);
        $this->endClass();

        $fileModule = FileManager::getModulePath($this->generator) . TypesController::DEFAULT_MODULE
                      . TypesController::PHP_EXT;
        FileManager::createFile($fileModule, $this->sourceCode);
    }
}