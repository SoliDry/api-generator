<?php

namespace SoliDry\Blocks;

use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\Console;
use SoliDry\ApiGenerator;
use SoliDry\Types\ApiInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\RoutesInterface;

class Routes
{

    use ContentManager, RoutesTrait;

    /** @var ApiGenerator $generator */
    private $generator;
    protected $sourceCode = '';

    private $className;

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setCodeState($generator): void
    {
        $this->generator = $generator;
    }

    public function create(): void
    {
        $this->setRoutes();
        $isCreated = false;

        $file = FileManager::getModulePath($this->generator, true) .
            RoutesInterface::ROUTES_FILE_NAME . PhpInterface::PHP_EXT;

        // TODO: fix this behaviour - collect data 1-st for ex.
        if ($this->generator->routesCreated === 0 || file_exists($file) === false) {
            $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        } else {
            $this->sourceCode = str_replace(PhpInterface::PHP_OPEN_TAG, '', $this->sourceCode);
            file_put_contents($file, $this->sourceCode, FILE_APPEND);
        }

        ++$this->generator->routesCreated;
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setRoutes(): void
    {
        $this->setTag();
        $this->setComment(DefaultInterface::ROUTES_START, 0);
        $this->setComment($this->className . ' routes', 0);
        $this->openGroup();

        // create bulk api-calls
        $this->setBulkRoutes();

        // create basic api-calls of JSON API
        $this->setBasicRoutes();

        // create relations process routes
        $this->setRelationsRoutes();
        $this->closeGroup();
        $this->setComment(DefaultInterface::ROUTES_END, 0);
    }

    private function setBulkRoutes(): void
    {
        $this->setComment('bulk routes');

        $this->setRoute(RoutesInterface::METHOD_POST, $this->composeBulkUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_CREATE_BULK));
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->composeBulkUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_UPDATE_BULK));
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->composeBulkUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_DELETE_BULK));
    }

    private function setBasicRoutes(): void
    {
        $this->setComment('basic routes');

        $this->setRoute(RoutesInterface::METHOD_OPTIONS, $this->composeObjectUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_OPTIONS));
        $this->setRoute(RoutesInterface::METHOD_GET, $this->composeObjectUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_INDEX));
        $this->setRoute(RoutesInterface::METHOD_GET, $this->composeIdUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_VIEW));
        $this->setRoute(RoutesInterface::METHOD_POST, $this->composeObjectUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_CREATE));
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->composeIdUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_UPDATE));
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->composeIdUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_DELETE));
    }

    private function setRelationsRoutes(): void
    {
        $this->setComment('relation routes');

        $this->setRoute(RoutesInterface::METHOD_GET, $this->composeRelationsUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_RELATIONS));
        $this->setRoute(RoutesInterface::METHOD_POST, $this->composeRelationsUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_CREATE
                . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS)));
        $this->setRoute(RoutesInterface::METHOD_PATCH, $this->composeRelationsUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_UPDATE
                . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS)));
        $this->setRoute(RoutesInterface::METHOD_DELETE, $this->composeRelationsUri(),
            $this->composeEndPoint(JSONApiInterface::URI_METHOD_DELETE
                . ucfirst(JSONApiInterface::URI_METHOD_RELATIONS)));
    }
}