<?php
namespace rjapi\controllers;

use Illuminate\Console\Command;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\Middleware;
use rjapi\blocks\CommandsInterface;
use rjapi\blocks\Controllers;
use rjapi\blocks\CustomsInterface;
use rjapi\blocks\FileManager;
use rjapi\blocks\Entities;
use rjapi\blocks\Migrations;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;
use rjapi\blocks\Routes;
use rjapi\helpers\Console;
use Symfony\Component\Yaml\Yaml;

trait ControllersTrait
{
    // dirs
    public $rootDir        = '';
    public $appDir         = '';
    public $modulesDir     = '';
    public $httpDir        = '';
    public $controllersDir = '';
    public $middlewareDir  = '';
    public $entitiesDir    = '';
    public $migrationsDir  = '';

    public $version;
    public $objectName        = '';
    public $defaultController = 'Default';
    public $uriNamedParams    = null;
    public $ramlFile          = '';
    public $force             = null;
    public $customTypes       = [
        CustomsInterface::CUSTOM_TYPES_ID,
        CustomsInterface::CUSTOM_TYPES_TYPE,
        CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_SINGLE_DATA_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_MULTIPLE_DATA_RELATIONSHIPS,
    ];
    public $types             = [];
    public $frameWork         = '';
    public $objectProps       = [];
    public $generatedFiles    = [];
    public $relationships     = [];

    private $forms        = null;
    private $controllers  = null;
    private $moduleObject = null;
    private $mappers      = null;
    private $containers   = null;
    private $routes       = null;
    private $migrations   = null;

    private $excludedSubtypes = [
        self::CUSTOM_TYPES_ATTRIBUTES,
        self::CUSTOM_TYPES_RELATIONSHIPS,
        self::CUSTOM_TYPES_QUERY_SEARCH,
        self::CUSTOM_TYPES_FILTER,
    ];

    private $options = [];

    /**
     *  Generates api Controllers + Models to support RAML validation
     */
    public function actionIndex(string $ramlFile)
    {
//        $this->options = $this->options();
        $data = Yaml::parse(file_get_contents($ramlFile));

        $this->version        = str_replace('/', '', $data['version']);
        $this->appDir         = self::APPLICATION_DIR;
        $this->controllersDir = self::CONTROLLERS_DIR;
        $this->entitiesDir    = self::ENTITIES_DIR;
        $this->modulesDir     = self::MODULES_DIR;
        $this->httpDir        = self::HTTP_DIR;
        $this->middlewareDir  = self::MIDDLEWARE_DIR;
        $this->migrationsDir  = self::MIGRATIONS_DIR;

        $this->types = $data['types'];
        if(env('PHP_DEV'))
        {
            $this->createDirs();
        }
        $this->runGenerator();
    }

    private function runGenerator()
    {
        $this->generateModule();
        foreach($this->types as $objName => $objData)
        {
            if(in_array($objName, $this->customTypes) === false)
            { // if this is not a custom type generate resources
                $excluded = false;
                foreach($this->excludedSubtypes as $type)
                {
                    if(strpos($objName, $type) !== false)
                    {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if($excluded === true)
                {
                    continue;
                }
                foreach($objData as $k => $v)
                {
                    if($k === self::RAML_PROPS)
                    { // process props
                        $this->setObjectName($objName);
                        $this->setObjectProps($v);
                        $this->generateResources();
                    }
                }
            }
        }
    }

    private function generateModule()
    {
        $output = [];
        exec(CommandsInterface::LARAVEL_MODULE_MAKE . PhpEntitiesInterface::SPACE . $this->version, $output);
        exec(CommandsInterface::LARAVEL_MODULE_USE . PhpEntitiesInterface::SPACE . $this->version, $output);
        foreach($output as $str)
        {
            Console::out($str, Console::COLOR_GREEN);
        }
    }

    public function createDirs()
    {
        // create modules dir
        FileManager::createPath(FileManager::getModulePath($this));
        // create controllers dir
        FileManager::createPath($this->formatControllersPath());
        // create forms dir
        FileManager::createPath($this->formatMiddlewarePath());
        // create mapper dir
        FileManager::createPath($this->formatEntitiesPath());
        // create migrations dir
        FileManager::createPath($this->formatMigrationsPath());
    }

    public function formatControllersPath(): string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->controllersDir;
    }

    public function formatMiddlewarePath(): string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->middlewareDir;
    }

    public function formatEntitiesPath() : string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this) . $this->entitiesDir;
    }

    public function formatMigrationsPath() : string
    {
        /** @var ControllersTrait $this */
        return FileManager::getModulePath($this) . self::DATABASE_DIR . PhpEntitiesInterface::SLASH
               . $this->migrationsDir . PhpEntitiesInterface::SLASH;
    }

    private function setObjectName(string $name)
    {
        $this->objectName = $name;
    }

    private function setObjectProps($props)
    {
        $this->objectProps = $props;
    }

    /**
     * The creation sequence of every entity element is crucial
     */
    private function generateResources()
    {
        Console::out(
            '================' . PhpEntitiesInterface::SPACE . $this->objectName
            . PhpEntitiesInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->create();

        // create middleware
        $this->forms = new Middleware($this);
        $this->forms->create();

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $this->mappers->create();

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        // create Migrations
        $this->migrations = new Migrations($this);
        $this->migrations->create();
        $this->migrations->createPivot();
    }

    /**
     * @return array
     */
    public function getGeneratedFiles(): array
    {
        return $this->generatedFiles;
    }

    /**
     * @param string $path
     */
    public function setGeneratedFiles(string $path)
    {
        $this->generatedFiles[] = $path;
    }
}