<?php

namespace SoliDry\Controllers;

use SoliDry\Blocks\Controllers;
use SoliDry\Blocks\Entities;
use SoliDry\Blocks\FileManager;
use SoliDry\Blocks\FormRequest;
use SoliDry\Blocks\Migrations;
use SoliDry\Blocks\Routes;
use SoliDry\Blocks\Tests;
use SoliDry\Exceptions\DirectoryException;
use SoliDry\Helpers\Console;
use SoliDry\Types\ConsoleInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\PhpInterface;

/**
 * Trait GeneratorTrait
 *
 * @package SoliDry\Controllers
 */
trait GeneratorTrait
{
    use HistoryTrait;

    // all generated entities/resources
    private $forms;
    private $mappers;
    private $routes;
    private $migrations;
    private $controllers;
    private $tests;

    // gen dir found in history
    private $genDir;

    /**
     * Standard generation
     */
    private function generateResources(): void
    {
        $this->outputEntity();

        // create controller
        $this->solveControllers();

        // create FormRequest
        $this->solveFormRequest();

        // create entities/models
        $this->solveEntities();

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        // create tests
        if (empty($this->options[ConsoleInterface::OPTION_TESTS]) === false) {
            try {
                FileManager::createPath($this->formatFuncTestsPath());
            } catch (DirectoryException $e) {
                $this->error($e->getTraceAsString());
            }

            $this->tests = new Tests($this);
            $this->tests->createEntity($this->formatFuncTestsPath(), DefaultInterface::FUNCTIONAL_POSTFIX);
        }

        $this->createMigrations();
    }

    /**
     *  Generation with merge option
     */
    private function mergeResources(): void
    {
        $this->outputEntity();
        $this->solveControllers();

        $this->solveFormRequest();

        $this->solveEntities();

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        $this->createMigrations();
    }

    /**
     *  Creates Controllers and leaves those generated in case of merge
     */
    private function solveControllers(): void
    {
        $this->controllers = new Controllers($this);
        $controllerPath = $this->formatControllersPath();
        if (empty($this->options[ConsoleInterface::OPTION_REGENERATE]) === false
            && file_exists($this->controllers->getEntityFile($controllerPath,
                DefaultInterface::CONTROLLER_POSTFIX)) === true) {
            $this->controllers->recreateEntity($controllerPath, DefaultInterface::CONTROLLER_POSTFIX);
        } else {
            $this->controllers->createDefault();
            $this->controllers->createEntity($controllerPath, DefaultInterface::CONTROLLER_POSTFIX);
        }
    }

    private function solveFormRequest(): void
    {
        $this->forms = new FormRequest($this);
        $formRequestPath = $this->formatRequestsPath();
        if (empty($this->options[ConsoleInterface::OPTION_REGENERATE]) === false
            && file_exists($this->forms->getEntityFile($formRequestPath,
                DefaultInterface::FORM_REQUEST_POSTFIX)) === true) {
            $this->forms->recreateEntity($formRequestPath, DefaultInterface::FORM_REQUEST_POSTFIX);
        } else {
            $this->forms->createEntity($formRequestPath, DefaultInterface::FORM_REQUEST_POSTFIX);
        }
    }

    /**
     *  Decide whether to generate new Entities or mutate existing
     */
    private function solveEntities(): void
    {
        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $entitiesPath = $this->formatEntitiesPath();
        if (empty($this->options[ConsoleInterface::OPTION_MERGE]) === false
            && file_exists($this->forms->getEntityFile($entitiesPath)) === true) {
            $this->mappers->recreateEntity($entitiesPath);
        } else {
            $this->mappers->createEntity($entitiesPath);
        }
    }

    private function outputEntity(): void
    {
        Console::out(
            '===============' . PhpInterface::SPACE . $this->objectName
            . PhpInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
    }

    /**
     *  Creates migrations for every entity if there is merge option - adds additional
     */
    private function createMigrations(): void
    {
        if (empty($this->options[ConsoleInterface::OPTION_MIGRATIONS]) === false) {
            $this->migrations = new Migrations($this);
            $this->migrations->create();
            $this->migrations->createPivot();
        }
    }
}