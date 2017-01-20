<?php
namespace rjapi\blocks;
use rjapi\types\ConfigInterface;
use rjapi\types\PhpEntitiesInterface;

/**
 * Class ConfigTrait
 * @package rjapi\blocks
 */
trait ConfigTrait
{
    private function openRoot()
    {
        $this->sourceCode .= PhpEntitiesInterface::PHP_RETURN . PhpEntitiesInterface::SPACE
            . PhpEntitiesInterface::OPEN_BRACKET . PHP_EOL;
    }

    private function closeRoot()
    {
        $this->sourceCode .= PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::SEMICOLON;
    }

    private function openParams()
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::QUOTES . ConfigInterface::QUERY_PARAMS
            . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::DOUBLE_ARROW . PhpEntitiesInterface::SPACE
            . PhpEntitiesInterface::OPEN_BRACKET . PHP_EOL;
    }

    private function closeParams()
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::COMMA . PHP_EOL;
    }
}