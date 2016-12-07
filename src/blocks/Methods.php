<?php

namespace rjapi\extension\yii2\raml\blocks;

use Raml\Method;
use rjapi\extension\yii2\raml\controllers\SchemaController;
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
        $bodies = $this->method->getBodies();
        $attributes = null;

        if(empty($bodies[SchemaController::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[SchemaController::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[SchemaController::RAML_PROPS]['data']['type'] === SchemaController::RAML_TYPE_OBJECT
               && !empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['relationships'][SchemaController::RAML_PROPS])
            )
            {
                $attributes = $jsonBodyArr[SchemaController::RAML_PROPS]['data'][SchemaController::RAML_PROPS]['relationships'][SchemaController::RAML_PROPS];
            }

            if($jsonBodyArr[SchemaController::RAML_PROPS]['data']['type'] === SchemaController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['relationships'][SchemaController::RAML_PROPS]))
                {
                    $attributes = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['relationships'][SchemaController::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][SchemaController::RAML_PROPS]))
                {
                    $attributes = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][SchemaController::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[SchemaController::RAML_PROPS]['data']['type'] === SchemaController::RAML_TYPE_OBJECT)
            {
                $attributes       = $jsonBodyArr[SchemaController::RAML_PROPS]['data'][SchemaController::RAML_PROPS]['attributes'][SchemaController::RAML_PROPS];
                $attributes['id'] = $jsonBodyArr[SchemaController::RAML_PROPS]['data'][SchemaController::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[SchemaController::RAML_PROPS]['data']['type'] === SchemaController::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['attributes'][SchemaController::RAML_PROPS]))
                {
                    $attributes       = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['attributes'][SchemaController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][SchemaController::RAML_PROPS]))
                {
                    $attributes       = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][SchemaController::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[SchemaController::RAML_PROPS]['data']['items'][0]['id'];
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