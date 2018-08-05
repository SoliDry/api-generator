<?php

namespace rjapi\blocks;

use rjapi\exceptions\AttributesException;
use rjapi\helpers\Console;
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
    use ContentManager, MigrationsTrait;

    public const PATTERN_TIME = 'd_m_Y_Hi';

    private $signedIntergerMap = [
        ModelsInterface::INT_DIGITS_TINY => ModelsInterface::MIGRATION_METHOD_TINY_INTEGER,
        ModelsInterface::INT_DIGITS_SMALL => ModelsInterface::MIGRATION_METHOD_SMALL_INTEGER,
        ModelsInterface::INT_DIGITS_MEDIUM => ModelsInterface::MIGRATION_METHOD_MEDIUM_INTEGER,
        ModelsInterface::INT_DIGITS_INT => ModelsInterface::MIGRATION_METHOD_INTEGER,
        ModelsInterface::INT_DIGITS_BIGINT => ModelsInterface::MIGRATION_METHOD_BIG_INTEGER,
    ];

    private $unsignedIntergerMap = [
        ModelsInterface::INT_DIGITS_TINY => ModelsInterface::MIGRATION_METHOD_UTINYINT,
        ModelsInterface::INT_DIGITS_SMALL => ModelsInterface::MIGRATION_METHOD_USMALLINT,
        ModelsInterface::INT_DIGITS_MEDIUM => ModelsInterface::MIGRATION_METHOD_UMEDIUMINT,
        ModelsInterface::INT_DIGITS_INT => ModelsInterface::MIGRATION_METHOD_UINT,
        ModelsInterface::INT_DIGITS_BIGINT => ModelsInterface::MIGRATION_METHOD_UBIGINT,
    ];

    /**
     *  Sets rows of migration with their description and options
     */
    protected function setRows(): void
    {
        $attrs = $this->getEntityAttributes();
        foreach ($attrs as $attrKey => $attrVal) {
            if (is_array($attrVal)) {
                if (empty($attrVal[RamlInterface::RAML_TYPE]) === false) {
                    $this->setDescription($attrVal);
                    $type = $attrVal[RamlInterface::RAML_TYPE];
                    // create migration fields depending on types
                    $this->setRowContent($attrVal, $type, $attrKey);
                } else {// non-standard types aka enum
                    if (empty($attrVal[RamlInterface::RAML_ENUM]) === false) {
                        $this->setRowContent($attrVal, RamlInterface::RAML_ENUM, $attrKey);
                    }
                }

                try {
                    $this->setIndex($attrVal, $attrKey);
                    $this->setCompositeIndex($attrVal);
                } catch (AttributesException $e) {
                    echo $e->getTraceAsString();
                }
            }
        }
        // created_at/updated_at created for every table
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS, '', null, null, false);
    }

    /**
     *  Sets rows of migration with their description and options
     * @param array $attrs
     */
    protected function setAddRows(array $attrs): void
    {
        foreach ($attrs as $attrKey => $attrVal) {
            if (is_array($attrVal)) {
                if (empty($attrVal[RamlInterface::RAML_TYPE]) === false) {
                    $this->setDescription($attrVal);
                    $type = $attrVal[RamlInterface::RAML_TYPE];
                    // create migration fields depending on types
                    $this->setRowContent($attrVal, $type, $attrKey);
                } else {// non-standard types aka enum
                    if (empty($attrVal[RamlInterface::RAML_ENUM]) === false) {
                        $this->setRowContent($attrVal, RamlInterface::RAML_ENUM, $attrKey);
                    }
                }
                try {
                    $this->setIndex($attrVal, $attrKey);
                    $this->setCompositeIndex($attrVal);
                } catch (AttributesException $e) {
                    echo $e->getTraceAsString();
                }
            }
        }
    }

    /**
     * Sets row content with opts
     * @param array $attrVal
     * @param string $type
     * @param string $attrKey
     */
    private function setRowContent(array $attrVal, string $type, string $attrKey): void
    {
        // create migration fields depending on types
        switch ($type) {
            case RamlInterface::RAML_TYPE_STRING:
                $this->setStringRow($attrKey, $attrVal);
                break;
            case RamlInterface::RAML_TYPE_INTEGER:
                // create an auto_incremented primary key
                $this->setIntegerRow($attrKey, $attrVal, $type);
                break;
            case RamlInterface::RAML_TYPE_BOOLEAN:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_UTINYINT, $attrKey);
                break;
            case RamlInterface::RAML_TYPE_NUMBER:
                $this->setNumberRow($attrVal, $attrKey);
                break;
            case RamlInterface::RAML_ENUM:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_ENUM, $attrKey,
                    $this->getArrayParam($attrVal[ModelsInterface::MIGRATION_METHOD_ENUM]));
                break;
            case RamlInterface::RAML_DATE:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_DATE, $attrKey);
                break;
            case RamlInterface::RAML_TIME:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_TIME, $attrKey);
                break;
            case RamlInterface::RAML_DATETIME:
                if ($attrKey === ModelsInterface::COLUMN_DEL_AT) {
                    $this->setRow(ModelsInterface::MIGRATION_METHOD_SOFT_DEL, '', null, null, false);
                } else {
                    $this->setRow(ModelsInterface::MIGRATION_METHOD_DATETIME, $attrKey);
                }
                break;
        }
    }

    /**
     * @param array $attrs
     */
    public function dropRows(array $attrs): void
    {
        foreach ($attrs as $attrKey => $attrVal) {
            $this->setRow(ModelsInterface::MIGRATION_DROP_COLUMN, $attrKey);
        }
    }

    /**
     *  Sets index for particular column if facets was declared
     * @param array $attrVal
     * @param string $attrKey
     *
     * @throws AttributesException
     */
    public function setIndex(array $attrVal, string $attrKey): void
    {
        if (empty($attrVal[RamlInterface::RAML_FACETS][RamlInterface::RAML_INDEX]) === false) {
            $facets = $attrVal[RamlInterface::RAML_FACETS][RamlInterface::RAML_INDEX];
            foreach ($facets as $k => $v) {
                switch ($v) {
                    case ModelsInterface::INDEX_TYPE_INDEX:
                        $this->setRow(ModelsInterface::INDEX_TYPE_INDEX, $attrKey, $this->quoteParam($k));
                        break;
                    case ModelsInterface::INDEX_TYPE_PRIMARY:
                        $this->setRow(ModelsInterface::INDEX_TYPE_PRIMARY, $attrKey, $this->quoteParam($k));
                        break;
                    case ModelsInterface::INDEX_TYPE_UNIQUE:
                        $this->setRow(ModelsInterface::INDEX_TYPE_UNIQUE, $attrKey, $this->quoteParam($k));
                        break;
                    case ModelsInterface::INDEX_TYPE_FOREIGN:
                        $this->setForeignIndex($facets, $attrKey, $k);
                        break;
                }
            }
        }
    }

    /**
     * @param string $key
     * @param int $max
     * @param bool $signed
     */
    private function setIntegerDigit(string $key, int $max = null, bool $signed = false): void
    {
        if ($signed) {
            foreach ($this->signedIntergerMap as $digits => $method) {
                $next = next($this->signedIntergerMap);
                if ($digits >= $max && ($next === false || ($next !== false && $max < key($this->signedIntergerMap)))) {
                    $this->setRow($method, $key);
                    break;
                }
            }
        } else {
            foreach ($this->unsignedIntergerMap as $digits => $method) {
                $next = next($this->unsignedIntergerMap);
                if ($digits >= $max && ($next === false || ($next !== false && $max < key($this->unsignedIntergerMap)))) {
                    $this->setRow($method, $key);
                    break;
                }
            }
        }
    }

    /**
     * @param $relationEntity
     */
    protected function setPivotRows($relationEntity): void
    {
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, RamlInterface::RAML_ID);
        $attrs = [
            strtolower($this->generator->objectName) . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID => $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ID]],
            $relationEntity . PhpInterface::UNDERSCORE . RamlInterface::RAML_ID => $this->generator->types[$this->generator->types[ucfirst($relationEntity)][RamlInterface::RAML_PROPS][RamlInterface::RAML_ID]],
        ];
        foreach ($attrs as $attrKey => $attrVal) {
            $this->setRowContent($attrVal, $attrVal[RamlInterface::RAML_TYPE], $attrKey);
        }
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS, '', null, null, false);
    }

    private function getEntityAttributes()
    {
        $attrsArray = [RamlInterface::RAML_ID => $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ID]]] +
            $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]][RamlInterface::RAML_PROPS];

        return $attrsArray;
    }

    /**
     * @param $attrVal
     * @param $attrKey
     * @param $type
     */
    private function setId($attrVal, $attrKey, $type): void
    {
        // set incremented id int
        if ($type === RamlInterface::RAML_TYPE_INTEGER && empty($attrVal[RamlInterface::RAML_INTEGER_MAX]) === false) {
            if ($attrVal[RamlInterface::RAML_INTEGER_MAX] > ModelsInterface::ID_MAX_INCREMENTS) {
                $this->setRow(ModelsInterface::MIGRATION_METHOD_BIG_INCREMENTS, $attrKey);

                return;
            }
        }
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, $attrKey);
    }

    /**
     * Creates migration file with time mask
     * @param string $migrationName
     */
    protected function createMigrationFile(string $migrationName): void
    {
        $migrationMask = date(self::PATTERN_TIME) . random_int(10, 99);
        $file = $this->generator->formatMigrationsPath() . $migrationMask . PhpInterface::UNDERSCORE .
            $migrationName . PhpInterface::PHP_EXT;

        // if migration file with the same name ocasionally exists we do not override it
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}