<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

/**
 * @property RJApiGenerator generator
 * @property string sourceCode
 */
abstract class MigrationsAbstract
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

    protected function setRows()
    {
        foreach ($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
                 [RamlInterface::RAML_PROPS] as $attrKey => $attrVal) {
            if (is_array($attrVal)) {
                foreach ($attrVal as $k => $v) {
                    if (empty($attrVal[RamlInterface::RAML_TYPE]) === false) {
                        
                    }
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
}