<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

trait ContentManager
{
    // default storage variable
    private $code = 'sourceCode';

    /**
     * Sets any custom storage variable
     * @param string $code
     */
    protected function setCode(string $code)
    {
        $this->code = $code;
    }

    protected function setTag()
    {
        $this->{$this->code} = RJApiGenerator::PHP_OPEN_TAG . PHP_EOL;
    }

    protected function setNamespace($postfix)
    {
        $this->{$this->code} .= RJApiGenerator::PHP_NAMESPACE . ' ' .
            $this->generator->modulesDir . RJApiGenerator::BACKSLASH . strtoupper($this->generator->version) .
            RJApiGenerator::BACKSLASH . $postfix . RJApiGenerator::SEMICOLON . PHP_EOL . PHP_EOL;
    }

    protected function setUse($path, $isTrait = false, $isLast = false)
    {
        $this->{$this->code} .= (($isTrait === false) ? '' : PhpEntitiesInterface::TAB_PSR4) .
            RJApiGenerator::PHP_USE . ' ' . $path . RJApiGenerator::SEMICOLON .
            PHP_EOL . (($isLast === false) ? '' : PHP_EOL);
    }

    protected function startClass($name, $extends = null)
    {
        $this->{$this->code} .= RJApiGenerator::PHP_CLASS . ' ' . $name . ' ';
        if ($extends !== null) {
            $this->{$this->code} .=
                RJApiGenerator::PHP_EXTENDS
                . ' ' . $extends . ' ';
        }
        $this->{$this->code} .= PHP_EOL . RJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->{$this->code} .= PHP_EOL . RJApiGenerator::CLOSE_BRACE . PHP_EOL;
    }

    protected function startMethod($name, $modifier, $returnType = null, $static = false)
    {
        $this->{$this->code} .= RJApiGenerator::TAB_PSR4 . $modifier . PhpEntitiesInterface::SPACE .
            (($static !== false) ? PhpEntitiesInterface::PHP_STATIC . PhpEntitiesInterface::SPACE : '') .
            RJApiGenerator::PHP_FUNCTION . ' ' .
            $name . RJApiGenerator::OPEN_PARENTHESES . RJApiGenerator::CLOSE_PARENTHESES .
            (($returnType === null) ? '' : RJApiGenerator::COLON . PhpEntitiesInterface::SPACE . $returnType) .
            PhpEntitiesInterface::SPACE
            . RJApiGenerator::OPEN_BRACE . PHP_EOL;
    }

    protected function methodReturn($value, $isString = false)
    {
        $this->{$this->code} .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
            PhpEntitiesInterface::PHP_RETURN . ' ' . (($isString === false) ? $value :
                '"' . $value . '"') . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->{$this->code} .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::CLOSE_BRACE . PHP_EOL . PHP_EOL;
    }

    protected function startArray()
    {
        $this->{$this->code} .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 .
            RJApiGenerator::PHP_RETURN . ' ' .
            RJApiGenerator::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->{$this->code} .= PHP_EOL . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4
            . RJApiGenerator::CLOSE_BRACKET . RJApiGenerator::SEMICOLON . PHP_EOL;
    }

    protected function createProperty($prop, $modifier, $value = RJApiGenerator::PHP_TYPES_NULL, $isString = false)
    {
        $this->{$this->code} .= RJApiGenerator::TAB_PSR4 . $modifier . ' ' . RJApiGenerator::DOLLAR_SIGN . $prop
            . RJApiGenerator::SPACE . RJApiGenerator::EQUALS . RJApiGenerator::SPACE
            . (($isString === false) ? $value : '"' . $value . '"') . RJApiGenerator::SEMICOLON .
            PHP_EOL;
    }

    protected function setComment($comment)
    {
        $this->{$this->code} .= PhpEntitiesInterface::COMMENT
            . PhpEntitiesInterface::SPACE . $comment . PHP_EOL;
    }

    protected function setTabs(int $amount = 1)
    {
        for ($i = $amount; $i > 0; --$i) {
            $this->{$this->code} .= PhpEntitiesInterface::TAB_PSR4;
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