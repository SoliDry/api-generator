<?php
namespace rjapi\blocks;
use rjapi\types\ConfigInterface;
use rjapi\types\PhpInterface;

/**
 * Class ConfigTrait
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

    private function openParams()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . ConfigInterface::QUERY_PARAMS
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    private function openJwt()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . ConfigInterface::JWT
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }
    
    private function openTrees()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . ConfigInterface::TREES
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;        
    }

    /**
     * Sets the default value of the $param name
     * @param string $param
     * @param mixed $defaultValue
     */
    private function setParamDefault(string $param, $defaultValue)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . $param . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . ((bool)$defaultValue === true ? PhpInterface::PHP_TYPES_BOOL_TRUE : $defaultValue) . PhpInterface::COMMA . PHP_EOL;
    }
    
    private function setParam(string $param, string $value, int $tabs = 1)
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::QUOTES . $param . PhpInterface::QUOTES
            . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . $value . PhpInterface::COMMA . PHP_EOL;
    }

    private function openStateMachine()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . ConfigInterface::STATE_MACHINE
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    private function openSpellCheck()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::QUOTES . ConfigInterface::SPELL_CHECK
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }    
    
    /**
     * Opens finite state machine
     * @param string $entity
     * @param string $field
     */
    private function openFsm(string $entity, string $field)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . strtolower($entity)
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::QUOTES . strtolower($field)
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
        $this->setTabs(4);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::ENABLED
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::PHP_TYPES_BOOL_TRUE . PhpInterface::COMMA
            . PHP_EOL;
        $this->setTabs(4);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::STATES
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
    }

    /**
     * Opens finite state machine
     * @param string $entity
     * @param string $field
     */
    private function openSc(string $entity, string $field)
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::QUOTES . strtolower($entity)
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::QUOTES . strtolower($field)
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::SPACE
            . PhpInterface::OPEN_BRACKET . PHP_EOL;
        $this->setTabs(4);
        $this->sourceCode .= PhpInterface::QUOTES . ConfigInterface::ENABLED
            . PhpInterface::QUOTES . PhpInterface::DOUBLE_ARROW . PhpInterface::PHP_TYPES_BOOL_TRUE . PhpInterface::COMMA
            . PHP_EOL;
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

    /**
     * Closes any configuration entity
     * @param int $tabs
     */
    private function closeEntity(int $tabs = 1)
    {
        $this->sourceCode .= $this->setTabs($tabs) . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA . PHP_EOL;
    }
}