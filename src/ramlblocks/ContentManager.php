<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\yii2\raml\controllers\TypesController;

trait ContentManager
{
    protected function setTag()
    {
        $this->sourceCode = TypesController::PHP_OPEN_TAG . PHP_EOL;
    }

    protected function setNamespace($postfix)
    {
        $this->sourceCode .= TypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                             TypesController::BACKSLASH
                             . $this->generator->modulesDir . TypesController::BACKSLASH . $this->generator->version
                             . TypesController::BACKSLASH . $postfix . TypesController::SEMICOLON
                             . PHP_EOL . PHP_EOL;
    }

    protected function setUse($path)
    {
        $this->sourceCode .= TypesController::PHP_USE . ' ' . $path . TypesController::SEMICOLON .
                             PHP_EOL . PHP_EOL;
    }

    protected function startClass($name, $extends = null)
    {
        $this->sourceCode .= TypesController::PHP_CLASS . ' ' . $name . ' ';
        if($extends !== null)
        {
            $this->sourceCode .=
                TypesController::PHP_EXTENDS
                . ' ' . $extends . ' ';
        }
        $this->sourceCode .= PHP_EOL . TypesController::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= PHP_EOL . TypesController::CLOSE_BRACE . PHP_EOL;
    }

    protected function startMethod($name, $modifier, $returnType)
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . $modifier . ' ' . TypesController::PHP_FUNCTION . ' ' . $name .
                             TypesController::OPEN_PARENTHESES . TypesController::CLOSE_PARENTHESES .
                             TypesController::COLON
                             . ' ' . $returnType . ' ' . TypesController::OPEN_BRACE . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::CLOSE_BRACE;
    }

    protected function startArray()
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::PHP_RETURN . ' ' .
                             TypesController::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . TypesController::TAB_PSR4 . TypesController::TAB_PSR4
                             . TypesController::CLOSE_BRACKET . TypesController::SEMICOLON . PHP_EOL;
    }

    protected function createProperty($prop, $modifier)
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . $modifier . ' ' . TypesController::DOLLAR_SIGN . $prop
                             . TypesController::SPACE . TypesController::EQUALS . TypesController::SPACE
                             . TypesController::PHP_TYPES_NULL . TypesController::SEMICOLON . PHP_EOL;
    }
}