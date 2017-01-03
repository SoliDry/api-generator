<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

trait ContentManager
{
    /**
     *  Sets <?php open tag for source code
     */
    protected function setTag()
    {
        $this->sourceCode = RJApiGenerator::PHP_OPEN_TAG . PHP_EOL;
    }

    /**
     * @param string $postfix
     */
    protected function setNamespace(string $postfix)
    {
        $this->sourceCode .= RJApiGenerator::PHP_NAMESPACE . PhpEntitiesInterface::SPACE .
            $this->generator->modulesDir . RJApiGenerator::BACKSLASH . strtoupper($this->generator->version) .
            RJApiGenerator::BACKSLASH . $postfix . RJApiGenerator::SEMICOLON . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $path
     * @param bool $isTrait
     * @param bool $isLast
     */
    protected function setUse(string $path, bool $isTrait = false, bool $isLast = false)
    {
        $this->sourceCode .= (($isTrait === false) ? '' : PhpEntitiesInterface::TAB_PSR4) .
            RJApiGenerator::PHP_USE . PhpEntitiesInterface::SPACE . $path . RJApiGenerator::SEMICOLON .
            PHP_EOL . (($isLast === false) ? '' : PHP_EOL);
    }

    /**
     * @param string $name
     * @param null $extends
     */
    protected function startClass(string $name, $extends = null)
    {
        $this->sourceCode .= RJApiGenerator::PHP_CLASS . PhpEntitiesInterface::SPACE . $name
            . PhpEntitiesInterface::SPACE;
        if ($extends !== null) {
            $this->sourceCode .=
                RJApiGenerator::PHP_EXTENDS
                . PhpEntitiesInterface::SPACE . $extends . PhpEntitiesInterface::SPACE;
        }
        $this->sourceCode .= PHP_EOL . RJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= RJApiGenerator::CLOSE_BRACE . PHP_EOL;
    }

    /**
     * @param string $name
     * @param string $modifier
     * @param null $returnType
     * @param bool $static
     */
    protected function startMethod(string $name, string $modifier, $returnType = null, bool $static = false)
    {
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . $modifier . PhpEntitiesInterface::SPACE .
            (($static !== false) ? PhpEntitiesInterface::PHP_STATIC . PhpEntitiesInterface::SPACE : '') .
            RJApiGenerator::PHP_FUNCTION . PhpEntitiesInterface::SPACE .
            $name . RJApiGenerator::OPEN_PARENTHESES . RJApiGenerator::CLOSE_PARENTHESES .
            (($returnType === null) ? '' : RJApiGenerator::COLON . PhpEntitiesInterface::SPACE . $returnType) .
            PhpEntitiesInterface::SPACE
            . RJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    /**
     * @param string $value
     * @param bool $isString
     */
    protected function methodReturn(string $value, $isString = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
            PhpEntitiesInterface::PHP_RETURN . PhpEntitiesInterface::SPACE . (($isString === false) ? $value :
                PhpEntitiesInterface::DOUBLE_QUOTES . $value . PhpEntitiesInterface::DOUBLE_QUOTES) . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::CLOSE_BRACE . PHP_EOL . PHP_EOL;
    }

    protected function startArray()
    {
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
            RJApiGenerator::PHP_RETURN . PhpEntitiesInterface::SPACE .
            RJApiGenerator::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4
            . RJApiGenerator::CLOSE_BRACKET . RJApiGenerator::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $prop
     * @param string $modifier
     * @param string $value
     * @param bool $isString
     */
    protected function createProperty(string $prop, string $modifier, $value = RJApiGenerator::PHP_TYPES_NULL, bool $isString = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . $modifier . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOLLAR_SIGN . $prop
            . PhpEntitiesInterface::SPACE . RJApiGenerator::EQUALS . PhpEntitiesInterface::SPACE
            . (($isString === false) ? $value : PhpEntitiesInterface::DOUBLE_QUOTES . $value . PhpEntitiesInterface::DOUBLE_QUOTES)
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $comment
     */
    protected function setComment(string $comment)
    {
        $this->sourceCode .= PhpEntitiesInterface::COMMENT
            . PhpEntitiesInterface::SPACE . $comment . PHP_EOL;
    }

    /**
     * @param int $amount
     */
    protected function setTabs(int $amount = 1)
    {
        for ($i = $amount; $i > 0; --$i) {
            $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4;
        }
    }

    /**
     * @param array $attrVal
     */
    public function setDescription(array $attrVal)
    {
        foreach ($attrVal as $k => $v) {
            if ($k === RamlInterface::RAML_KEY_DESCRIPTION) {
                $this->setTabs(3);
                $this->setComment($v);
            }
        }
    }
}