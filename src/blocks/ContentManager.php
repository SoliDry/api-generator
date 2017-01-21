<?php

namespace rjapi\blocks;

use rjapi\helpers\MethodOptions;
use rjapi\RJApiGenerator;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class ContentManager
 * @package rjapi\blocks
 * @property RJApiGenerator generator
 * @property string sourceCode
 */
trait ContentManager
{
    /**
     *  Sets <?php open tag for source code
     */
    protected function setTag()
    {
        $this->sourceCode = PhpInterface::PHP_OPEN_TAG . PHP_EOL;
    }

    /**
     * @param string $postfix
     */
    protected function setNamespace(string $postfix)
    {
        $this->sourceCode .= PhpInterface::PHP_NAMESPACE . PhpInterface::SPACE .
            $this->generator->modulesDir . PhpInterface::BACKSLASH . strtoupper($this->generator->version) .
            PhpInterface::BACKSLASH . $postfix . PhpInterface::SEMICOLON . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $path
     * @param bool $isTrait
     * @param bool $isLast
     */
    protected function setUse(string $path, bool $isTrait = false, bool $isLast = false)
    {
        $this->sourceCode .= (($isTrait === false) ? '' : PhpInterface::TAB_PSR4) .
            PhpInterface::PHP_USE . PhpInterface::SPACE . $path . PhpInterface::SEMICOLON .
            PHP_EOL . (($isLast === false) ? '' : PHP_EOL);
    }

    /**
     * @param string $name
     * @param null $extends
     */
    protected function startClass(string $name, $extends = null)
    {
        $this->sourceCode .= PhpInterface::PHP_CLASS . PhpInterface::SPACE . $name
            . PhpInterface::SPACE;
        if($extends !== null)
        {
            $this->sourceCode .=
                PhpInterface::PHP_EXTENDS
                . PhpInterface::SPACE . $extends . PhpInterface::SPACE;
        }
        $this->sourceCode .= PHP_EOL . PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    protected function endClass()
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PHP_EOL;
    }

    /**
     * @param MethodOptions $methodOptions
     */
    protected function startMethod(MethodOptions $methodOptions)
    {
        // get params
        $params = $this->getMethodParams($methodOptions->getParams());
        $this->sourceCode .= PhpInterface::TAB_PSR4 . $methodOptions->getModifier() . PhpInterface::SPACE .
            (($methodOptions->isStatic() !== false) ? PhpInterface::PHP_STATIC . PhpInterface::SPACE : '') .
            PhpInterface::PHP_FUNCTION . PhpInterface::SPACE .
            $methodOptions->getName()
            . PhpInterface::OPEN_PARENTHESES . $params . PhpInterface::CLOSE_PARENTHESES .
            ((empty($methodOptions->getReturnType())) ? '' : PhpInterface::COLON . PhpInterface::SPACE . $methodOptions->getReturnType()) .
            PhpInterface::SPACE . PHP_EOL . PhpInterface::TAB_PSR4
            . PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    /**
     * @param string $value
     * @param bool $isString
     */
    protected function setMethodReturn(string $value, $isString = false)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE . (($isString === false) ? $value :
                PhpInterface::DOUBLE_QUOTES . $value . PhpInterface::DOUBLE_QUOTES) . PhpInterface::SEMICOLON . PHP_EOL;
    }

    protected function endMethod()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACE . PHP_EOL . PHP_EOL;
    }

    protected function startArray()
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE .
            PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    protected function endArray()
    {
        $this->sourceCode .= PHP_EOL . PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4
            . PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $prop
     * @param string $modifier
     * @param string $value
     * @param bool $isString
     */
    protected function createProperty(string $prop, string $modifier, $value = RJApiGenerator::PHP_TYPES_NULL, bool $isString = false)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . $modifier . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN . $prop
            . PhpInterface::SPACE . PhpInterface::EQUALS . PhpInterface::SPACE
            . (($isString === false) ? $value : PhpInterface::DOUBLE_QUOTES . $value . PhpInterface::DOUBLE_QUOTES)
            . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $comment
     */
    protected function setComment(string $comment)
    {
        $this->sourceCode .= PhpInterface::COMMENT
            . PhpInterface::SPACE . $comment . PHP_EOL;
    }

    /**
     * @param int $amount
     */
    protected function setTabs(int $amount = 1)
    {
        for($i = $amount; $i > 0; --$i)
        {
            $this->sourceCode .= PhpInterface::TAB_PSR4;
        }
    }

    /**
     * @param array $attrVal
     */
    public function setDescription(array $attrVal)
    {
        foreach($attrVal as $k => $v)
        {
            if($k === RamlInterface::RAML_KEY_DESCRIPTION)
            {
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
            --$cnt;
            if(is_int($type))
            {// not typed
                $paramsStr .= PhpInterface::DOLLAR_SIGN . $name;
            }
            else
            {// typed
                $paramsStr .= $type . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN . $name;
            }
            if($cnt > 0)
            {
                $paramsStr .= PhpInterface::COMMA . PhpInterface::SPACE;
            }
        }

        return $paramsStr;
    }

    /**
     * @param string $str
     */
    public function setEchoString(string $str)
    {
        $this->sourceCode .= PhpInterface::ECHO . PhpInterface::SPACE . PhpInterface::QUOTES
            . $str . PhpInterface::QUOTES . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $attribute
     */
    public function openRule(string $attribute)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 .
            PhpInterface::TAB_PSR4
            . PhpInterface::DOUBLE_QUOTES . $attribute . PhpInterface::DOUBLE_QUOTES
            . PhpInterface::SPACE
            . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . PhpInterface::DOUBLE_QUOTES;;
    }

    public function closeRule()
    {
        $this->sourceCode .= PhpInterface::DOUBLE_QUOTES . PhpInterface::COMMA;
    }
}