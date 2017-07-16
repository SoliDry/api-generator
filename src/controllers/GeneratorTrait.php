<?php

namespace rjapi\controllers;

use rjapi\blocks\Controllers;
use rjapi\blocks\Entities;
use rjapi\blocks\Middleware;
use rjapi\blocks\Migrations;
use rjapi\blocks\Routes;
use rjapi\helpers\Console;
use rjapi\types\ConsoleInterface;
use rjapi\types\CustomsInterface;
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

    /**
     * Standard generation
     */
    private function generateResources()
    {
        $this->outputEntity();
        $this->createControllers();
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

        $this->createMigrations();
    }

    /**
     *  Generation with merge option
     */
    private function mergeResources()
    {
        $this->outputEntity();
        $this->createControllers();
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

        $this->createMigrations();
    }

    private function outputEntity()
    {
        Console::out(
            '===============' . PhpInterface::SPACE . $this->objectName
            . PhpInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
    }

    /**
     *  Creates controllers and leaves those generated in case of merge
     */
    private function createControllers()
    {
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->createEntity($this->formatControllersPath(), DefaultInterface::CONTROLLER_POSTFIX);
    }

    /**
     *  Creates migrations for every entity if there is merge option - adds additional
     */
    private function createMigrations()
    {
        if (empty($this->options[ConsoleInterface::OPTION_MIGRATIONS]) === false) {
            $this->migrations = new Migrations($this);
            $this->migrations->create();
            $this->migrations->createPivot();
        }
    }

    /**
     *  Collects all attrs, types and diffs for further code-generation
     */
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
                $this->composeTypes($dir, $files, $rFiles);
            }
        }
    }

    /**
     * Gets history files and merges them with current raml files
     * @param string $dir       desc sorted last date YYYY-mmm-dd directory
     * @param array  $files     files from .gen/ dir saved history
     * @param array  $ramlFiles file that were passed as an option + files from uses RAML property
     */
    private function composeTypes(string $dir, array $files, array $ramlFiles)
    {
        $attrsCurrent = [];
        $attrsHistory = [];
        foreach ($files as $file) {
            foreach ($ramlFiles as $ramlFile) {
                if (mb_strpos($file, basename($ramlFile), null, PhpInterface::ENCODING_UTF8) !== false) {
                    $dataCurrent = Yaml::parse(file_get_contents($ramlFile));
                    $dataHistory = Yaml::parse(file_get_contents(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file));
                    $this->currentTypes = $dataCurrent[RamlInterface::RAML_KEY_TYPES];
                    $this->historyTypes = $dataHistory[RamlInterface::RAML_KEY_TYPES];
                    $this->types += array_merge_recursive($this->historyTypes, $this->currentTypes);
                    $attrsCurrent += array_filter($this->currentTypes, function($k) {
                        return strpos($k, CustomsInterface::CUSTOM_TYPES_ATTRIBUTES) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                    $attrsHistory += array_filter($this->historyTypes, function($k) {
                        return strpos($k, CustomsInterface::CUSTOM_TYPES_ATTRIBUTES) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                }
            }
        }
        $this->composeDiffs($attrsCurrent, $attrsHistory);
    }

    /**
     * Compares attributes for current and previous history and sets the diffTypes prop
     * to process additional migrations creation
     * @param array $attrsCurrent   Current attributes
     * @param array $attrsHistory   History attributes
     */
    private function composeDiffs(array $attrsCurrent, array $attrsHistory)
    {
        // make diffs on current raml array to add columns/indices to migrations
        foreach ($attrsCurrent as $k => $v) {
            if (empty($attrsHistory[$k][RamlInterface::RAML_PROPS]) === false
                && (empty($v[RamlInterface::RAML_PROPS]) === false)) {
                foreach ($v[RamlInterface::RAML_PROPS] as $attr => $attrValue) {
                    if (empty($attrsHistory[$k][RamlInterface::RAML_PROPS][$attr])) { // if there is no such element in history data - collect
                        $this->diffTypes[$k][$attr] = $attrValue;
                    }
                }
            }
        }
    }
}