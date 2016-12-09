<?php

namespace rjapi\blocks;

use rjapi\controllers\YiiTypesController;

trait ContentManager
{
    protected function setTag()
    {
        $this->sourceCode = YiiTypesController::PHP_OPEN_TAG . PHP_EOL;
    }

    protected function setNamespace($postfix)
    {
        $this->sourceCode .= YiiTypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                             YiiTypesController::BACKSLASH
                             . $this->generator->modulesDir . YiiTypesController::BACKSLASH . $this->generator->version
                             . YiiTypesController::BACKSLASH . $postfix . YiiTypesController::SEMICOLON
                             . PHP_EOL . PHP_EOL;
    }

    protected function setUse($path)
    {
        $this->sourceCode .= YiiTypesController::PHP_USE . ' ' . $path . YiiTypesController::SEMICOLON .
                             PHP_EOL . PHP_EOL;
    }

    protected function startClass($name, $extends = null)
    {
        $this->sourceCode .= YiiTypesController::PHP_CLASS . ' ' . $name . ' ';
        if($extends !== null)
        {
            $this->sourceCode .=
                YiiTypesController::PHP_EXTENDS
                . ' ' . $extends . ' ';
        }
        $this->sourceCode .= PHP_EOL . YiiTypesController::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= PHP_EOL . YiiTypesController::CLOSE_BRACE . PHP_EOL;
    }

    protected function startMethod($name, $modifier, $returnType, $static = false)
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . $modifier .
                             (($static !== false) ? PhpEntitiesInterface::PHP_STATIC : '') . ' ' .
                             YiiTypesController::PHP_FUNCTION . ' ' .
                             $name .
                             YiiTypesController::OPEN_PARENTHESES . YiiTypesController::CLOSE_PARENTHESES .
                             YiiTypesController::COLON
                             . ' ' . $returnType . ' ' . YiiTypesController::OPEN_BRACE . PHP_EOL;
    }

    protected function methodReturn($value, $isString = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
                             PhpEntitiesInterface::PHP_RETURN . ' ' . (($isString === false) ? $value :
                '"' . $value . '"') . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::CLOSE_BRACE;
    }

    protected function startArray()
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4 .
                             YiiTypesController::PHP_RETURN . ' ' .
                             YiiTypesController::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . YiiTypesController::TAB_PSR4 . YiiTypesController::TAB_PSR4
                             . YiiTypesController::CLOSE_BRACKET . YiiTypesController::SEMICOLON . PHP_EOL;
    }

    protected function createProperty($prop, $modifier)
    {
        $this->sourceCode .= YiiTypesController::TAB_PSR4 . $modifier . ' ' . YiiTypesController::DOLLAR_SIGN . $prop
                             . YiiTypesController::SPACE . YiiTypesController::EQUALS . YiiTypesController::SPACE
                             . YiiTypesController::PHP_TYPES_NULL . YiiTypesController::SEMICOLON . PHP_EOL;
    }
}