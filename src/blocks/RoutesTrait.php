<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20/12/2016
 * Time: 17:03
 */

namespace rjapi\blocks;

use rjapi\helpers\Config;

trait RoutesTrait
{
    public function openGroup(string $version)
    {
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
            . RoutesInterface::METHOD_GROUP . PhpEntitiesInterface::OPEN_PARENTHESES
            . PhpEntitiesInterface::OPEN_BRACKET
            . PhpEntitiesInterface::DOUBLE_QUOTES . DefaultInterface::PREFIX_KEY . PhpEntitiesInterface::DOUBLE_QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW . PhpEntitiesInterface::SPACE
            . PhpEntitiesInterface::DOUBLE_QUOTES . $version . PhpEntitiesInterface::DOUBLE_QUOTES
            . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_QUOTES
            . PhpEntitiesInterface::PHP_NAMESPACE . PhpEntitiesInterface::DOUBLE_QUOTES
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_QUOTES. DirsInterface::MODULES_DIR .
            PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH . strtoupper($this->generator->version)
            . PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR
            . PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH . DirsInterface::CONTROLLERS_DIR . PhpEntitiesInterface::DOUBLE_QUOTES
            . PhpEntitiesInterface::CLOSE_BRACKET . PhpEntitiesInterface::COMMA
            . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::PHP_FUNCTION .
            PhpEntitiesInterface::OPEN_PARENTHESES
            . PhpEntitiesInterface::CLOSE_PARENTHESES . PHP_EOL;

        $this->sourceCode .= PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeGroup()
    {
        $this->sourceCode .= PhpEntitiesInterface::CLOSE_BRACE . PhpEntitiesInterface::CLOSE_PARENTHESES
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    public function setRoute($method, $objectName, $uri, $withId = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
            . $method . PhpEntitiesInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpEntitiesInterface::DOUBLE_QUOTES . PhpEntitiesInterface::SLASH
            . strtolower($objectName) . (($withId === true) ?
                PhpEntitiesInterface::SLASH . PhpEntitiesInterface::OPEN_BRACE
                . RamlInterface::RAML_ID . PhpEntitiesInterface::CLOSE_BRACE : '')
            . PhpEntitiesInterface::DOUBLE_QUOTES . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE
            . PhpEntitiesInterface::DOUBLE_QUOTES .
            $objectName . DefaultInterface::CONTROLLER_POSTFIX
            . PhpEntitiesInterface::AT . $uri
            . PhpEntitiesInterface::DOUBLE_QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES .
            PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }
}