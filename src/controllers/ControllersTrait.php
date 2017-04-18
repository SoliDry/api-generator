<?php
namespace rjapi\controllers;

use Illuminate\Console\Command;
use rjapi\blocks\Config;
use rjapi\blocks\Middleware;
use rjapi\blocks\Controllers;
use rjapi\blocks\FileManager;
use rjapi\blocks\Entities;
use rjapi\blocks\Migrations;
use rjapi\blocks\Module;
use rjapi\blocks\Routes;
use rjapi\helpers\Console;
use rjapi\types\ConsoleInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
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

    public $excludedSubtypes = [
        CustomsInterface::CUSTOM_TYPES_ATTRIBUTES,
        CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS,
        CustomsInterface::CUSTOM_TYPES_FILTER,
        CustomsInterface::CUSTOM_TYPES_TREES,
    ];

    public $options = [];

    /**
     *  Generates api Controllers + Models to support RAML validation
     */
    public function actionIndex(string $ramlFile)
    {
        $data = Yaml::parse(file_get_contents($ramlFile));
        $this->version        = str_replace('/', '', $data['version']);
        $this->appDir         = DirsInterface::APPLICATION_DIR;
        $this->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->entitiesDir    = DirsInterface::ENTITIES_DIR;
        $this->modulesDir     = DirsInterface::MODULES_DIR;
        $this->httpDir        = DirsInterface::HTTP_DIR;
        $this->middlewareDir  = DirsInterface::MIDDLEWARE_DIR;
        $this->migrationsDir  = DirsInterface::MIGRATIONS_DIR;

        if((bool) env('PHP_DEV') === true)
        {
            $this->createDirs();
            $this->options = [
                ConsoleInterface::OPTION_MIGRATIONS => 1,
                ConsoleInterface::OPTION_REGENERATE => 1
            ];
        }
        else
        {
            $this->options = $this->options();
        }
        $this->setIncludedTypes($data);
        $this->runGenerator();
    }

    /**
     *  Main generator method - the sequence of methods execution is crucial
     */
    private function runGenerator()
    {
        $this->generateModule();
        $this->generateConfig();
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
                $this->processObjectData($objName, $objData);
            }
        }
    }

    /**
     * @param string $objName
     * @param array $objData
     */
    private function processObjectData(string $objName, array $objData)
    {
        foreach($objData as $k => $v)
        {
            if($k === RamlInterface::RAML_PROPS)
            { // process props
                $this->setObjectName($objName);
                $this->setObjectProps($v);
                $this->generateResources();
            }
        }
    }

    private function generateModule()
    {
        $module = new Module($this);
        $module->create();
    }
    
    private function generateConfig()
    {
        $module = new Config($this);
        $module->create();
    }

    public function createDirs()
    {
        // create modules dir
        FileManager::createPath(FileManager::getModulePath($this));
        // create config dir
        FileManager::createPath($this->formatConfigPath());
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
        /** @var Command $this */
        return FileManager::getModulePath($this) . DirsInterface::DATABASE_DIR . PhpInterface::SLASH
               . $this->migrationsDir . PhpInterface::SLASH;
    }

    public function formatConfigPath()
    {
        return FileManager::getModulePath($this) . DirsInterface::MODULE_CONFIG_DIR . PhpInterface::SLASH;
    }

    /**
     * @param string $name
     */
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
            '================' . PhpInterface::SPACE . $this->objectName
            . PhpInterface::SPACE . DirsInterface::ENTITIES_DIR
        );
        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->create();

        // create middleware
        $this->forms = new Middleware($this);
        $this->forms->create();
        $this->forms->createAccessToken();

        // create entities/models
        $this->mappers = new Entities($this);
        $this->mappers->createPivot();
        $this->mappers->create();

        // create routes
        $this->routes = new Routes($this);
        $this->routes->create();

        if(empty($this->options[ConsoleInterface::OPTION_MIGRATIONS]) === false)
        {
            // create Migrations
            $this->migrations = new Migrations($this);
            $this->migrations->create();
            $this->migrations->createPivot();
        }
    }

    /**
     * Collect types = main + included files
     * @param array $data
     */
    private function setIncludedTypes(array $data)
    {
        $this->types = $data[RamlInterface::RAML_KEY_TYPES];
        if(empty($data[RamlInterface::RAML_KEY_USES]) === false)
        {
            $files = $data[RamlInterface::RAML_KEY_USES];
            foreach($files as $file)
            {
                $fileData = Yaml::parse(file_get_contents($file));
                $this->types += $fileData[RamlInterface::RAML_KEY_TYPES];
            }
        }
    }
}