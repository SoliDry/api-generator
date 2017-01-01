<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20/12/2016
 * Time: 17:03
 */

namespace rjapi\blocks;

trait RoutesTrait
{
    public function openGroup(string $version)
    {
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . RoutesInterface::METHOD_GROUP . PhpEntitiesInterface::OPEN_PARENTHESES
                             . PhpEntitiesInterface::OPEN_BRACKET
                             . PhpEntitiesInterface::QUOTES . DefaultInterface::PREFIX_KEY .
                             PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW .
                             PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES . $version . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE .
                             PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::PHP_NAMESPACE . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOUBLE_ARROW
                             . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES .
                             DirsInterface::MODULES_DIR .
                             PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH .
                             strtoupper($this->generator->version)
                             . PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH .
                             DirsInterface::HTTP_DIR
                             . PhpEntitiesInterface::BACKSLASH . PhpEntitiesInterface::BACKSLASH .
                             DirsInterface::CONTROLLERS_DIR . PhpEntitiesInterface::QUOTES
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

    public function setRoute($method, $objectName, $uri, $withId = false, $withRelation = false)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . RoutesInterface::CLASS_ROUTE .
                             PhpEntitiesInterface::DOUBLE_COLON
                             . $method . PhpEntitiesInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::SLASH
                             . strtolower($objectName) . (($withId === true) ?
                PhpEntitiesInterface::SLASH . PhpEntitiesInterface::OPEN_BRACE
                . RamlInterface::RAML_ID . PhpEntitiesInterface::CLOSE_BRACE : '') .
                             (($withRelation === true) ?
                                 PhpEntitiesInterface::SLASH . RamlInterface::RAML_RELATIONSHIPS
                                 . PhpEntitiesInterface::SLASH . PhpEntitiesInterface::OPEN_BRACE
                                 . ModelsInterface::MODEL_METHOD_RELATIONS . PhpEntitiesInterface::CLOSE_BRACE : '')
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA .
                             PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES .
                             $objectName . DefaultInterface::CONTROLLER_POSTFIX
                             . PhpEntitiesInterface::AT . $uri
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES .
                             PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }
}