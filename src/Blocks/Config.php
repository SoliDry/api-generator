<?php

namespace SoliDry\Blocks;

use SoliDry\Controllers\BaseCommand;
use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\Console;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;
use SoliDry\Types\CustomsInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\ModulesInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class Config
 *
 * @package SoliDry\Blocks
 */
class Config implements ConfigInterface
{
    use ContentManager, ConfigTrait;

    protected $sourceCode = '';
    /** @var BaseCommand generator */
    protected $generator;
    protected $className;

    private $queryParams = [
        ModelsInterface::PARAM_LIMIT,
        ModelsInterface::PARAM_SORT,
        ModelsInterface::PARAM_PAGE,
        JSONApiInterface::PARAM_ACCESS_TOKEN,
    ];

    private $entityMethods = [
        ConfigInterface::STATE_MACHINE => ConfigInterface::STATE_MACHINE_METHOD,
        ConfigInterface::SPELL_CHECK   => ConfigInterface::SPELL_CHECK_METHOD,
        ConfigInterface::BIT_MASK      => ConfigInterface::BIT_MASK_METHOD,
        ConfigInterface::CACHE         => ConfigInterface::CACHE_METHOD,
    ];

    /**
     * Config constructor.
     * @param $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    /**
     *  Creates common config file
     */
    public function create()
    {
        $this->setContent();
        // create config file
        $file      = $this->generator->formatConfigPath() .
            ModulesInterface::CONFIG_FILENAME . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     * Constructs the config structure
     */
    private function setContent()
    {
        $this->setTag();
        $this->openRoot();
        $this->setParam(ModulesInterface::KEY_NAME, ApiInterface::RAML_TYPE_STRING, ucfirst($this->generator->version));
        $this->setParam(ConfigInterface::ATTRIBUTES_CASE, ApiInterface::RAML_TYPE_STRING, ConfigInterface::DEFAULT_CASE);
        $this->setQueryParams();
        $this->setTrees();
        $this->setJwtContent();
        $this->setConfigEntities();
        $this->closeRoot();
    }

    /**
     *  Sets query params e.g.:
     *  - limit
     *  - sort
     *  - access
     */
    private function setQueryParams()
    {
        if (empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][ApiInterface::RAML_PROPS]) === false) {
            $queryParams = $this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][ApiInterface::RAML_PROPS];
            $this->openEntity(ConfigInterface::QUERY_PARAMS);
            foreach ($this->queryParams as $param) {
                if (empty($queryParams[$param][ApiInterface::RAML_KEY_DEFAULT]) === false) {
                    $this->setParam($param, $queryParams[$param][ApiInterface::RAML_TYPE], $queryParams[$param][ApiInterface::RAML_KEY_DEFAULT], 2);
                }
            }
            $this->closeEntities();
        }
    }

    /**
     *  Sets JWT config array
     * @example
     *    'jwt'                  => [
     *      'enabled'  => true,
     *      'table'    => 'user',
     *      'activate' => 30,
     *      'expires'  => 3600,
     *    ],
     */
    private function setJwtContent()
    {
        foreach ($this->generator->types as $objName => $objData) {
            if (in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                $excluded = false;
                foreach ($this->generator->excludedSubtypes as $type) {
                    if (strpos($objName, $type) !== false) {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if ($excluded === true) {
                    continue;
                }
                $this->setJwtOptions($objName);
            }
        }
    }

    /**
     * Sets all global config entities by crawling yaml config
     *
     * @uses setFsmOptions
     * @uses setSpellOptions
     * @uses setBitMaskOptions
     * @uses setCacheOptions
     */
    private function setConfigEntities()
    {
        foreach ($this->entityMethods as $entity => $methodName) {
            $this->openEntity($entity);
            foreach ($this->generator->types as $objName => $objData) {
                if (in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                    $excluded = false;
                    foreach ($this->generator->excludedSubtypes as $type) {
                        if (strpos($objName, $type) !== false) {
                            $excluded = true;
                        }
                    }
                    // if the type is among excluded - continue
                    if ($excluded === true) {
                        continue;
                    }
                    $this->setOptions($objName, $methodName);
                }
            }
            $this->closeEntities();
        }
    }

    /**
     * @param string $objName
     * @param string $methodName
     */
    private function setOptions(string $objName, string $methodName)
    {
        if (empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][ApiInterface::RAML_PROPS]) === false) {
            foreach ($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][ApiInterface::RAML_PROPS] as $propKey => $propVal) {
                $this->$methodName($objName, $propKey, $propVal);
            }
        }
    }

    /**
     * Sets cache config enabled option true to activate caching mechanism
     * @param string $objName
     */
    private function setCacheOptions(string $objName)
    {
        if (empty($this->generator->types[$objName][ApiInterface::RAML_PROPS][ConfigInterface::CACHE][ApiInterface::RAML_TYPE]) === false
            && $this->generator->types[$objName][ApiInterface::RAML_PROPS][ConfigInterface::CACHE][ApiInterface::RAML_TYPE] === CustomsInterface::CUSTOM_TYPE_REDIS) {
            $this->openCache($objName);
            foreach ($this->generator->types[$objName][ApiInterface::RAML_PROPS][ConfigInterface::CACHE][ApiInterface::RAML_PROPS] as $prop => $value) {
                $this->setParam($prop, $value[ApiInterface::RAML_TYPE], $value[ApiInterface::RAML_KEY_DEFAULT], 3);
            }
            $this->closeEntity(2, true);
            // unset cache to prevent doubling
            unset($this->generator->types[$objName][ApiInterface::RAML_PROPS][ConfigInterface::CACHE]);
        }
    }

    /**
     * @param string $objName
     * @param string $propKey
     * @param $propVal
     */
    private function setSpellOptions(string $objName, string $propKey, $propVal)
    {
        if (is_array($propVal) && empty($propVal[ApiInterface::RAML_FACETS][ConfigInterface::SPELL_CHECK]) === false) {
            // found FSM definition
            $this->openSc($objName, $propKey);
            $this->setParam(ConfigInterface::LANGUAGE, ApiInterface::RAML_TYPE_STRING, empty($propVal[ApiInterface::RAML_FACETS][ConfigInterface::SPELL_LANGUAGE])
                ? ConfigInterface::DEFAULT_LANGUAGE
                : $propVal[ApiInterface::RAML_FACETS][ConfigInterface::SPELL_LANGUAGE], 4);
            $this->closeEntities();
        }
    }

    /**
     * @param string $objName
     * @param string $propKey
     * @param $propVal
     */
    private function setFsmOptions(string $objName, string $propKey, $propVal)
    {
        if (is_array($propVal)) {// create fsm
            if (empty($propVal[ApiInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE]) === false) {
                // found FSM definition
                $this->openFsm($objName, $propKey);
                foreach ($propVal[ApiInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE] as $key => &$val) {
                    $this->setTabs(5);
                    $this->setArrayProperty($key, (array)$val);
                }
                $this->closeEntities();
            }
        }
    }

    /**
     * @param string $objName
     * @param string $propKey
     * @param $propVal
     * @example
     * 'bit_mask'=> [
     *  'user'=> [
     *       'permissions'=> [
     *       'enabled' => true,
     *       'flags'=> [
     *           'publisher' => 1,
     *           'editor' => 2,
     *           'manager' => 4,
     *           'photo_reporter' => 8,
     *           'admin' => 16,
     *           ],
     *       ],
     *     ],
     *   ],
     */
    private function setBitMaskOptions(string $objName, string $propKey, $propVal)
    {
        if (is_array($propVal)) {
            if (empty($propVal[ApiInterface::RAML_FACETS][ConfigInterface::BIT_MASK]) === false) {
                // found FSM definition
                $this->openBitMask($objName, $propKey);
                foreach ($propVal[ApiInterface::RAML_FACETS][ConfigInterface::BIT_MASK] as $key => $val) {
                    $this->setParam($key, ApiInterface::RAML_TYPE_INTEGER, $val, 5);
                }
                $this->closeEntities();
            }
        }
    }

    /**
     * Sets jwt config options
     * @param string $objName
     * @example
     *  'jwt'=> [
     *   'enabled' => true,
     *   'table' => 'user',
     *   'activate' => 30,
     *   'expires' => 3600,
     *  ],
     */
    private function setJwtOptions(string $objName)
    {
        if (empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][ApiInterface::RAML_PROPS]) === false) {
            foreach ($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][ApiInterface::RAML_PROPS] as $propKey => $propVal) {
                if (is_array($propVal) && $propKey === CustomsInterface::CUSTOM_PROP_JWT) {// create jwt config setting
                    $this->openEntity(ConfigInterface::JWT);
                    $this->setParam(ConfigInterface::ENABLED, ApiInterface::RAML_TYPE_BOOLEAN, PhpInterface::PHP_TYPES_BOOL_TRUE, 2);
                    $this->setParam(ModelsInterface::MIGRATION_TABLE, ApiInterface::RAML_TYPE_STRING, MigrationsHelper::getTableName($objName), 2);
                    $this->setParam(ConfigInterface::ACTIVATE, ApiInterface::RAML_TYPE_INTEGER, ConfigInterface::DEFAULT_ACTIVATE, 2);
                    $this->setParam(ConfigInterface::EXPIRES, ApiInterface::RAML_TYPE_INTEGER, ConfigInterface::DEFAULT_EXPIRES, 2);
                    $this->closeEntities();
                }
            }
        }
    }

    /**
     *  Sets config trees structure
     * @example
     *   'trees'=> [
     *       'menu' => true,
     *   ],
     */
    private function setTrees()
    {
        if (empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][ApiInterface::RAML_PROPS]) === false) {
            foreach ($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][ApiInterface::RAML_PROPS] as $propKey => $propVal) {
                if (is_array($propVal) && empty($this->generator->types[ucfirst($propKey)]) === false) {
                    // ensure that there is a type of propKey ex.: Menu with parent_id field set
                    $this->openEntity(ConfigInterface::TREES);
                    $this->setParamDefault($propKey, $propVal[ApiInterface::RAML_KEY_DEFAULT]);
                    $this->closeEntities();
                }
            }
        }
    }
}