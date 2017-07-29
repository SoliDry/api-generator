<?php

namespace rjapi\controllers;

use rjapi\blocks\Controllers;
use rjapi\blocks\Entities;
use rjapi\blocks\Middleware;
use rjapi\blocks\Migrations;
use rjapi\blocks\Routes;
use rjapi\exception\DirectoryException;
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
        $opMerge = $this->options[ConsoleInterface::OPTION_MERGE];
        $timeCheck = strtotime($opMerge); // only for validation - coz of a diff timezones
        if (false !== $timeCheck) {
            $dateTime = explode(PhpInterface::SPACE, $opMerge);
            $this->mergeTime($dateTime);
        } else if (is_numeric($opMerge) !== false) {
            $this->mergeStep($opMerge);
        } else if ($opMerge === ConsoleInterface::MERGE_DEFAULT_VALUE) {
            $this->mergeLast();
        }
    }

    /**
     * Merges history RAML files with current by time in the past
     * @param array $dateTime
     * @throws DirectoryException
     */
    private function mergeTime(array $dateTime)
    {
        $date = $dateTime[0];
        $time = str_replace(':', '', $dateTime[1]);
        $path = DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR;
        if (is_dir($path) === false) {
            throw new DirectoryException('The directory: ' . $path . ' was not found.');
        }
        $files = glob($path . $time . '*' . DefaultInterface::RAML_EXT);
        foreach ($files as &$fullPath) {
            $fullPath = str_replace($path, '', $fullPath);
        }
        $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);
        $this->composeTypes($date, $files, $this->ramlFiles);
    }

    /**
     * Merges history RAML files with current by backward steps
     * @param int $step
     */
    private function mergeStep(int $step)
    {
        $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
        if ($dirs !== false) {
            $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
            $composed = $this->composeStepFiles($dirs, $step);
            $this->composeTypes($composed['dirToPass'], $composed['filesToPass'], $this->ramlFiles);
        }
    }

    private function composeStepFiles(array $dirs, int $step)
    {
        $dirToPass   = '';
        $filesToPass = '';
        foreach ($dirs as $dir) {
            $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir, SCANDIR_SORT_DESCENDING);
            $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);
            $prefixFlag = '';
            foreach ($files as $kFile => $file) {
                $prefix = substr($file, 0, 6); // Hms
                if ($prefix !== $prefixFlag) {
                    --$step;
                    $prefixFlag = $prefix;
                    if ($step > 0) {
                        $skip = preg_grep('/^' . $prefix . '.*$/i', $files);
                        $files = array_diff($files, $skip);
                    }
                }
                if ($step <= 0) {
                    $skip = preg_grep('/[^' . $prefix . '.*]+/i', $files);
                    $files = array_diff($files, $skip);
                    $dirToPass = $dir;
                    $filesToPass = $files;
                    break;
                }
            }
        }
        return compact('dirToPass', 'filesToPass');
    }

    private function mergeLast()
    {
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