<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use Raml\Method;
use rjapi\extension\yii2\raml\controllers\TypesController;
use rjapi\extension\yii2\raml\exception\AttributesException;

class Methods
{
    private $method = null;

    public function __construct(Method $method)
    {
        $this->method = $method;
    }

    public function setCodeState(Method $method)
    {
        $this->method = $method;
    }

    public function getResponseMethodProperties()
    {
    }

    public function getMethodProperties($related = false)
    {
        $bodies     = $this->method->getBodies();
        $attributes = null;

        if(empty($bodies[TypesController::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[TypesController::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[TypesController::RAML_PROPS]['data']['type'] === TypesController::RAML_TYPE_OBJECT
               &&
               !empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['relationships'][TypesController::RAML_PROPS])
            )
            {
                $attributes =
                    $jsonBodyArr[TypesController::RAML_PROPS]['data'][TypesController::RAML_PROPS]['relationships'][TypesController::RAML_PROPS];
            }

            if($jsonBodyArr[TypesController::RAML_PROPS]['data']['type'] === TypesController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['relationships'][TypesController::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['relationships'][TypesController::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][TypesController::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][TypesController::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[TypesController::RAML_PROPS]['data']['type'] === TypesController::RAML_TYPE_OBJECT)
            {
                $attributes       =
                    $jsonBodyArr[TypesController::RAML_PROPS]['data'][TypesController::RAML_PROPS]['attributes'][TypesController::RAML_PROPS];
                $attributes['id'] =
                    $jsonBodyArr[TypesController::RAML_PROPS]['data'][TypesController::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[TypesController::RAML_PROPS]['data']['type'] === TypesController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['attributes'][TypesController::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['attributes'][TypesController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][TypesController::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][TypesController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[TypesController::RAML_PROPS]['data']['items'][0]['id'];
                }
            }
        }

        if($attributes === null)
        {
            throw new AttributesException('There hasn`t been set or not set correctly neither "attributes" nor "relationships" tag in schema.');
        }

        return $attributes;
    }
}