<?php

namespace rjapi\blocks;

use rjapi\extension\JSONApiInterface;
use rjapi\RJApiGenerator;

class Routes
{
    use ContentManager, RoutesTrait;
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setTag();
        $this->openGroup($this->generator->version);
        $this->setRoute(RoutesInterface::METHOD_GET, $this->generator->objectName, JSONApiInterface::URI_METHOD_INDEX);
        $this->setRoute(RoutesInterface::METHOD_GET, $this->generator->objectName, JSONApiInterface::URI_METHOD_VIEW);
        $this->setRoute(RoutesInterface::METHOD_POST, $this->generator->objectName, JSONApiInterface::URI_METHOD_CREATE);
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->generator->objectName, JSONApiInterface::URI_METHOD_UPDATE);
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->generator->objectName, JSONApiInterface::URI_METHOD_DELETE);
        $this->closeGroup();

        $file = FileManager::getModulePath($this, true) .
                RoutesInterface::ROUTES_FILE_NAME . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode, true);
    }
}