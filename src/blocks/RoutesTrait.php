<?php
namespace rjapi\blocks;

use rjapi\helpers\Classes;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
use rjapi\types\RoutesInterface;

trait RoutesTrait
{
    public function openGroup(string $version)
    {
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpInterface::DOUBLE_COLON
                             . RoutesInterface::METHOD_GROUP . PhpInterface::OPEN_PARENTHESES
                             . PhpInterface::OPEN_BRACKET
                             . PhpInterface::QUOTES . DefaultInterface::PREFIX_KEY .
                             PhpInterface::QUOTES
                             . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW .
                             PhpInterface::SPACE
                             . PhpInterface::QUOTES . $version . PhpInterface::QUOTES
                             . PhpInterface::COMMA . PhpInterface::SPACE .
                             PhpInterface::QUOTES
                             . PhpInterface::PHP_NAMESPACE . PhpInterface::QUOTES
                             . PhpInterface::SPACE . PhpInterface::DOUBLE_ARROW
                             . PhpInterface::SPACE . PhpInterface::QUOTES .
                             DirsInterface::MODULES_DIR .
                             PhpInterface::BACKSLASH . PhpInterface::BACKSLASH .
                             strtoupper($this->generator->version)
                             . PhpInterface::BACKSLASH . PhpInterface::BACKSLASH .
                             DirsInterface::HTTP_DIR
                             . PhpInterface::BACKSLASH . PhpInterface::BACKSLASH .
                             DirsInterface::CONTROLLERS_DIR . PhpInterface::QUOTES
                             . PhpInterface::CLOSE_BRACKET . PhpInterface::COMMA
                             . PhpInterface::SPACE . PhpInterface::PHP_FUNCTION .
                             PhpInterface::OPEN_PARENTHESES
                             . PhpInterface::CLOSE_PARENTHESES . PHP_EOL;

        $this->sourceCode .= PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeGroup()
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PhpInterface::CLOSE_PARENTHESES
                             . PhpInterface::SEMICOLON . PHP_EOL;
    }

    public function setRoute($method, $objectName, $uri, $withId = false, $withRelation = false)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . RoutesInterface::CLASS_ROUTE .
                             PhpInterface::DOUBLE_COLON
                             . $method . PhpInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpInterface::QUOTES . PhpInterface::SLASH
                             . strtolower($objectName) . (($withId === true) ?
                PhpInterface::SLASH . PhpInterface::OPEN_BRACE
                . RamlInterface::RAML_ID . PhpInterface::CLOSE_BRACE : '') .
                             (($withRelation === true) ?
                                 PhpInterface::SLASH . RamlInterface::RAML_RELATIONSHIPS
                                 . PhpInterface::SLASH . PhpInterface::OPEN_BRACE
                                 . ModelsInterface::MODEL_METHOD_RELATIONS . PhpInterface::CLOSE_BRACE : '')
                             . PhpInterface::QUOTES . PhpInterface::COMMA .
                             PhpInterface::SPACE
                             . PhpInterface::QUOTES .
                             Classes::getClassName($objectName) . DefaultInterface::CONTROLLER_POSTFIX
                             . PhpInterface::AT . $uri
                             . PhpInterface::QUOTES . PhpInterface::CLOSE_PARENTHESES .
                             PhpInterface::SEMICOLON . PHP_EOL;
    }
}