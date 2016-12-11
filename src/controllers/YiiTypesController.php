<?php
namespace rjapi\controllers;

use rjapi\blocks\CustomsInterface;
use rjapi\blocks\DefaultInterface;
use rjapi\blocks\DirsInterface;
use rjapi\blocks\HTTPMethodsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;
use yii\console\Controller;

class YiiTypesController extends Controller implements DefaultInterface, PhpEntitiesInterface, HTTPMethodsInterface,
    RamlInterface, CustomsInterface, DirsInterface
{
    use ControllersTrait;
    
    private $forms = null;
    private $controllers = null;
    private $moduleObject = null;
    private $mappers = null;
    private $containers = null;
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
}