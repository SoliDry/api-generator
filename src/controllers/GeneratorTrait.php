<?php

namespace rjapi\controllers;

use rjapi\blocks\Entities;
use rjapi\blocks\Middleware;
use rjapi\blocks\Migrations;
use rjapi\blocks\Routes;
use rjapi\types\ConsoleInterface;
use rjapi\types\DefaultInterface;

trait GeneratorTrait
{
    private $forms      = null;
    private $mappers    = null;
    private $routes     = null;
    private $migrations = null;

    private function createNewComponents()
    {
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

    private function appendToComponents()
    {
    }
}