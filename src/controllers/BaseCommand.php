<?php

namespace rjapi\controllers;

use Illuminate\Console\Command;
use rjapi\blocks\Config;
use rjapi\blocks\FileManager;
use rjapi\blocks\Module;
use rjapi\exceptions\DirectoryException;
use rjapi\exceptions\SchemaException;
use rjapi\types\ConsoleInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\ApiInterface;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command
{
    use GeneratorTrait;

    // dirs
    public $rootDir        = '';
    public $appDir         = '';
    public $modulesDir     = '';
    public $httpDir        = '';
    public $controllersDir = '';
    public $formRequestDir = '';
    public $entitiesDir    = '';
    public $migrationsDir  = '';

    public $version;
    public $objectName        = '';
    public $defaultController = 'Default';
    public $uriNamedParams;
    public $ramlFile          = '';
    public $force;
    public $customTypes       = [
        CustomsInterface::CUSTOM_TYPES_ID,
        CustomsInterface::CUSTOM_TYPES_TYPE,
        CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPE_REDIS,
    ];

    public  $types          = [];
    public  $currentTypes   = [];
    public  $historyTypes   = [];
    public  $mergedTypes    = [];
    public  $diffTypes      = [];
    public  $objectProps    = [];
    public  $generatedFiles = [];
    public  $relationships  = [];
    private $files          = [];

    public $excludedSubtypes = [
        CustomsInterface::CUSTOM_TYPES_ATTRIBUTES,
        CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS,
        CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS,
        CustomsInterface::CUSTOM_TYPES_FILTER,
        CustomsInterface::CUSTOM_TYPES_TREES,
    ];

    public $options = [];
    public $isMerge = false;
    /** increment created routes to create file first and then append content */
    public $routesCreated = 0;

    public $data = [];

    /**
     *  Generates api Controllers + Models to support RAML validation
     *
     * @param string $file path to raml file
     * @throws SchemaException
     */
    public function actionIndex(string $file)
    {
        $this->files[] = $file;
        $this->data    = Yaml::parse(file_get_contents($file));
        $this->validate();
        $this->generateOpenApi();
    }

    /**
     * Validates OAS + Custom fields
     * @throws SchemaException
     */
    private function validate()
    {
        // required yaml fields will be thrown as exceptions
        if (empty($this->data[ApiInterface::OPEN_API_KEY])) {
            throw new SchemaException('There must be ' . ApiInterface::OPEN_API_KEY . ', ' . ApiInterface::API_SERVERS . ' document root fields');
        }

        $schemas = $this->data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        if (empty($schemas[CustomsInterface::CUSTOM_TYPES_ID])
            || empty($schemas[CustomsInterface::CUSTOM_TYPES_TYPE])
            || empty($schemas[CustomsInterface::CUSTOM_RELATIONSHIPS_DATA_ITEM])) {
            throw new SchemaException('At least these types must be declared: ' . CustomsInterface::CUSTOM_TYPES_ID
                . ', ' . CustomsInterface::CUSTOM_TYPES_TYPE . ', ' . CustomsInterface::CUSTOM_RELATIONSHIPS_DATA_ITEM
                . ' as components -> schemas descendants');
        }

        if (empty($this->data[ApiInterface::API_INFO])) {
            $this->warn(ApiInterface::API_INFO . ': field would be convenient to show users what this API is about');
        }
    }

    private function generateOpenApi()
    {
        $this->appDir         = DirsInterface::APPLICATION_DIR;
        $this->controllersDir = DirsInterface::CONTROLLERS_DIR;
        $this->entitiesDir    = DirsInterface::ENTITIES_DIR;
        $this->modulesDir     = DirsInterface::MODULES_DIR;
        $this->httpDir        = DirsInterface::HTTP_DIR;
        $this->formRequestDir = DirsInterface::FORM_REQUEST_DIR;
        $this->migrationsDir  = DirsInterface::MIGRATIONS_DIR;

        foreach ($this->data[ApiInterface::API_SERVERS] as $server) {
            $vars          = $server[ApiInterface::API_VARS];
            $this->version = $vars[ApiInterface::API_BASE_PATH][ApiInterface::API_DEFAULT];

            if (env('APP_ENV') === 'dev') { // for test env based on .env
                $this->options = [
                    ConsoleInterface::OPTION_REGENERATE => 1,
                    ConsoleInterface::OPTION_MIGRATIONS => 1,
                    ConsoleInterface::OPTION_TESTS      => 1,
                ];
            } else {
                $this->options = $this->options();
            }

            $this->setIncludedTypes();
            $this->runGenerator();

            try {
                $this->setGenHistory();
            } catch (DirectoryException $ex) {
                $this->error($ex->getTraceAsString());
            }
        }
    }

    /**
     *  Main generator method - the sequence of methods execution is crucial
     */
    private function runGenerator()
    {
        if (empty($this->options[ConsoleInterface::OPTION_MERGE]) === false) { // create new or regenerate
            $this->setMergedTypes();
            $this->isMerge = true;
        }

        $this->generateModule();
        $this->generateConfig();
        $this->generate();
    }

    /**
     *  Generates new code or regenerate older with new content
     */
    private function generate()
    {
        foreach ($this->types as $objName => $objData) {
            if (in_array($objName, $this->customTypes) === false) { // if this is not a custom type generate resources
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
        foreach ($objData as $k => $v) {
            if ($k === ApiInterface::RAML_PROPS) { // process props
                $this->setObjectName($objName);
                $this->setObjectProps($v);
                if (true === $this->isMerge) {
                    $this->mergeResources();
                } else {
                    $this->generateResources();
                }
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

    /**
     * @throws \rjapi\exceptions\DirectoryException
     */
    public function createDirs()
    {
        // create modules dir
        FileManager::createPath(FileManager::getModulePath($this));
        // create config dir
        FileManager::createPath($this->formatConfigPath());
        // create controllers dir
        FileManager::createPath($this->formatControllersPath());
        // create forms dir
        FileManager::createPath($this->formatRequestsPath());
        // create mapper dir
        FileManager::createPath($this->formatEntitiesPath());
        // create migrations dir
        FileManager::createPath($this->formatMigrationsPath());
    }

    public function formatControllersPath() : string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->controllersDir;
    }

    public function formatRequestsPath() : string
    {
        /** @var Command $this */
        return FileManager::getModulePath($this, true) . $this->formRequestDir;
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

    public function formatGenPath()
    {
        return DirsInterface::GEN_DIR . PhpInterface::SLASH . date('Y-m-d') . PhpInterface::SLASH;
    }

    public function formatFuncTestsPath()
    {
        return DirsInterface::TESTS_DIR . PhpInterface::SLASH . DirsInterface::TESTS_FUNC_DIR;
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

    private function setIncludedTypes()
    {
        $this->types = $this->data[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
        if (empty($this->data[ApiInterface::RAML_KEY_USES]) === false) {
            $files = $this->data[ApiInterface::RAML_KEY_USES];
            foreach ($files as $file) {
                $this->files[] = $file;
                $fileData      = Yaml::parse(file_get_contents($file));
                $this->types   += $fileData[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
            }
        }
    }

    /**
     * @throws \rjapi\exceptions\DirectoryException
     */
    private function setGenHistory()
    {
        if (empty($this->options[ConsoleInterface::OPTION_NO_HISTORY])) {
            // create .gen dir to store raml history
            FileManager::createPath($this->formatGenPath());
            foreach ($this->files as $file) {
                $pathInfo = pathinfo($file);
                $dest     = $this->formatGenPath() . date('His') . PhpInterface::UNDERSCORE
                    . $pathInfo['filename'] . PhpInterface::DOT . $pathInfo['extension'];
                copy($file, $dest);
            }
        }
    }
}