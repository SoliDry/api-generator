<?php

namespace rjapi\blocks;

use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\ApiInterface;
use rjapi\types\RoutesInterface;

/**
 * Trait RoutesTrait
 * @package rjapi\blocks
 *
 * @property Routes generator
 */
trait RoutesTrait
{
    /**
     * @param string $version
     */
    public function openGroup(string $version) : void
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

    public function closeGroup() : void
    {
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Sets route to sourceCode
     *
     * @param string $method
     * @param string $uri
     * @param string $endPoint
     */
    public function setRoute(string $method, string $uri, string $endPoint) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . RoutesInterface::CLASS_ROUTE .
            PhpInterface::DOUBLE_COLON
            . $method . PhpInterface::OPEN_PARENTHESES . $uri . PhpInterface::COMMA .
            PhpInterface::SPACE . $endPoint . PhpInterface::CLOSE_PARENTHESES .
            PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * @return string
     */
    private function composeRelationsUri() : string
    {
        return $this->composeRelationsBaseUri() . PhpInterface::SLASH . ApiInterface::RAML_RELATIONSHIPS
            . PhpInterface::SLASH . PhpInterface::OPEN_BRACE
            . ModelsInterface::MODEL_METHOD_RELATIONS . PhpInterface::CLOSE_BRACE . PhpInterface::QUOTES;
    }

    /**
     * @return string
     */
    private function composeIdUri() : string
    {
        return $this->composeBaseUri() . PhpInterface::SLASH . PhpInterface::OPEN_BRACE
            . ApiInterface::RAML_ID . PhpInterface::CLOSE_BRACE . PhpInterface::QUOTES;
    }

    /**
     * Creates bulk requests uri
     *
     * @return string
     */
    private function composeBulkUri() : string
    {
        return $this->composeBaseUri() . PhpInterface::SLASH . JSONApiInterface::EXT_BULK . PhpInterface::QUOTES;
    }

    /**
     * Creates standard object uri
     *
     * @return string
     */
    private function composeObjectUri() : string
    {
        return $this->composeBaseUri() . PhpInterface::QUOTES;
    }

    /**
     * Creates base uri
     *
     * @return string
     */
    private function composeBaseUri() : string
    {
        return PhpInterface::QUOTES . PhpInterface::SLASH . strtolower($this->generator->objectName);
    }

    /**
     * Creates base uri for relations
     * @return string
     */
    private function composeRelationsBaseUri() : string
    {
        return PhpInterface::QUOTES . PhpInterface::SLASH . strtolower($this->generator->objectName) .
            PhpInterface::SLASH . PhpInterface::OPEN_BRACE
            . ApiInterface::RAML_ID . PhpInterface::CLOSE_BRACE;
    }

    /**
     * Creates controller@method end-point
     *
     * @param string $uriMethod
     * @return string
     */
    private function composeEndPoint(string $uriMethod) : string
    {
        return PhpInterface::QUOTES .
            Classes::getClassName($this->generator->objectName) . DefaultInterface::CONTROLLER_POSTFIX
            . PhpInterface::AT . $uriMethod
            . PhpInterface::QUOTES;
    }
}