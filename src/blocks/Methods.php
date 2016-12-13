<?php

namespace rjapi\blocks;

use Raml\Method;
use rjapi\controllers\YiiRJApiGenerator;
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

        if(empty($bodies[YiiRJApiGenerator::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[YiiRJApiGenerator::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['type'] === YiiRJApiGenerator::RAML_TYPE_OBJECT
               &&
               !empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][YiiRJApiGenerator::RAML_PROPS])
            )
            {
                $attributes =
                    $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data'][YiiRJApiGenerator::RAML_PROPS]['relationships'][YiiRJApiGenerator::RAML_PROPS];
            }

            if($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['type'] === YiiRJApiGenerator::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][YiiRJApiGenerator::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][YiiRJApiGenerator::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][YiiRJApiGenerator::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][YiiRJApiGenerator::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['type'] === YiiRJApiGenerator::RAML_TYPE_OBJECT)
            {
                $attributes       =
                    $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data'][YiiRJApiGenerator::RAML_PROPS]['attributes'][YiiRJApiGenerator::RAML_PROPS];
                $attributes['id'] =
                    $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data'][YiiRJApiGenerator::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['type'] === YiiRJApiGenerator::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['attributes'][YiiRJApiGenerator::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['attributes'][YiiRJApiGenerator::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][YiiRJApiGenerator::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][YiiRJApiGenerator::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[YiiRJApiGenerator::RAML_PROPS]['data']['items'][0]['id'];
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