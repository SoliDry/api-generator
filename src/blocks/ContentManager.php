<?php

namespace rjapi\blocks;

use rjapi\controllers\YiiRJApiGenerator;

trait ContentManager
{
    protected function setTag()
    {
        $this->sourceCode = YiiRJApiGenerator::PHP_OPEN_TAG . PHP_EOL;
    }

    protected function setNamespace($postfix)
    {
        $this->sourceCode .= YiiRJApiGenerator::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                             YiiRJApiGenerator::BACKSLASH
                             . $this->generator->modulesDir . YiiRJApiGenerator::BACKSLASH . $this->generator->version
                             . YiiRJApiGenerator::BACKSLASH . $postfix . YiiRJApiGenerator::SEMICOLON
                             . PHP_EOL . PHP_EOL;
    }

    protected function setUse($path, $isTrait = false)
    {
        $this->sourceCode .= (($isTrait === false) ? '' : PhpEntitiesInterface::TAB_PSR4) .
                             YiiRJApiGenerator::PHP_USE . ' ' . $path . YiiRJApiGenerator::SEMICOLON .
                             PHP_EOL . PHP_EOL;
    }

    protected function startClass($name, $extends = null)
    {
        $this->sourceCode .= YiiRJApiGenerator::PHP_CLASS . ' ' . $name . ' ';
        if($extends !== null)
        {
            $this->sourceCode .=
                YiiRJApiGenerator::PHP_EXTENDS
                . ' ' . $extends . ' ';
        }
        $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::CLOSE_BRACE . PHP_EOL;
    }

    protected function startMethod($name, $modifier, $returnType, $static = false)
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . $modifier . PhpEntitiesInterface::SPACE .
                             (($static !== false) ? PhpEntitiesInterface::PHP_STATIC : '') . ' ' .
                             YiiRJApiGenerator::PHP_FUNCTION . ' ' .
                             $name .
                             YiiRJApiGenerator::OPEN_PARENTHESES . YiiRJApiGenerator::CLOSE_PARENTHESES .
                             YiiRJApiGenerator::COLON
                             . ' ' . $returnType . ' ' . YiiRJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    protected function methodReturn($value, $isString = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
                             PhpEntitiesInterface::PHP_RETURN . ' ' . (($isString === false) ? $value :
                '"' . $value . '"') . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::CLOSE_BRACE;
    }

    protected function startArray()
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4 .
                             YiiRJApiGenerator::PHP_RETURN . ' ' .
                             YiiRJApiGenerator::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . YiiRJApiGenerator::TAB_PSR4 . YiiRJApiGenerator::TAB_PSR4
                             . YiiRJApiGenerator::CLOSE_BRACKET . YiiRJApiGenerator::SEMICOLON . PHP_EOL;
    }

    protected function createProperty($prop, $modifier)
    {
        $this->sourceCode .= YiiRJApiGenerator::TAB_PSR4 . $modifier . ' ' . YiiRJApiGenerator::DOLLAR_SIGN . $prop
                             . YiiRJApiGenerator::SPACE . YiiRJApiGenerator::EQUALS . YiiRJApiGenerator::SPACE
                             . YiiRJApiGenerator::PHP_TYPES_NULL . YiiRJApiGenerator::SEMICOLON . PHP_EOL;
    }
}