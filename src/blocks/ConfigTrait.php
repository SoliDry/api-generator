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

    private function closeParams()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA . PHP_EOL;
    }
}