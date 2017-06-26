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
    private function openRoot()
    {
        $this->sourceCode .= PhpInterface::PHP_RETURN . PhpInterface::SPACE
                             . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    private function closeRoot()
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACKET . PhpInterface::SEMICOLON;
    }

    /**
     * Sets the default value of the $param name
     *
     * @param string $param
     * @param mixed  $defaultValue
     */
    private function setParamDefault(string $param, $defaultValue)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . $param .
                             PhpInterface::QUOTES
                             . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . ((bool) $defaultValue === true ? PhpInterface::PHP_TYPES_BOOL_TRUE : $defaultValue) .
                             PhpInterface::COMMA . PHP_EOL;
    }

    private function setParam(string $param, string $value, int $tabs = 1)
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
    protected abstract function setTabs(int $amount = 1);

    /**
     * Opens finite state machine
     *
     * @param string $entity
     * @param string $field
     */
    private function openFsm(string $entity, string $field)
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
    private function openSc(string $entity, string $field)
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
    }

    /**
     * @param string $entity
     * @param string $field
     */
    private function openBitMask(string $entity, string $field)
    {
        $this->openEntity(strtolower($entity), 2);
        $this->openEntity(strtolower($field), 3);
        $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
        $this->setParam(ConfigInterface::HIDE_MASK, PhpInterface::PHP_TYPES_BOOL_TRUE, 4);
        $this->openEntity(ConfigInterface::FLAGS, 4);
    }

    // todo: program closeEntities after openEntities array filling
    private function closeBitMask()
    {
        $this->closeEntity(4);
        $this->closeEntity(3);
        $this->closeEntity(2);
    }

    private function closeSc()
    {
        $this->closeEntity(3);
        $this->closeEntity(2);
    }

    private function closeFsm()
    {
        $this->closeEntity(4);
        $this->closeEntity(3);
        $this->closeEntity(2);
    }

    private function openEntity(string $entity, int $tabs = 1)
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::QUOTES . $entity
                             . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
                             . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    /**
     * Closes any configuration entity
     *
     * @param int $tabs
     */
    private function closeEntity(int $tabs = 1)
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA . PHP_EOL;
    }
}