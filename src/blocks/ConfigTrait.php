<?php
namespace rjapi\blocks;

use rjapi\types\ConfigInterface;
use rjapi\types\PhpInterface;

/**
 * Class ConfigTrait
 *
 * @package rjapi\blocks
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
     * @param mixed  $defaultValue
     */
    private function setParamDefault(string $param, $defaultValue): void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . $param .
                             PhpInterface::QUOTES
                             . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . ((bool) $defaultValue === true ? PhpInterface::PHP_TYPES_BOOL_TRUE : $defaultValue) .
                             PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * Sets any config related param
     * @param string $param
     * @param string|bool $value
     * @param int $tabs
     */
    private function setParam(string $param, string $value, int $tabs = 1): void
    {
        if (is_numeric($value) === false
            && is_bool($value) === false
            && in_array($value, [PhpInterface::PHP_TYPES_BOOL_TRUE, PhpInterface::PHP_TYPES_BOOL_FALSE]) === false
        ) {
            $value = PhpInterface::QUOTES . $value . PhpInterface::QUOTES;
        }
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::QUOTES . $param . PhpInterface::QUOTES
                             . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . $value . PhpInterface::COMMA . PHP_EOL;
    }

    /**
     * @param int $amount
     *
     * @return mixed
     */
    abstract protected function setTabs(int $amount = 1);

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
        $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
        $this->openEntity(ConfigInterface::STATES, 4);
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
        $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
    }

    /**
     * @param string $entity
     * @param string $field
     */
    private function openBitMask(string $entity, string $field): void
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
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
        array_push($this->openedBrackets, $tabs);
    }

    /**
     * Closes any configuration entity
     *
     * @param int $tabs
     */
    private function closeEntity(int $tabs = 1): void
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA . PHP_EOL;
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