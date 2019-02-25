<?php

namespace SoliDry\Blocks;

use SoliDry\Controllers\BaseCommand;
use SoliDry\Helpers\Console;
use SoliDry\Helpers\Json;
use SoliDry\Helpers\MethodOptions;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class ContentManager
 *
 * @package SoliDry\Blocks
 * @property BaseCommand generator
 * @property string sourceCode
 */
trait ContentManager
{
    /**
     *  Sets <?php open tag for source code
     */
    protected function setTag() : void
    {
        $this->sourceCode = PhpInterface::PHP_OPEN_TAG . PHP_EOL;
    }

    /**
     * @param string $postfix
     */
    protected function setNamespace(string $postfix) : void
    {
        if ($this->generator->version) {
            $this->sourceCode .= PhpInterface::PHP_NAMESPACE . PhpInterface::SPACE .
                ApiInterface::DEFAULT_VERSION . PhpInterface::BACKSLASH . $postfix .
                PhpInterface::SEMICOLON . PHP_EOL . PHP_EOL;
        } else {
            $this->sourceCode .= PhpInterface::PHP_NAMESPACE . PhpInterface::SPACE .
                $this->generator->modulesDir . PhpInterface::BACKSLASH .
                strtoupper($this->generator->version) .
                PhpInterface::BACKSLASH . $postfix . PhpInterface::SEMICOLON . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * @param string $path
     * @param bool $isTrait
     * @param bool $isLast
     */
    protected function setUse(string $path, bool $isTrait = false, bool $isLast = false) : void
    {
        $this->sourceCode .= (($isTrait === false) ? '' : PhpInterface::TAB_PSR4) .
            PhpInterface::PHP_USE . PhpInterface::SPACE . $path . PhpInterface::SEMICOLON .
            PHP_EOL . (($isLast === false) ? '' : PHP_EOL);
    }

    /**
     * @param string $name
     * @param null $extends
     */
    protected function startClass(string $name, $extends = null) : void
    {
        $this->sourceCode .= PhpInterface::PHP_CLASS . PhpInterface::SPACE . $name
            . PhpInterface::SPACE;
        if ($extends !== null) {
            $this->sourceCode .=
                PhpInterface::PHP_EXTENDS
                . PhpInterface::SPACE . $extends . PhpInterface::SPACE;
        }
        $this->sourceCode .= PHP_EOL . PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    /**
     *  Ends class declaration
     */
    protected function endClass() : void
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PHP_EOL;
    }

    /**
     * @param MethodOptions $methodOptions
     */
    protected function startMethod(MethodOptions $methodOptions) : void
    {
        // get params
        $params           = $this->getMethodParams($methodOptions->getParams());
        $this->sourceCode .= PhpInterface::TAB_PSR4 . $methodOptions->getModifier() . PhpInterface::SPACE .
            (($methodOptions->isStatic() !== false) ? PhpInterface::PHP_STATIC . PhpInterface::SPACE :
                '') .
            PhpInterface::PHP_FUNCTION . PhpInterface::SPACE .
            $methodOptions->getName()
            . PhpInterface::OPEN_PARENTHESES . $params . PhpInterface::CLOSE_PARENTHESES .
            ((empty($methodOptions->getReturnType())) ? '' :
                PhpInterface::COLON . PhpInterface::SPACE . $methodOptions->getReturnType()) .
            PhpInterface::SPACE . PHP_EOL . PhpInterface::TAB_PSR4
            . PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    /**
     * Sets return stmt for any generated method
     *
     * @param string $value
     * @param bool $isString
     */
    protected function setMethodReturn(string $value, $isString = false) : void
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE . (($isString === false) ? $value :
                PhpInterface::DOUBLE_QUOTES . $value . PhpInterface::DOUBLE_QUOTES) . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Ends method declaration
     *
     * @param int $eolCnt
     */
    protected function endMethod(int $eolCnt = 2) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACE;
        for ($i = $eolCnt; $i > 0; --$i) {
            $this->sourceCode .= PHP_EOL;
        }
    }

    /**
     *  Starts an array declaration in string notation
     */
    protected function startArray() : void
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE .
            PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    /**
     *  Ends an array declaration after values had been set
     */
    protected function endArray() : void
    {
        $this->sourceCode .= PHP_EOL . PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4
            . PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Creates simple key=value map array property
     *
     * @param $key
     * @param $value
     */
    private function setArrayProperty($key, array $value) : void
    {
        $val              = $this->setArrayToString($value);
        $this->sourceCode .= $this->quoteParam($key)
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW
            . PhpInterface::SPACE . $val . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param string $prop
     * @param string $modifier
     * @param string $value
     * @param bool $isString
     */
    protected function createProperty(string $prop, string $modifier, $value = PhpInterface::PHP_TYPES_NULL, bool $isString = false) : void
    {
        if ($value === PhpInterface::PHP_TYPES_NULL) { // drop null assignments as they are already nullable by default
            $this->sourceCode .= PhpInterface::TAB_PSR4 . $modifier . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN .
                $prop . PhpInterface::SEMICOLON . PHP_EOL;
        } else {
            $this->sourceCode .= PhpInterface::TAB_PSR4 . $modifier . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN .
                $prop . PhpInterface::SPACE . PhpInterface::EQUALS . PhpInterface::SPACE
                . (($isString === false) ? $value : PhpInterface::QUOTES . $value . PhpInterface::QUOTES) . PhpInterface::SEMICOLON . PHP_EOL;
        }
    }

    /**
     * @param string $prop
     * @param string $modifier
     * @param array $value
     */
    protected function createPropertyArray(string $prop, string $modifier, array $value) : void
    {
        $val              = $this->setArrayToString($value);
        $this->sourceCode .= PhpInterface::TAB_PSR4 . $modifier . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN .
            $prop . PhpInterface::SPACE . PhpInterface::EQUALS . PhpInterface::SPACE . $val . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param array $value
     * @return string
     */
    private function setArrayToString(array $value) : string
    {
        $val = PhpInterface::OPEN_BRACKET;
        $val .= PhpInterface::QUOTES . implode(
                PhpInterface::QUOTES . PhpInterface::COMMA . PhpInterface::SPACE . PhpInterface::QUOTES, $value
            ) . PhpInterface::QUOTES;
        $val .= PhpInterface::CLOSE_BRACKET;
        return $val;
    }

    /**
     * @param string $comment
     * @param int $tabs
     */
    protected function setComment(string $comment, int $tabs = 1) : void
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::COMMENT
            . PhpInterface::SPACE . $comment . PHP_EOL;
    }

    /**
     * @param int $amount
     */
    protected function setTabs(int $amount = 1) : void
    {
        for ($i = $amount; $i > 0; --$i) {
            $this->sourceCode .= PhpInterface::TAB_PSR4;
        }
    }

    /**
     * @param array $attrVal
     */
    public function setDescription(array $attrVal) : void
    {
        foreach ($attrVal as $k => $v) {
            if ($k === ApiInterface::RAML_KEY_DESCRIPTION) {
                $this->setTabs(3);
                $this->setComment($v);
            }
        }
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getMethodParams(array $params) : string
    {
        $paramsStr = '';
        $cnt       = count($params);
        foreach ($params as $type => $name) {
            --$cnt;
            if (is_int($type)) {// not typed
                $paramsStr .= PhpInterface::DOLLAR_SIGN . $name;
            } else {// typed
                $paramsStr .= $type . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN . $name;
            }
            if ($cnt > 0) {
                $paramsStr .= PhpInterface::COMMA . PhpInterface::SPACE;
            }
        }

        return $paramsStr;
    }

    /**
     * @param array $params
     *
     * @param bool $arrayToJson
     * @return string
     */
    private function getMethodParamsToPass(array $params, $arrayToJson = true) : string
    {
        $paramsStr = '';
        $cnt       = count($params);
        foreach ($params as $value) {
            --$cnt;
            if (is_array($value)) {
                $paramsStr .= $arrayToJson ? $this->quoteParam(json_encode($value)) : var_export($value, true);
            } else {
                $paramsStr .= $this->quoteParam($value);
            }
            if ($cnt > 0) {
                $paramsStr .= PhpInterface::COMMA . PhpInterface::SPACE;
            }
        }

        return $paramsStr;
    }

    /**
     * @param string $str
     */
    public function setEchoString(string $str) : void
    {
        $this->sourceCode .= PhpInterface::ECHO . PhpInterface::SPACE . PhpInterface::QUOTES
            . $str . PhpInterface::QUOTES . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @param string $attribute
     */
    public function openRule(string $attribute) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 .
            PhpInterface::TAB_PSR4
            . PhpInterface::QUOTES . $attribute . PhpInterface::QUOTES
            . PhpInterface::SPACE
            . PhpInterface::DOUBLE_ARROW .
            PhpInterface::SPACE . PhpInterface::QUOTES;
    }

    /**
     *  Close rules in FormRequest
     */
    public function closeRule() : void
    {
        $this->sourceCode .= PhpInterface::QUOTES . PhpInterface::COMMA;
    }

    /**
     * @uses \SoliDry\Blocks\Controllers::setContent
     * @uses \SoliDry\Blocks\Config::setContent
     * @uses \SoliDry\Blocks\Migrations::setContent
     * @uses \SoliDry\Blocks\Entities::setContent
     * @uses \SoliDry\Blocks\FormRequest::setContent
     * @uses \SoliDry\Blocks\Tests::setContent
     *
     * Creates entities like *Controller, *FormRequest, BaseModel entities etc
     *
     * @param string $basePath
     * @param string $postFix
     */
    public function createEntity(string $basePath, string $postFix = '') : void
    {
        $this->setContent();
        $file      = $this->getEntityFile($basePath, $postFix);
        $isCreated = FileManager::createFile(
            $file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     * Gets Laravel <Entity> file
     *
     * @param string $basePath
     * @param string $postFix
     * @return string
     */
    public function getEntityFile(string $basePath, string $postFix = '') : string
    {
        $file = $basePath . DIRECTORY_SEPARATOR . $this->className;
        if ($postFix !== '') {
            $file .= $postFix;
        }
        $file .= PhpInterface::PHP_EXT;

        return $file;
    }

    /**
     * Creates entities like *Controller, *FormRequest, BaseModel entities etc
     *
     * @param string $basePath
     * @param string $postFix
     */
    public function recreateEntity(string $basePath, string $postFix = '') : void
    {
        $this->resetContent();
        $file      = $this->getEntityFile($basePath, $postFix);
        $isCreated = FileManager::createFile(
            $file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     * Gets array param as string to place in generated methods
     *
     * @param array $param
     *
     * @return string
     */
    private function getArrayParam(array $param) : string
    {
        return PhpInterface::OPEN_BRACKET . PhpInterface::QUOTES .
            implode(PhpInterface::QUOTES . PhpInterface::COMMA . PhpInterface::SPACE . PhpInterface::QUOTES, $param)
            . PhpInterface::QUOTES
            . PhpInterface::CLOSE_BRACKET;
    }

    /**
     * @param string $param
     *
     * @return string
     */
    public function quoteParam(string $param) : string
    {
        return PhpInterface::QUOTES . $param . PhpInterface::QUOTES;
    }

    /**
     * Sets the source starting code
     * @param string $entityFile
     */
    private function setBeforeProps(string $entityFile) : void
    {
        $this->resourceCode = file_get_contents($entityFile);
        $end                = mb_strpos($this->resourceCode, DefaultInterface::PROPS_START, null, PhpInterface::ENCODING_UTF8) - 3;
        $this->sourceCode   = mb_substr($this->resourceCode, 0, $end, PhpInterface::ENCODING_UTF8);
    }

    /**
     * Sets the source middle code
     * @param string $till
     */
    private function setAfterProps($till = null) : void
    {
        $start = $this->setTabs() . mb_strpos($this->resourceCode, DefaultInterface::PROPS_END, null, PhpInterface::ENCODING_UTF8) - 3;
        if ($till === null) {
            $this->sourceCode .= mb_substr($this->resourceCode, $start, null, PhpInterface::ENCODING_UTF8);
        } else {
            $end              = mb_strpos($this->resourceCode, $till, null, PhpInterface::ENCODING_UTF8) - 3;
            $this->sourceCode .= mb_substr($this->resourceCode, $start, $end - $start, PhpInterface::ENCODING_UTF8);
        }
    }

    /**
     *  Sets the source tail
     */
    private function setAfterMethods() : void
    {
        $start            = mb_strpos($this->resourceCode, DefaultInterface::METHOD_END, null, PhpInterface::ENCODING_UTF8) - 3;
        $this->sourceCode .= $this->setTabs() . mb_substr($this->resourceCode, $start, null, PhpInterface::ENCODING_UTF8);
    }

    /**
     *
     * @param string $object
     * @param string $method
     * @param array $params
     * @param bool $arrayToJson
     */
    private function methodCallOnObject(string $object, string $method, array $params = [], $arrayToJson = true) : void
    {
        $this->sourceCode .= $this->setTabs(2) . PhpInterface::DOLLAR_SIGN . $object
            . PhpInterface::ARROW . $method . PhpInterface::OPEN_PARENTHESES
            . $this->getMethodParamsToPass($params, $arrayToJson)
            . PhpInterface::CLOSE_PARENTHESES . PhpInterface::SEMICOLON . PHP_EOL;
    }
}