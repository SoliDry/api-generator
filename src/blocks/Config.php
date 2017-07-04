<?php
namespace rjapi\blocks;

use rjapi\controllers\BaseCommand;
use rjapi\exception\AttributesException;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\ModulesInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class Config
 *
 * @package rjapi\blocks
 */
class Config implements ConfigInterface
{
    use ContentManager, ConfigTrait;

    protected $sourceCode = '';
    /** @var BaseCommand generator */
    protected $generator = null;
    protected $className = null;

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

    public function create()
    {
        $this->setContent();
        // create config file
        $file      = $this->generator->formatConfigPath() .
            ModulesInterface::CONFIG_FILENAME . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        if($isCreated) {
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
        $this->setParam(ModulesInterface::KEY_NAME, ucfirst($this->generator->version));
        $this->setQueryParams();
        $this->setTrees();
        $this->setJwtContent();
        $this->setConfigEntities();
        $this->closeRoot();
    }

    private function setQueryParams()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS]) === false) {
            $queryParams = $this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS];
            $this->openEntity(ConfigInterface::QUERY_PARAMS);
            foreach($this->queryParams as $param) {
                if(empty($queryParams[$param][RamlInterface::RAML_KEY_DEFAULT]) === false) {
                    $this->setParam($param, $queryParams[$param][RamlInterface::RAML_KEY_DEFAULT], 2);
                }
            }
            $this->closeEntities();
        }
    }

    /**
     *  Sets JWT config array
     *  Ex.:
     *    'jwt'                  => [
     *      'enabled'  => true,
     *      'table'    => 'user',
     *      'activate' => 30,
     *      'expires'  => 3600,
     *    ],
     */
    private function setJwtContent()
    {
        foreach($this->generator->types as $objName => $objData) {
            if(in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                $excluded = false;
                foreach($this->generator->excludedSubtypes as $type) {
                    if(strpos($objName, $type) !== false) {
                        $excluded = true;
                    }
                }
                // if the type is among excluded - continue
                if($excluded === true) {
                    continue;
                }
                $this->setJwtOptions($objName);
            }
        }
    }

    private function setConfigEntities()
    {
        foreach($this->entityMethods as $entity => $method) {
            $this->openEntity($entity);
            foreach($this->generator->types as $objName => $objData) {
                if(in_array($objName, $this->generator->customTypes) === false) { // if this is not a custom type generate resources
                    $excluded = false;
                    foreach($this->generator->excludedSubtypes as $type) {
                        if(strpos($objName, $type) !== false) {
                            $excluded = true;
                        }
                    }
                    // if the type is among excluded - continue
                    if($excluded === true) {
                        continue;
                    }
                    $this->$method($objName);
                }
            }
            $this->closeEntities();
        }
    }

    /**
     * @param string $objName
     */
    private function setSpellOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal) && empty($propVal[RamlInterface::RAML_FACETS][ConfigInterface::SPELL_CHECK]) === false) {
                    // found FSM definition
                    $this->openSc($objName, $propKey);
                    $this->setParam(ConfigInterface::LANGUAGE, empty($propVal[RamlInterface::RAML_FACETS][ConfigInterface::SPELL_LANGUAGE])
                        ? ConfigInterface::DEFAULT_LANGUAGE
                        : $propVal[RamlInterface::RAML_FACETS][ConfigInterface::SPELL_LANGUAGE], 4);
                    $this->closeEntities();
                }
            }
        }
    }

    /**
     * @param string $objName
     * @throws AttributesException
     */
    private function setFsmOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal)) {// create fsm
                    if(empty($propVal[RamlInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE]) === false) {
                        // found FSM definition
                        $this->openFsm($objName, $propKey);
                        foreach($propVal[RamlInterface::RAML_FACETS][ConfigInterface::STATE_MACHINE] as $key => &$val) {
                            $this->setTabs(5);
                            $this->setArrayProperty($key, (array)$val);
                        }
                        $this->closeEntities();
                    }
                }
            }
        }
    }

    /**
     * @param string $objName
     */
    private function setBitMaskOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal)) {
                    if(empty($propVal[RamlInterface::RAML_FACETS][ConfigInterface::BIT_MASK]) === false) {
                        // found FSM definition
                        $this->openBitMask($objName, $propKey);
                        foreach($propVal[RamlInterface::RAML_FACETS][ConfigInterface::BIT_MASK] as $key => $val) {
                            $this->setParam($key, (int)$val, 5);
                        }
                        $this->closeEntities();
                    }
                }
            }
        }
    }

    /**
     * Sets jwt config options
     * @param string $objName
     */
    private function setJwtOptions(string $objName)
    {
        if(empty($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[$objName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal) && $propKey === CustomsInterface::CUSTOM_PROP_JWT) {// create jwt config setting
                    $this->openEntity(ConfigInterface::JWT);
                    $this->setParam(ConfigInterface::ENABLED, PhpInterface::PHP_TYPES_BOOL_TRUE, 2);
                    $this->setParam(ModelsInterface::MIGRATION_TABLE, MigrationsHelper::getTableName($objName), 2);
                    $this->setParam(ConfigInterface::ACTIVATE, ConfigInterface::DEFAULT_ACTIVATE, 2);
                    $this->setParam(ConfigInterface::EXPIRES, ConfigInterface::DEFAULT_EXPIRES, 2);
                    $this->closeEntities();
                }
            }
        }
    }

    /**
     *  Sets config trees structure
     */
    private function setTrees()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][RamlInterface::RAML_PROPS]) === false) {
            foreach($this->generator->types[CustomsInterface::CUSTOM_TYPES_TREES][RamlInterface::RAML_PROPS] as $propKey => $propVal) {
                if(is_array($propVal) && empty($this->generator->types[ucfirst($propKey)]) === false) {
                    // ensure that there is a type of propKey ex.: Menu with parent_id field set
                    $this->openEntity(ConfigInterface::TREES);
                    $this->setParamDefault($propKey, $propVal[RamlInterface::RAML_KEY_DEFAULT]);
                    $this->closeEntities();
                }
            }
        }
    }
}