<?php

namespace rjapi\controllers;

use rjapi\blocks\Controllers;
use rjapi\blocks\Entities;
use rjapi\blocks\Middleware;
use rjapi\blocks\Migrations;
use rjapi\blocks\Routes;
use rjapi\helpers\Console;
use rjapi\types\ConsoleInterface;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;

trait GeneratorTrait
{
    private $forms       = null;
    private $mappers     = null;
    private $routes      = null;
    private $migrations  = null;
    private $mergedTypes = [];

    private function generateResources()
    {
        Console::out(
            '================' . PhpInterface::SPACE . $this->objectName
            . PhpInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->createEntity($this->formatControllersPath(), DefaultInterface::CONTROLLER_POSTFIX);

        // create middleware
        $this->forms = new Middleware($this);
        $this->forms->createEntity($this->formatMiddlewarePath(), DefaultInterface::MIDDLEWARE_POSTFIX);
        $this->forms->createAccessToken();

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $this->mappers->createEntity($this->formatEntitiesPath());

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        if (empty($this->options[ConsoleInterface::OPTION_MIGRATIONS]) === false) {
            // create Migrations
            $this->migrations = new Migrations($this);
            $this->migrations->create();
            $this->migrations->createPivot();
        }
    }

    private function mergeResources()
    {
        // create middleware
        $this->forms = new Middleware($this);
        $this->forms->recreateEntity($this->formatMiddlewarePath(), DefaultInterface::MIDDLEWARE_POSTFIX);
    }

    private function setMergedTypes()
    {
        if ($this->options[ConsoleInterface::OPTION_APPEND] === ConsoleInterface::APPEND_DEFAULT_VALUE) {
            $dirs = scandir($this->formatGenPath(), SCANDIR_SORT_DESCENDING);
            if ($dirs !== false) {
                $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
                $dir = $dirs[0]; // desc last date YYYY-mmm-dd
                $files = scandir($this->formatGenPath() . $dir, SCANDIR_SORT_DESCENDING);
                $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);
                foreach ($files as $file) {
                    foreach ($this->ramlFiles as $ramlFile) {

                    }
                }
            }
        }
    }
}