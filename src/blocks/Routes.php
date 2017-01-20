<?php

namespace rjapi\blocks;

use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;
use rjapi\types\PhpInterface;
use rjapi\types\RoutesInterface;

class Routes
{
    use ContentManager, RoutesTrait;
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';

    private $className = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setRoutes();
        $isCreated = false;
        $file      = FileManager::getModulePath($this->generator, true) .
                     RoutesInterface::ROUTES_FILE_NAME . PhpInterface::PHP_EXT;
        // TODO: fix this behaviour - collect data 1-st for ex.
        if(file_exists($file) === false)
        {
            $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        }
        else
        {
            $this->sourceCode = str_replace(PhpInterface::PHP_OPEN_TAG, '', $this->sourceCode);
            file_put_contents($file, $this->sourceCode, FILE_APPEND);
        }
        if($isCreated)
        {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setRoutes()
    {
        $this->setTag();
        $this->setComment($this->className . ' routes');
        $this->openGroup($this->generator->version);
        $this->setRoute(RoutesInterface::METHOD_GET, $this->generator->objectName, JSONApiInterface::URI_METHOD_INDEX);
        $this->setRoute(RoutesInterface::METHOD_GET, $this->generator->objectName, JSONApiInterface::URI_METHOD_VIEW, true);
        $this->setRoute(RoutesInterface::METHOD_POST, $this->generator->objectName, JSONApiInterface::URI_METHOD_CREATE);
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->generator->objectName, JSONApiInterface::URI_METHOD_UPDATE, true);
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->generator->objectName, JSONApiInterface::URI_METHOD_DELETE, true);
        // create relations process routes
        $this->setTabs();
        $this->setComment('relation routes');
        $this->setRoute(RoutesInterface::METHOD_GET, $this->generator->objectName, JSONApiInterface::URI_METHOD_RELATIONS, true, true);
        $this->setRoute(RoutesInterface::METHOD_POST, $this->generator->objectName, JSONApiInterface::URI_METHOD_CREATE
            . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS), true, true);
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->generator->objectName, JSONApiInterface::URI_METHOD_UPDATE
            . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS), true, true);
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->generator->objectName, JSONApiInterface::URI_METHOD_DELETE
            . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS), true, true);
        $this->closeGroup();
    }
}