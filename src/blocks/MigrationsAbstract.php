<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

/**
 * @property RJApiGenerator generator
 * @property string         sourceCode
 */
abstract class MigrationsAbstract
{
    const PATTERN_TIME     = 'd_m_Y_Hi';

    protected function setRows()
    {
        // always create an auto_increment primary key - id
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, RamlInterface::RAML_ID);
        foreach($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
        [RamlInterface::RAML_PROPS] as $attrKey => $attrVal)
        {
            if(is_array($attrVal) && empty($attrVal[RamlInterface::RAML_TYPE]) === false)
            {
                $this->setDescription($attrVal);
                $type = $attrVal[RamlInterface::RAML_TYPE];
                switch ($type)
                {
                    case RamlInterface::RAML_TYPE_STRING:
                        $this->setRow(ModelsInterface::MIGRATION_METHOD_STRING, $attrKey);
                        break;
                    case RamlInterface::RAML_TYPE_INTEGER:
                        $this->setRow(ModelsInterface::MIGRATION_METHOD_INTEGER, $attrKey);
                        break;
                    case RamlInterface::RAML_TYPE_BOOLEAN:
                        $this->setRow(ModelsInterface::MIGRATION_METHOD_TINYINT, $attrKey);
                        break;
                    case RamlInterface::RAML_TYPE_DATETIME:
                        $this->setRow(ModelsInterface::MIGRATION_METHOD_DATETIME, $attrKey);
                        break;
                    // TODO: implement ENUM
//                        case RamlInterface::RAML_ENUM:
//                            $this->setRow(ModelsInterface::MIGRATION_METHOD_ENUM, $attrKey, );
//                            break;
                }
            }
        }
        // created_at/updated_at created for every table
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS);
    }

    protected function setPivotRows($relationEntity)
    {
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, RamlInterface::RAML_ID);
        $this->setRow(
            ModelsInterface::MIGRATION_METHOD_INTEGER, strtolower($this->generator->objectName)
                                                       . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID
        );
        $this->setRow(
            ModelsInterface::MIGRATION_METHOD_INTEGER, $relationEntity
                                                       . PhpEntitiesInterface::UNDERSCORE . RamlInterface::RAML_ID
        );
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS);
    }
}