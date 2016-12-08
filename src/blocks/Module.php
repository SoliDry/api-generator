<?php

namespace rjapi\extension\yii2\raml\blocks;

use rjapi\extension\yii2\raml\controllers\SchemaController;
use yii\console\Controller;

class Module
{
    /** @var SchemaController generator */
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
        $this->sourceCode = SchemaController::PHP_OPEN_TAG . PHP_EOL;
        $this->sourceCode .= SchemaController::PHP_NAMESPACE . ' ' . $this->generator->appDir . SchemaController::BACKSLASH . $this->generator->modulesDir . SchemaController::BACKSLASH . $this->generator->version . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;
        $this->sourceCode .= SchemaController::PHP_CLASS . ' ' . SchemaController::DEFAULT_MODULE . ' ' . SchemaController::PHP_EXTENDS . ' \rjapi\extension\json\api\base\\' . SchemaController::DEFAULT_MODULE . ' ' . SchemaController::OPEN_BRACE . PHP_EOL;
        $this->sourceCode .= PHP_EOL . SchemaController::CLOSE_BRACE . PHP_EOL;
        $this->createModuleFile();
    }

    private function createModuleFile()
    {
        $fileModule = $this->generator->rootDir . $this->generator->modulesDir . SchemaController::SLASH . $this->generator->version . SchemaController::SLASH . SchemaController::DEFAULT_MODULE . SchemaController::PHP_EXT;

        $fpModule = fopen($fileModule, 'w');
        fwrite($fpModule, $this->sourceCode);
        fclose($fpModule);
    }
}