<?php

namespace rjapi\extension\yii2\raml\blocks;

use rjapi\extension\yii2\raml\controllers\SchemaController;
use yii\console\Controller;

class Controllers implements IControllers
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

    /**
     * @return bool - true if Controller has been created, false otherwise.
     */
    public function createDefault()
    {
        $fileController = $this->generator->rootDir . $this->generator->modulesDir . SchemaController::SLASH . $this->generator->version . SchemaController::SLASH . $this->generator->controllersDir
                          . SchemaController::SLASH . $this->generator->defaultController . SchemaController::DEFAULT_POSTFIX . SchemaController::PHP_EXT;
        if(!file_exists($fileController))
        {
            $this->sourceCode = SchemaController::PHP_OPEN_TAG . PHP_EOL;
            $this->sourceCode .= SchemaController::PHP_NAMESPACE . ' ' . $this->generator->appDir . SchemaController::BACKSLASH . $this->generator->modulesDir . SchemaController::BACKSLASH . $this->generator->version . SchemaController::BACKSLASH
                                 . $this->generator->controllersDir . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

            $this->sourceCode .= SchemaController::PHP_USE . ' tass\extension\json\api\rest\mapper\\' . SchemaController::DEFAULT_CONTROLLER . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

            $this->sourceCode .= SchemaController::PHP_CLASS . ' ' . $this->generator->defaultController . SchemaController::DEFAULT_POSTFIX . ' ' . SchemaController::PHP_EXTENDS . ' ' . SchemaController::DEFAULT_CONTROLLER . ' ' . SchemaController::OPEN_BRACE;
            $this->sourceCode .= SchemaController::CLOSE_BRACE;

            $fpController = fopen($fileController, 'w');
            fwrite($fpController, $this->sourceCode);
            fclose($fpController);

            return true;
        }

        return false;
    }

    public function create()
    {
        $fileController = $this->generator->rootDir . $this->generator->modulesDir . SchemaController::SLASH . $this->generator->version . SchemaController::SLASH . $this->generator->controllersDir
                          . SchemaController::SLASH . $this->generator->controller . SchemaController::DEFAULT_POSTFIX . SchemaController::PHP_EXT;
//        if(file_exists($fileController))
//        {
//            echo 'Controller already exists - ' . $fileController . PHP_EOL;
//            echo 'Do U want to regenerate it? (y/n)';
//            $answer = fgets(STDIN);
//            if($answer === 'y')
//            {
//                unlink($fileController);
//            }
//            else
//            {
//                exit(0);
//            }
//        }

        $this->sourceCode = SchemaController::PHP_OPEN_TAG . PHP_EOL;
        $this->sourceCode .= SchemaController::PHP_NAMESPACE . ' ' . $this->generator->appDir . SchemaController::BACKSLASH . $this->generator->modulesDir . SchemaController::BACKSLASH . $this->generator->version . SchemaController::BACKSLASH
                             . $this->generator->controllersDir . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

        $this->sourceCode .= SchemaController::PHP_CLASS . ' ' . $this->generator->controller . SchemaController::DEFAULT_POSTFIX . ' ' . SchemaController::PHP_EXTENDS . ' ' . $this->generator->defaultController . SchemaController::DEFAULT_POSTFIX . ' ' . SchemaController::OPEN_BRACE;
        $this->sourceCode .= SchemaController::CLOSE_BRACE;

        $fpController = fopen($fileController, 'w');
        fwrite($fpController, $this->sourceCode);
        fclose($fpController);
    }
}