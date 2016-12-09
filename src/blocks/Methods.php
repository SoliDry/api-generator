<?php

namespace rjapi\blocks;

use Raml\Method;
use rjapi\controllers\YiiTypesController;
use rjapi\exception\AttributesException;

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

        if(empty($bodies[YiiTypesController::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[YiiTypesController::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['type'] === YiiTypesController::RAML_TYPE_OBJECT
               &&
               !empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['relationships'][YiiTypesController::RAML_PROPS])
            )
            {
                $attributes =
                    $jsonBodyArr[YiiTypesController::RAML_PROPS]['data'][YiiTypesController::RAML_PROPS]['relationships'][YiiTypesController::RAML_PROPS];
            }

            if($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['type'] === YiiTypesController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['relationships'][YiiTypesController::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['relationships'][YiiTypesController::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][YiiTypesController::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][YiiTypesController::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['type'] === YiiTypesController::RAML_TYPE_OBJECT)
            {
                $attributes       =
                    $jsonBodyArr[YiiTypesController::RAML_PROPS]['data'][YiiTypesController::RAML_PROPS]['attributes'][YiiTypesController::RAML_PROPS];
                $attributes['id'] =
                    $jsonBodyArr[YiiTypesController::RAML_PROPS]['data'][YiiTypesController::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['type'] === YiiTypesController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['attributes'][YiiTypesController::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['attributes'][YiiTypesController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][YiiTypesController::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][YiiTypesController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[YiiTypesController::RAML_PROPS]['data']['items'][0]['id'];
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