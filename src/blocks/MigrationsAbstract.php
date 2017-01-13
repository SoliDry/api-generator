<?php

namespace rjapi\blocks;

use rjapi\RJApiGenerator;

/**
 * @property RJApiGenerator generator
 * @property string         sourceCode
 */
abstract class MigrationsAbstract
{
    const PATTERN_TIME = 'd_m_Y_Hi';

    protected function setRows()
    {
//        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, RamlInterface::RAML_ID);
        $attrs = $this->getEntityAttributes();
        foreach($attrs as $attrKey => $attrVal)
        {
            if(is_array($attrVal) && empty($attrVal[RamlInterface::RAML_TYPE]) === false)
            {
                $this->setDescription($attrVal);
                $type = $attrVal[RamlInterface::RAML_TYPE];
                if($attrKey === RamlInterface::RAML_ID)
                {
                    // create an auto_increment primary key - id
                    $this->setId($attrVal, $attrKey, $type);
                    continue;
                }
                // create migration fields depending on types
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
        // P = 2T/2
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

    private function getEntityAttributes()
    {
        $attrsArray[RamlInterface::RAML_ID] = $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ID]];
        array_push($attrsArray, $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]][RamlInterface::RAML_PROPS]);
        return $attrsArray;
    }

    private function setId($attrVal, $attrKey, $type)
    {
        // set incremented id int
        if($type === RamlInterface::RAML_TYPE_INTEGER && empty($attrVal[RamlInterface::RAML_INTEGER_MAX]) === false)
        {
            if($attrVal[RamlInterface::RAML_INTEGER_MAX] > ModelsInterface::ID_MAX_INCREMENTS)
            {
                $this->setRow(ModelsInterface::MIGRATION_METHOD_BIG_INCREMENTS, $attrKey);

                return;
            }
        }
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, $attrKey);
    }
}