<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * @property RJApiGenerator generator
 * @property string sourceCode
 */
abstract class FormRequestModel
{
    use ContentManager;

    const CHECK_MANY_BRACKETS = '[]';

    private $legalTypes = [
        RamlInterface::RAML_TYPE_DATETIME,
        RamlInterface::RAML_TYPE_STRING,
        RamlInterface::RAML_TYPE_INTEGER,
        RamlInterface::RAML_TYPE_BOOLEAN,
        RamlInterface::RAML_TYPE_ARRAY,
    ];

    private $excludedKeys = [
        RamlInterface::RAML_KEY_DESCRIPTION,
        RamlInterface::RAML_KEY_DEFAULT,
    ];

    protected function setPropertyFilters()
    {
        $attrCnt =
            count($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]][RamlInterface::RAML_PROPS]);
        foreach($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
                [RamlInterface::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->setDescription($attrVal);
                $this->openRule($attrKey);
                $cnt = count($attrVal);
                $this->setFilters($attrVal, $cnt);
                $this->closeRule();
                if($attrCnt > 0)
                {
                    $this->sourceCode .= PHP_EOL;
                }
            }
        }
    }

    /**
     * @param array $attrVal
     * @param int $cnt
     */
    private function setFilters(array $attrVal, int $cnt)
    {
        foreach($attrVal as $k => $v)
        {
            --$cnt;
            if($k === RamlInterface::RAML_KEY_REQUIRED && (bool)$v === false)
            {
                continue;
            }
            $this->setRequired($k, $v);
            $this->setType($k, $v);
            $this->setEnum($k, $v);
            $this->setPattern($k, $v);
            $this->setMinMax($k, $v);
            if($cnt > 0 && in_array($k, $this->excludedKeys) === false)
            {
                $this->sourceCode .= PhpInterface::PIPE;
            }
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     */
    private function setRequired(string $k, $v)
    {
        if($k === RamlInterface::RAML_KEY_REQUIRED && (bool)$v === true)
        {
            $this->sourceCode .= RamlInterface::RAML_KEY_REQUIRED;
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     */
    private function setPattern(string $k, $v)
    {
        if($k === RamlInterface::RAML_PATTERN)
        {
            $this->sourceCode .= ModelsInterface::LARAVEL_FILTER_REGEX . PhpInterface::COLON . $v;
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     */
    private function setEnum(string $k, $v)
    {
        if($k === RamlInterface::RAML_ENUM)
        {
            $this->sourceCode .= ModelsInterface::LARAVEL_FILTER_ENUM . PhpInterface::COLON . implode(PhpInterface::COMMA, $v);
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     */
    private function setType(string $k, $v)
    {
        if($k === RamlInterface::RAML_TYPE && in_array($v, $this->legalTypes))
        {
            $this->sourceCode .= $v;
        }
    }

    /**
     * @param string $k
     * @param mixed $v
     */
    private function setMinMax(string $k, $v)
    {
        if($k === RamlInterface::RAML_STRING_MIN || $k === RamlInterface::RAML_INTEGER_MIN)
        {
            $this->sourceCode .= ModelsInterface::LARAVEL_FILTER_MIN . PhpInterface::COLON . $v;
        }
        else if($k === RamlInterface::RAML_STRING_MAX || $k === RamlInterface::RAML_INTEGER_MAX)
        {
            $this->sourceCode .= ModelsInterface::LARAVEL_FILTER_MAX . PhpInterface::COLON . $v;
        }
    }
}