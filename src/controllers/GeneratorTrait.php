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
use rjapi\types\RamlInterface;
use Symfony\Component\Yaml\Yaml;

trait GeneratorTrait
{
    private $forms       = null;
    private $mappers     = null;
    private $routes      = null;
    private $migrations  = null;

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
        if (true === file_exists($this->forms->getEntityFile($this->formatMiddlewarePath(), DefaultInterface::MIDDLEWARE_POSTFIX))) {
            $this->forms->recreateEntity($this->formatMiddlewarePath(), DefaultInterface::MIDDLEWARE_POSTFIX);
        } else {
            $this->forms->createEntity($this->formatMiddlewarePath(), DefaultInterface::MIDDLEWARE_POSTFIX);
        }

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        if (true === file_exists($this->forms->getEntityFile($this->formatEntitiesPath()))) {
            $this->mappers->recreateEntity($this->formatEntitiesPath());
        } else {
            $this->mappers->createEntity($this->formatEntitiesPath());
        }

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

    private function setMergedTypes()
    {
        if ($this->options[ConsoleInterface::OPTION_MERGE] === ConsoleInterface::MERGE_DEFAULT_VALUE) {
            $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
            if ($dirs !== false) {
                $rFiles = $this->ramlFiles;
                $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
                $dir = $dirs[0]; // desc last date YYYY-mmm-dd
                $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir, SCANDIR_SORT_DESCENDING);
                $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);
                foreach ($files as $file) {
                    foreach ($rFiles as $ramlFile) {
                        if (mb_strpos($file, basename($ramlFile), null, PhpInterface::ENCODING_UTF8) !== false) {
                            $dataCurrent = Yaml::parse(file_get_contents($ramlFile));
                            $dataHistory = Yaml::parse(file_get_contents(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file));
                            $typesCurrent = $dataCurrent[RamlInterface::RAML_KEY_TYPES];
                            $typesHistory = $dataHistory[RamlInterface::RAML_KEY_TYPES];
                            $this->types += array_merge_recursive($typesHistory, $typesCurrent);
                        }
                    }
                }
            }
        }
    }
}