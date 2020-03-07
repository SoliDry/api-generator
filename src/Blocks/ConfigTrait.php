<?php

namespace SoliDry\Blocks;

use SoliDry\Types\ConfigInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class ConfigTrait
 *
 * @package SoliDry\Blocks
 * @property string sourceCode
 */
trait ConfigTrait
{
    public $openedBrackets = [];

    /**
     *  Opens config's root element
     */
    private function openRoot(): void
    {
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    /**
     *  Closes config's root element
     */
    private function closeRoot(): void
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON;
    }

    /**
     * Sets the default value of the $param name
     *
     * @param string $param
     * @param mixed $defaultValue
     */
    private function setParamDefault(string $param, $defaultValue): void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . $param .
            PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . ((bool)$defaultValue === true ? PhpInterface::PHP_TYPES_BOOL_TRUE : $defaultValue) .
            PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * Sets any config related param
     * @param string $param
     * @param string $type
     * @param string|bool $value
     * @param int $tabs
     */
    private function setParam(string $param, string $type, string $value, int $tabs = 1): void
    {
        // todo: this is ugly and stupid, because of Types misconception in RAML, PHP and YAML parse
        if ($type === ApiInterface::RAML_TYPE_BOOLEAN) {
            if ($value === PhpInterface::PHP_TYPES_BOOL_TRUE || (int)$value === 1) { // Yaml::parse converts true to 1, false to 0
                $value = PhpInterface::PHP_TYPES_BOOL_TRUE;
            } else {
                $value = PhpInterface::PHP_TYPES_BOOL_FALSE;
            }
        } else if ($type !== ApiInterface::RAML_TYPE_NUMBER) {
            if ($type === ApiInterface::RAML_TYPE_STRING) {
                $value = PhpInterface::QUOTES . $value . PhpInterface::QUOTES;
            } else {
                settype($value, ApiInterface::RAML_TO_PHP_TYPES[$type]);
            }
        }
        $this->setTabs($tabs);
        $this->sourceCode .= PhpInterface::QUOTES . $param . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . $value . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $amount
     *
     * @return mixed
     */
    abstract protected function setTabs(int $amount = 1): void;

    /**
     * Opens finite state machine
     *
     * @param string $entity
     * @param string $field
     */
    private function openFsm(string $entity, string $field): void
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, ApiInterface::RAML_TYPE_BOOLEAN, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
        $this->openEntity(ConfigInterface::STATES, 4);
    }

    private function openCache(string $entity): void
    {
        $this->openEntity(strtolower($entity), 2);
        $this->setParam(ConfigInterface::ENABLED,  ApiInterface::RAML_TYPE_BOOLEAN,PhpInterface::PHP_TYPES_BOOL_TRUE, 3);
    }

    /**
     * Opens finite state machine
     *
     * @param string $entity
     * @param string $field
     */
    private function openSc(string $entity, string $field): void
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, ApiInterface::RAML_TYPE_BOOLEAN, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
    }



    /**
     * @param string $entity
     * @param string $field
     */
    private function openBitMask(string $entity, string $field): void
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, ApiInterface::RAML_TYPE_BOOLEAN, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
        $this->openEntity(ConfigInterface::FLAGS, 4);
    }

    /**
     * Opens config file entity
     * @param string $entity
     * @param int $tabs
     */
    private function openEntity(string $entity, int $tabs = 1): void
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::QUOTES . $entity
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
        $this->openedBrackets[] = $tabs;
    }

    /**
     * Closes any configuration entity
     *
     * @param int $tabs
     * @param bool $isSingle
     */
    public function closeEntity(int $tabs = 1, $isSingle = false): void
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA . PHP_EOL;
        if ($isSingle === true) {
            unset($this->openedBrackets[count($this->openedBrackets) - 1]);
            $this->openedBrackets = array_values($this->openedBrackets);
        }
    }

    /**
     *  Closes entities by reversing array of prev opened
     */
    private function closeEntities(): void
    {
        $this->openedBrackets = array_reverse($this->openedBrackets);
        foreach ($this->openedBrackets as $k => $tabs) {
            $this->closeEntity($tabs);
            unset($this->openedBrackets[$k]);
        }
    }
}