<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

/**
 * @property RJApiGenerator generator
 * @property string sourceCode
 */
abstract class FormRequestModel
{
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
        foreach ($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
                 [RamlInterface::RAML_PROPS] as $attrKey => $attrVal) {
            --$attrCnt;
            // determine attr
            if (is_array($attrVal)) {
                $this->setDescription($attrVal);
                $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
                    PhpEntitiesInterface::TAB_PSR4
                    . PhpEntitiesInterface::DOUBLE_QUOTES . $attrKey . PhpEntitiesInterface::DOUBLE_QUOTES
                    . PhpEntitiesInterface::SPACE
                    . PhpEntitiesInterface::DOUBLE_ARROW .
                    PhpEntitiesInterface::SPACE;

                $this->sourceCode .= PhpEntitiesInterface::DOUBLE_QUOTES;
                $cnt = count($attrVal);
                $this->setFilters($attrVal, $cnt);

                $this->sourceCode .= PhpEntitiesInterface::DOUBLE_QUOTES . PhpEntitiesInterface::COMMA;
                if ($attrCnt > 0) {
                    $this->sourceCode .= PHP_EOL;
                }
            }
        }
    }

    /**
     * @param array $attrVal
     */
    private function setDescription(array $attrVal)
    {
        foreach ($attrVal as $k => $v) {
            if ($k === RamlInterface::RAML_KEY_DESCRIPTION) {
                $this->setTabs(3);
                $this->setComment($v);
            }
        }
    }

    /**
     * @param array $attrVal
     * @param int $cnt
     */
    private function setFilters(array $attrVal, int $cnt)
    {
        foreach ($attrVal as $k => $v) {
            --$cnt;
            if ($k === RamlInterface::RAML_KEY_REQUIRED && (bool)$v === false) {
                continue;
            }
            if ($k === RamlInterface::RAML_KEY_REQUIRED && (bool)$v === true) {
                $this->sourceCode .= RamlInterface::RAML_KEY_REQUIRED;
            }
            if ($k === RamlInterface::RAML_TYPE && in_array($v, $this->legalTypes)) {
                $this->sourceCode .= $v;
            }
            if ($k === RamlInterface::RAML_ENUM) {
                $this->sourceCode .= 'in:' . implode(',', $v);
            }
            if ($k === RamlInterface::RAML_PATTERN) {
                $this->sourceCode .= 'regex:' . $v;
            }
            if ($k === RamlInterface::RAML_STRING_MIN) {
                $this->sourceCode .= 'min:' . $v;
            }
            if ($k === RamlInterface::RAML_STRING_MAX) {
                $this->sourceCode .= 'max:' . $v;
            }
            if ($k === RamlInterface::RAML_INTEGER_MIN) {
                $this->sourceCode .= 'min:' . $v;
            }
            if ($k === RamlInterface::RAML_INTEGER_MAX) {
                $this->sourceCode .= 'max:' . $v;
            }
            // TODO: make prepared errors, probably not here
//            if(isset($attrVal['errorMessage']))
//            {
//                $this->sourceCode .= ', "message" => "' . $attrVal['errorMessage'] . '"';
//            }
            if ($cnt > 0 && in_array($k, $this->excludedKeys) === false) {
                $this->sourceCode .= PhpEntitiesInterface::PIPE;
            }
        }
    }
}