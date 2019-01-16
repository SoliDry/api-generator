<?php

namespace rjapi\controllers;

use rjapi\blocks\Controllers;
use rjapi\blocks\Entities;
use rjapi\blocks\FileManager;
use rjapi\blocks\FormRequest;
use rjapi\blocks\Migrations;
use rjapi\blocks\Routes;
use rjapi\blocks\Tests;
use rjapi\exceptions\DirectoryException;
use rjapi\helpers\Console;
use rjapi\types\ConsoleInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\ApiInterface;
use Symfony\Component\Yaml\Yaml;

trait GeneratorTrait
{
    private $forms;
    private $mappers;
    private $routes;
    private $migrations;
    private $controllers;
    private $tests;

    private $genDir;

    /**
     * Standard generation
     */
    private function generateResources(): void
    {
        $this->outputEntity();
        $this->createControllers();

        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->createEntity($this->formatControllersPath(), DefaultInterface::CONTROLLER_POSTFIX);

        // create FormRequest
        $this->forms = new FormRequest($this);
        $this->forms->createEntity($this->formatRequestsPath(), DefaultInterface::FORM_REQUEST_POSTFIX);
        $this->forms->createAccessToken();

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $this->mappers->createEntity($this->formatEntitiesPath());

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
        $this->createControllers();

        $this->forms = new FormRequest($this);
        $formRequestPath = $this->formatRequestsPath();
        if (true === file_exists($this->forms->getEntityFile($formRequestPath, DefaultInterface::FORM_REQUEST_POSTFIX))) {
            $this->forms->recreateEntity($formRequestPath, DefaultInterface::FORM_REQUEST_POSTFIX);
        } else {
            $this->forms->createEntity($formRequestPath, DefaultInterface::FORM_REQUEST_POSTFIX);
        }

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $entitiesPath = $this->formatEntitiesPath();
        if (true === file_exists($this->forms->getEntityFile($entitiesPath))) {
            $this->mappers->recreateEntity($entitiesPath);
        } else {
            $this->mappers->createEntity($entitiesPath);
        }

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        $this->createMigrations();
    }

    private function outputEntity(): void
    {
        Console::out(
            '===============' . PhpInterface::SPACE . $this->objectName
            . PhpInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
    }

    /**
     *  Creates controllers and leaves those generated in case of merge
     */
    private function createControllers(): void
    {
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->createEntity($this->formatControllersPath(), DefaultInterface::CONTROLLER_POSTFIX);
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

    /**
     *  Collects all attrs, types and diffs for further code-generation
     */
    private function setMergedTypes(): void
    {
        $opMerge = $this->options[ConsoleInterface::OPTION_MERGE];
        $timeCheck = strtotime($opMerge); // only for validation - coz of a diff timezones

        if (false !== $timeCheck) {
            $dateTime = explode(PhpInterface::SPACE, $opMerge);

            try {
                $this->mergeTime($dateTime);
            } catch (DirectoryException $e) {
                $this->error($e->getTraceAsString());
            }
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
    private function mergeTime(array $dateTime): void
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
        $this->composeTypes($date, $files, $this->files);
    }

    /**
     * Merges history RAML files with current by backward steps
     * @param int $step
     */
    private function mergeStep(int $step): void
    {
        $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
        if ($dirs !== false) {
            $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
            $this->composeTypes($this->genDir, $this->composeStepFiles($dirs, $step), $this->files);
        }
    }

    private function composeStepFiles(array $dirs, int $step): array
    {
        $filesToPass = [];
        foreach ($dirs as $dir) {
            $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir, SCANDIR_SORT_DESCENDING);
            $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);

            $prefixFlag = '';
            foreach ($files as $kFile => $file) {
                $prefix = substr($file, 0, 6); // Hms
                $template = '/^' . $prefix . '.*$/i';

                if ($prefix !== $prefixFlag) {
                    --$step;
                    $prefixFlag = $prefix;
                    if ($step > 0) {
                        $skip = preg_grep($template, $files);
                        $files = array_diff($files, $skip);
                    }
                }

                if ($step <= 0) {
                    $files = preg_grep($template, $files);
                    $this->genDir = $dir;
                    $filesToPass = $files;
                    break 2;
                }
            }
        }

        return $this->adjustFiles($filesToPass);
    }

    private function mergeLast(): void
    {
        $lastFiles = $this->getLastFiles();
        if (empty($lastFiles) === false) {
            $this->composeTypes($lastFiles['dir'], $lastFiles['files'], $this->files);
        }
    }

    /**
     * Gets last files according to main file named "openapi" by spec of OAS
     * and it's included files defined in "uses" property
     *
     * @return array
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function getLastFiles(): array
    {
        $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
        if ($dirs !== false) {
            $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
            $this->genDir = $dirs[0]; // desc last date YYYY-mm-dd

            $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $this->genDir, SCANDIR_SORT_DESCENDING);
            $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);

            $lastFiles = [];
            foreach ($files as $file) {
                if (($pos = strpos($file, ApiInterface::OPEN_API_KEY)) !== false) {
                    $lastFiles[] = $file;
                    $content = Yaml::parse(file_get_contents($this->formatGenPathByDir() .$file));
                    if (empty($content[ApiInterface::RAML_KEY_USES]) === false) {
                        foreach ($content[ApiInterface::RAML_KEY_USES] as $subFile) {
                            $lastFiles[] = substr($file, 0, $pos) . basename($subFile);
                        }
                    }
                    break;
                }
            }

            return $this->adjustFiles($lastFiles);
        }

        return [];
    }

    /**
     * Gets history files and merges them with current raml files
     * @param string $dir desc sorted last date YYYY-mmm-dd directory
     * @param array $files files from .gen/ dir saved history
     * @param array $inputFiles file that were passed as an option + files from uses RAML property
     */
    private function composeTypes(string $dir, array $files, array $inputFiles): void
    {
        $attrsCurrent = [];
        $attrsHistory = [];

        foreach ($files as $file) {
            foreach ($inputFiles as $inFile) {

                if (mb_strpos($file, basename($inFile), null, PhpInterface::ENCODING_UTF8) !== false) {
                    $dataCurrent = Yaml::parse(file_get_contents($inFile));
                    $dataHistory = Yaml::parse(file_get_contents(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file));

                    $this->currentTypes = $dataCurrent[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
                    $this->historyTypes = $dataHistory[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
                    $this->types += array_merge_recursive($this->historyTypes, $this->currentTypes);

                    $attrsCurrent += array_filter($this->currentTypes, function ($k) {
                        return strpos($k, CustomsInterface::CUSTOM_TYPES_ATTRIBUTES) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                    $attrsHistory += array_filter($this->historyTypes, function ($k) {
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
     * @param array $attrsCurrent Current attributes
     * @param array $attrsHistory History attributes
     */
    private function composeDiffs(array $attrsCurrent, array $attrsHistory): void
    {
        // make diffs on current array to add columns/indices to migrations
        foreach ($attrsCurrent as $k => $v) {
            if (empty($attrsHistory[$k][ApiInterface::RAML_PROPS]) === false
                && (empty($v[ApiInterface::RAML_PROPS]) === false)) {

                foreach ($v[ApiInterface::RAML_PROPS] as $attr => $attrValue) {
                    if (empty($attrsHistory[$k][ApiInterface::RAML_PROPS][$attr])) { // if there is no such element in history data - collect
                        $this->diffTypes[$k][$attr] = $attrValue;
                    }
                }
            }
        }

        // reflect array from history to append lost props
        foreach ($attrsHistory as $k => $v) {
            if (empty($attrsCurrent[$k][ApiInterface::RAML_PROPS]) === false
                && (empty($v[ApiInterface::RAML_PROPS]) === false)) {

                foreach ($v[ApiInterface::RAML_PROPS] as $attr => $attrValue) {
                    if (empty($attrsCurrent[$k][ApiInterface::RAML_PROPS][$attr])) { // if there is no such element in current data - collect
                        $this->diffTypes[$k][$attr] = $attrValue;
                    }
                }
            }
        }
    }

    private function adjustFiles(array $files)
    {
        $tmpFile = '';
        foreach ($files as $k => $file) {
            if (strpos($file, ApiInterface::OPEN_API_KEY) !== false) {
                $tmpFile = $file;
                unset($files[$k]);
                break;
            }
        }
        array_unshift($files, $tmpFile);

        return $files;
    }
}