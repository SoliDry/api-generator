<?php
namespace rjapi\controllers;

use Illuminate\Console\Command;
use rjapi\blocks\BaseFormRequestModel;
use rjapi\blocks\CommandsInterface;
use rjapi\blocks\Controllers;
use rjapi\blocks\CustomsInterface;
use rjapi\blocks\FileManager;
use rjapi\blocks\Mappers;
use rjapi\blocks\PhpEntitiesInterface;
use Symfony\Component\Yaml\Yaml;

trait ControllersTrait
{
    // paths
    public $rootDir = '';
    public $appDir = '';
    public $modulesDir = '';
    public $httpDir = '';
    public $controllersDir = '';
    public $middlewareDir = '';
    public $entitiesDir = '';

    public $version;
    public $objectName = '';
    public $defaultController = 'Default';
    public $uriNamedParams = null;
    public $ramlFile = '';
    public $force = null;
    public $customTypes = [
        CustomsInterface::CUSTOM_TYPES_ID,
        CustomsInterface::CUSTOM_TYPES_TYPE,
        CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_SINGLE_DATA_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_MULTIPLE_DATA_RELATIONSHIPS,
    ];
    public $types = [];
    public $frameWork = '';
    public $objectProps = [];

    /**
     *  Generates api Controllers + Models to support RAML validation
     */
    public function actionIndex($ramlFile)
    {
        $data = Yaml::parse(file_get_contents($ramlFile));

        $this->version = str_replace('/', '', $data['version']);
        $this->appDir = self::APPLICATION_DIR;
        $this->controllersDir = self::CONTROLLERS_DIR;
        $this->entitiesDir = self::ENTITIES_DIR;
        $this->modulesDir = self::MODULES_DIR;

        $this->types = $data['types'];
        $this->runGenerator();
    }

    private function runGenerator()
    {
        $this->generateModule();
        foreach ($this->types as $objName => $objData) {
            if (!in_array($objName, $this->customTypes)) { // if this is not a custom type generate resources
                $excluded = false;
                foreach ($this->excludedSubtypes as $type) {
                    if (strpos($objName, $type) !== false) {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if ($excluded === true) {
                    continue;
                }

                foreach ($objData as $k => $v) {
                    if ($k === self::RAML_PROPS) { // process props
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
        exec(CommandsInterface::LARAVEL_MODULE_MAKE . PhpEntitiesInterface::SPACE . $this->version);
        exec(CommandsInterface::LARAVEL_MODULE_USE . PhpEntitiesInterface::SPACE . $this->version);
        exec(CommandsInterface::LARAVEL_MODULE_LIST . PhpEntitiesInterface::SPACE . $this->version);
    }

    /*private function createDirs()
    {
        // create modules dir
        FileManager::createPath(FileManager::getModulePath($this));
        // create controllers dir
        FileManager::createPath($this->formatControllersPath());
        // create forms dir
        FileManager::createPath($this->formatFormsPath());
        // create mapper dir
        FileManager::createPath($this->formatMappersPath());
    }*/

    public function formatControllersPath()
    {
        /** @var Command $this */
        return FileManager::getModulePath($this) . $this->httpDir . PhpEntitiesInterface::SLASH . $this->controllersDir;
    }

    public function formatMiddlewarePath()
    {
        /** @var Command $this */
        return FileManager::getModulePath($this) . $this->httpDir . PhpEntitiesInterface::SLASH . $this->middlewareDir;
    }

    public function formatEntitiesPath() : string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->entitiesDir;
    }

    public function formatMappersPath() : string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->mappersDir;
    }

    private function setObjectName($name)
    {
        $this->objectName = $name;
    }

    private function setObjectProps($props)
    {
        $this->objectProps = $props;
    }

    private function generateResources()
    {
        /** @var Command $this */
        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->create();

        // create middleware
        $this->forms = new BaseFormRequestModel($this);
        $this->forms->create();

        // create mappers
        $this->mappers = new Mappers($this);
        $this->mappers->create();
    }
}