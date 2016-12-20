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
    public function setRoute($method, $objectName, $uri, $withId = false)
    {
        $this->sourceCode .= RoutesInterface::CLASS_ROUTE . PhpEntitiesInterface::DOUBLE_COLON
                             . $method . PhpEntitiesInterface::OPEN_PARENTHESES;

        $this->sourceCode .= PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::SLASH . strtolower($objectName)
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::QUOTES .
                             $this->generator->objectName . DefaultInterface::CONTROLLER_POSTFIX
                             . PhpEntitiesInterface::AT . HTTPMethodsInterface::URI_METHOD_INDEX
                             . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES .
                             PhpEntitiesInterface::SEMICOLON;
    }
}