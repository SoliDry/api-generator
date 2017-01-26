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
abstract class MigrationsAbstract
{
    const PATTERN_TIME = 'd_m_Y_Hi';

    private $signedIntergerMap = [
        ModelsInterface::INT_DIGITS_TINY   => ModelsInterface::MIGRATION_METHOD_TINY_INTEGER,
        ModelsInterface::INT_DIGITS_SMALL  => ModelsInterface::MIGRATION_METHOD_SMALL_INTEGER,
        ModelsInterface::INT_DIGITS_MEDIUM => ModelsInterface::MIGRATION_METHOD_MEDIUM_INTEGER,
        ModelsInterface::INT_DIGITS_INT    => ModelsInterface::MIGRATION_METHOD_INTEGER,
        ModelsInterface::INT_DIGITS_BIGINT => ModelsInterface::MIGRATION_METHOD_BIG_INTEGER,
    ];

    private $unsignedIntergerMap = [
        ModelsInterface::INT_DIGITS_TINY   => ModelsInterface::MIGRATION_METHOD_UTINYINT,
        ModelsInterface::INT_DIGITS_SMALL  => ModelsInterface::MIGRATION_METHOD_USMALLINT,
        ModelsInterface::INT_DIGITS_MEDIUM => ModelsInterface::MIGRATION_METHOD_UMEDIUMINT,
        ModelsInterface::INT_DIGITS_INT    => ModelsInterface::MIGRATION_METHOD_UINT,
        ModelsInterface::INT_DIGITS_BIGINT => ModelsInterface::MIGRATION_METHOD_UBIGINT,
    ];

    /**
     *  Sets rows of migration with their description and options
     */
    protected function setRows()
    {
        $attrs = $this->getEntityAttributes();
        foreach($attrs as $attrKey => $attrVal)
        {
            if(is_array($attrVal))
            {
                if(empty($attrVal[RamlInterface::RAML_TYPE]) === false)
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
                    $this->setRowContent($attrVal, $type, $attrKey);
                }
                else
                {// non-standard types aka enum
                    if(empty($attrVal[RamlInterface::RAML_ENUM]) === false)
                    {
                        $this->setRowContent($attrVal, RamlInterface::RAML_ENUM, $attrKey);
                    }
                }
            }
        }
        // created_at/updated_at created for every table
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS);
    }

    /**
     * Sets row content with opts
     * @param array $attrVal
     * @param string $type
     * @param string $attrKey
     */
    private function setRowContent(array $attrVal, string $type, string $attrKey)
    {
        // create migration fields depending on types
        switch($type)
        {
            case RamlInterface::RAML_TYPE_STRING:
                $length = empty($attrVal[RamlInterface::RAML_STRING_MAX]) ? null : $attrVal[RamlInterface::RAML_STRING_MAX];
                $build = empty($attrVal[RamlInterface::RAML_KEY_DEFAULT]) ? null : [RamlInterface::RAML_KEY_DEFAULT
                                                                                    => PhpInterface::QUOTES
                    . $attrVal[RamlInterface::RAML_KEY_DEFAULT] . PhpInterface::QUOTES];
                $this->setRow(ModelsInterface::MIGRATION_METHOD_STRING, $attrKey, $length, $build);
                break;
            case RamlInterface::RAML_TYPE_INTEGER:
                $min = empty($attrVal[RamlInterface::RAML_INTEGER_MIN]) ? null : $attrVal[RamlInterface::RAML_INTEGER_MIN];
                $max = empty($attrVal[RamlInterface::RAML_INTEGER_MAX]) ? null : $attrVal[RamlInterface::RAML_INTEGER_MAX];
                $this->setIntegerDigit($attrKey, $max, ($min >= 0) ? false : true);
                break;
            case RamlInterface::RAML_TYPE_BOOLEAN:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_UTINYINT, $attrKey);
                break;
            case RamlInterface::RAML_TYPE_DATETIME:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_DATETIME, $attrKey);
                break;
            case RamlInterface::RAML_TYPE_NUMBER:
                if(empty($attrVal[RamlInterface::RAML_TYPE_FORMAT]) === false
                    && ($attrVal[RamlInterface::RAML_TYPE_FORMAT] === ModelsInterface::MIGRATION_METHOD_DOUBLE
                        || $attrVal[RamlInterface::RAML_TYPE_FORMAT] === ModelsInterface::MIGRATION_METHOD_FLOAT)
                )
                {
                    $max = empty($attrVal[RamlInterface::RAML_INTEGER_MAX]) ? PhpInterface::PHP_TYPES_ARRAY : $attrVal[RamlInterface::RAML_INTEGER_MAX];
                    $min = empty($attrVal[RamlInterface::RAML_INTEGER_MIN]) ? PhpInterface::PHP_TYPES_ARRAY : $attrVal[RamlInterface::RAML_INTEGER_MIN];
                    $this->setRow($attrVal[RamlInterface::RAML_TYPE_FORMAT], $attrKey, $max . PhpInterface::COMMA
                        . PhpInterface::SPACE . $min);
                }
                break;
            case RamlInterface::RAML_ENUM:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_ENUM, $attrKey,
                    PhpInterface::OPEN_BRACKET . PhpInterface::DOUBLE_QUOTES . implode(PhpInterface::DOUBLE_QUOTES . PhpInterface::COMMA
                        . PhpInterface::DOUBLE_QUOTES, $attrVal[ModelsInterface::MIGRATION_METHOD_ENUM]) . PhpInterface::DOUBLE_QUOTES
                    . PhpInterface::CLOSE_BRACKET);
                break;
            case RamlInterface::RAML_DATE:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_DATE, $attrKey);
                break;
            case RamlInterface::RAML_TIME:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_TIME, $attrKey);
                break;
        }
    }

    /**
     * @param string $key
     * @param int $max
     * @param bool $signed
     */
    private function setIntegerDigit(string $key, int $max = null, bool $signed = false)
    {
        if($signed)
        {
            foreach($this->signedIntergerMap as $digits => $method)
            {
                $next = next($this->signedIntergerMap);
                if($digits >= $max && ($next === false || ($next !== false && $max < key($this->signedIntergerMap))))
                {
                    $this->setRow($method, $key);
                    break;
                }
            }
        }
        else
        {
            foreach($this->unsignedIntergerMap as $digits => $method)
            {
                $next = next($this->unsignedIntergerMap);
                if($digits >= $max && ($next === false || ($next !== false && $max < key($this->unsignedIntergerMap))))
                {
                    $this->setRow($method, $key);
                    break;
                }
            }
        }
    }

    protected function setPivotRows($relationEntity)
    {
        // P = 2T/2
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, RamlInterface::RAML_ID);
        $this->setRow(
            ModelsInterface::MIGRATION_METHOD_INTEGER, strtolower($this->generator->objectName)
            . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID
        );
        $this->setRow(
            ModelsInterface::MIGRATION_METHOD_INTEGER, $relationEntity
            . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID
        );
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS);
    }

    private function getEntityAttributes()
    {
        $attrsArray = [RamlInterface::RAML_ID => $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ID]]] +
            $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]][RamlInterface::RAML_PROPS];

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