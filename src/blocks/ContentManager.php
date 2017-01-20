<?php

namespace rjapi\blocks;

use rjapi\helpers\MethodOptions;
use rjapi\RJApiGenerator;
use rjapi\types\PhpEntitiesInterface;
use rjapi\types\RamlInterface;

trait ContentManager
{
    /**
     *  Sets <?php open tag for source code
     */
    protected function setTag()
    {
        $this->sourceCode = PhpEntitiesInterface::PHP_OPEN_TAG . PHP_EOL;
    }

    /**
     * @param string $postfix
     */
    protected function setNamespace(string $postfix)
    {
        $this->sourceCode .= PhpEntitiesInterface::PHP_NAMESPACE . PhpEntitiesInterface::SPACE .
            $this->generator->modulesDir . PhpEntitiesInterface::BACKSLASH . strtoupper($this->generator->version) .
            PhpEntitiesInterface::BACKSLASH . $postfix . PhpEntitiesInterface::SEMICOLON . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $path
     * @param bool $isTrait
     * @param bool $isLast
     */
    protected function setUse(string $path, bool $isTrait = false, bool $isLast = false)
    {
        $this->sourceCode .= (($isTrait === false) ? '' : PhpEntitiesInterface::TAB_PSR4) .
            PhpEntitiesInterface::PHP_USE . PhpEntitiesInterface::SPACE . $path . PhpEntitiesInterface::SEMICOLON .
            PHP_EOL . (($isLast === false) ? '' : PHP_EOL);
    }

    /**
     * @param string $name
     * @param null $extends
     */
    protected function startClass(string $name, $extends = null)
    {
        $this->sourceCode .= PhpEntitiesInterface::PHP_CLASS . PhpEntitiesInterface::SPACE . $name
            . PhpEntitiesInterface::SPACE;
        if ($extends !== null) {
            $this->sourceCode .=
                PhpEntitiesInterface::PHP_EXTENDS
                . PhpEntitiesInterface::SPACE . $extends . PhpEntitiesInterface::SPACE;
        }
        $this->sourceCode .= PHP_EOL . PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= PhpEntitiesInterface::CLOSE_BRACE . PHP_EOL;
    }

    /**
     * @param MethodOptions $methodOptions
     */
    protected function startMethod(MethodOptions $methodOptions)
    {
        // get params
        $params = $this->getMethodParams($methodOptions->getParams());
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . $methodOptions->getModifier() . PhpEntitiesInterface::SPACE .
            (($methodOptions->isStatic() !== false) ? PhpEntitiesInterface::PHP_STATIC . PhpEntitiesInterface::SPACE : '') .
            PhpEntitiesInterface::PHP_FUNCTION . PhpEntitiesInterface::SPACE .
            $methodOptions->getName()
            . PhpEntitiesInterface::OPEN_PARENTHESES . $params . RJApiGenerator::CLOSE_PARENTHESES .
            (($methodOptions->getReturnType() === null) ? '' : PhpEntitiesInterface::COLON . PhpEntitiesInterface::SPACE . $methodOptions->getReturnType()) .
            PhpEntitiesInterface::SPACE
            . PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    /**
     * @param string $value
     * @param bool $isString
     */
    protected function setMethodReturn(string $value, $isString = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
            PhpEntitiesInterface::PHP_RETURN . PhpEntitiesInterface::SPACE . (($isString === false) ? $value :
                PhpEntitiesInterface::DOUBLE_QUOTES . $value . PhpEntitiesInterface::DOUBLE_QUOTES) . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::CLOSE_BRACE . PHP_EOL . PHP_EOL;
    }

    protected function startArray()
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpEntitiesInterface::PHP_RETURN . PhpEntitiesInterface::SPACE .
            PhpEntitiesInterface::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4
            . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
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
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::EQUALS . PhpEntitiesInterface::SPACE
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

    /**
     * @param array $params
     * @return string
     */
    private function getMethodParams(array $params)
    {
        $paramsStr = '';
        $cnt = count($params);
        foreach($params as $type => $name)
        {
            if(is_int($type))
            {// not typed
                $paramsStr .= $name;
            }
            else
            {// typed
                $paramsStr .= $type . PhpEntitiesInterface::SPACE . $name;
            }
            if($cnt > 0)
            {
                $paramsStr .= PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE;
            }
        }
        return $paramsStr;
    }
}