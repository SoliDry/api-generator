<?php

namespace rjapi\blocks;

use Raml\Method;
use rjapi\RJApiGenerator;
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

        if(empty($bodies[RJApiGenerator::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[RJApiGenerator::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['type'] === RJApiGenerator::RAML_TYPE_OBJECT
               &&
               !empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][RJApiGenerator::RAML_PROPS])
            )
            {
                $attributes =
                    $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data'][RJApiGenerator::RAML_PROPS]['relationships'][RJApiGenerator::RAML_PROPS];
            }

            if($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['type'] === RJApiGenerator::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][RJApiGenerator::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['relationships'][RJApiGenerator::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][RJApiGenerator::RAML_PROPS]))
                {
                    $attributes =
                        $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][RJApiGenerator::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['type'] === RJApiGenerator::RAML_TYPE_OBJECT)
            {
                $attributes       =
                    $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data'][RJApiGenerator::RAML_PROPS]['attributes'][RJApiGenerator::RAML_PROPS];
                $attributes['id'] =
                    $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data'][RJApiGenerator::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['type'] === RJApiGenerator::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['attributes'][RJApiGenerator::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['attributes'][RJApiGenerator::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][RJApiGenerator::RAML_PROPS]))
                {
                    $attributes       =
                        $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][RJApiGenerator::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[RJApiGenerator::RAML_PROPS]['data']['items'][0]['id'];
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