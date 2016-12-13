<?php

namespace rjapi\blocks;

use rjapi\controllers\YiiRJApiGenerator;
use yii\console\Controller;

class Module
{
    use ContentManager;
    /** @var YiiRJApiGenerator generator */
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
        $this->sourceCode .= YiiRJApiGenerator::PHP_NAMESPACE . ' ' . $this->generator->appDir
                             . YiiRJApiGenerator::BACKSLASH . $this->generator->modulesDir . YiiRJApiGenerator::BACKSLASH
                             . $this->generator->version . YiiRJApiGenerator::SEMICOLON . PHP_EOL . PHP_EOL;

        $baseFullFormOut = \rjapi\extension\json\api\base\Module::class;
        $this->startClass(YiiRJApiGenerator::DEFAULT_MODULE, $baseFullFormOut);
        $this->endClass();

        $fileModule = FileManager::getModulePath($this->generator) . YiiRJApiGenerator::DEFAULT_MODULE
                      . YiiRJApiGenerator::PHP_EXT;
        FileManager::createFile($fileModule, $this->sourceCode);
    }
}