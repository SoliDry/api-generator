<?php
namespace rjapi\extension\yii2\raml\controllers;

use Symfony\Component\Yaml\Yaml;
use rjapi\extension\yii2\raml\ramlblocks\BaseModels;
use rjapi\extension\yii2\raml\ramlblocks\Controllers;
use rjapi\extension\yii2\raml\ramlblocks\CustomsInterface;
use rjapi\extension\yii2\raml\ramlblocks\FileManager;
use rjapi\extension\yii2\raml\ramlblocks\DefaultInterface;
use rjapi\extension\yii2\raml\ramlblocks\HTTPMethodsInterface;
use rjapi\extension\yii2\raml\ramlblocks\PhpEntitiesInterface;
use rjapi\extension\yii2\raml\ramlblocks\RamlInterface;
use rjapi\extension\yii2\raml\ramlblocks\Mappers;
use rjapi\extension\yii2\raml\ramlblocks\Module;
use yii\console\Controller;

class TypesController extends Controller implements DefaultInterface, PhpEntitiesInterface, HTTPMethodsInterface,
    RamlInterface, CustomsInterface
{
    const CONTENT_TYPE = 'application/vnd.api+json';

    const RESPONSE_CODE_200 = '200';
    const RESPONSE_CODE_201 = '201';

    public $rootDir = '';
    public $appDir = 'app';
    public $modulesDir = 'modules';
    public $controllersDir = 'controllers';
    public $modelsFormDir = 'models';
    public $formsDir = 'forms';
    public $mappersDir = 'mappers';
    public $version;
    public $objectName = '';
    public $defaultController = 'Default';
    public $uriNamedParams = null;
    public $ramlFile = '';
    public $force = null;
    public $customTypes = [
        self::CUSTOM_TYPES_ID,
        self::CUSTOM_TYPES_TYPE,
        self::CUSTOM_TYPES_RELATIONSHIPS,
        self::CUSTOM_TYPES_SINGLE_DATA_RELATIONSHIPS,
        self::CUSTOM_TYPES_MULTIPLE_DATA_RELATIONSHIPS,
    ];
    public $types = [];
    public $frameWork = '';
    public $objectProps = [];

    private $forms = null;
    private $controllers = null;
    private $moduleObject = null;
    private $mappers = null;
    private $excludedSubtypes = [
        self::CUSTOM_TYPES_ATTRIBUTES,
        self::CUSTOM_TYPES_RELATIONSHIPS,
        self::CUSTOM_TYPES_QUERY_SEARCH,
        self::CUSTOM_TYPES_FILTER,
    ];

    /**
     * @param string $actionId the action id of the current request
     *
     * @return array
     */
    public function options($actionId)
    {
        return ['force', 'ramlFile'];
    }

    /**
     * @return array
     */
    public function optionAliases()
    {
        return [
            'f' => 'force', // force override files
            'rf' => 'ramlFile' // pass RAML file
        ];
    }

    /**
     *  Generates api Controllers + Models to support RAML validation
     */
    public function actionIndex($ramlFile)
    {
        $data = Yaml::parse(file_get_contents($ramlFile));
        $this->version = str_replace('/', '', $data['version']);
        $this->frameWork = $data['uses']['FrameWork'];
        $this->createDirs();

        $this->types = $data['types'];
        $this->runGenerator();
    }

    private function runGenerator()
    {
        switch ($this->frameWork) {
            case self::FRAMEWORK_YII:
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
                break;
            case self::FRAMEWORK_LARAVEL:
                // TODO: implement laravel gen
                break;
        }
    }

    private function createDirs()
    {
        switch ($this->frameWork) {
            case self::FRAMEWORK_YII:
                // create modules dir
                FileManager::createPath(FileManager::getModulePath($this));
                // create controllers dir
                FileManager::createPath($this->formatControllersPath());
                // create forms dir
                FileManager::createPath($this->formatFormsPath());
                // create mapper dir
                FileManager::createPath($this->formatMappersPath());
                break;
            case self::FRAMEWORK_LARAVEL:
                // TODO: implement laravel gen
                break;
        }
    }

    public function formatControllersPath()
    {
        return FileManager::getModulePath($this) . $this->controllersDir;
    }

    public function formatModelsPath()
    {
        return FileManager::getModulePath($this) . $this->modelsFormDir;
    }

    public function formatFormsPath() : string
    {
        return FileManager::getModulePath($this, true) . $this->formsDir;
    }

    public function formatMappersPath() : string
    {
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
        // create controller
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();
        $this->controllers->create();

        // create module
        $this->moduleObject = new Module($this);
        $this->moduleObject->createModule();

        // create model
        $this->forms = new BaseModels($this);
        $this->forms->create();

        // create mappers
        $this->mappers = new Mappers($this);
        $this->mappers->create();
    }
}