<?php

namespace SoliDry\Blocks;

use SoliDry\Exceptions\AttributesException;
use SoliDry\Helpers\Console;
use SoliDry\ApiGenerator;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * @property ApiGenerator generator
 * @property string sourceCode
 */
abstract class MigrationsAbstract
{
    use ContentManager, MigrationsTrait;

    /**
     * @var int
     */
    public static int $counter = 10;

    public const PATTERN_TIME = 'Y_m_d_Hi';

    /**
     * @var array
     */
    private array $signedIntegerMap = [
        ModelsInterface::INT_DIGITS_TINY => ModelsInterface::MIGRATION_METHOD_TINY_INTEGER,
        ModelsInterface::INT_DIGITS_SMALL => ModelsInterface::MIGRATION_METHOD_SMALL_INTEGER,
        ModelsInterface::INT_DIGITS_MEDIUM => ModelsInterface::MIGRATION_METHOD_MEDIUM_INTEGER,
        ModelsInterface::INT_DIGITS_INT => ModelsInterface::MIGRATION_METHOD_INTEGER,
        ModelsInterface::INT_DIGITS_BIGINT => ModelsInterface::MIGRATION_METHOD_BIG_INTEGER,
    ];

    /**
     * @var array
     */
    private array $unsignedIntegerMap = [
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
                if (empty($attrVal[ApiInterface::RAML_TYPE]) === false) {
                    $this->setDescription($attrVal);
                    $type = $attrVal[ApiInterface::RAML_TYPE];
                    // create migration fields depending on Types
                    $this->setRowContent($attrVal, $type, $attrKey);
                } else {// non-standard Types aka enum
                    if (empty($attrVal[ApiInterface::RAML_ENUM]) === false) {
                        $this->setRowContent($attrVal, ApiInterface::RAML_ENUM, $attrKey);
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
                if (empty($attrVal[ApiInterface::RAML_TYPE]) === false) {
                    $this->setDescription($attrVal);
                    $type = $attrVal[ApiInterface::RAML_TYPE];
                    // create migration fields depending on Types
                    $this->setRowContent($attrVal, $type, $attrKey);
                } else {// non-standard Types aka enum
                    if (empty($attrVal[ApiInterface::RAML_ENUM]) === false) {
                        $this->setRowContent($attrVal, ApiInterface::RAML_ENUM, $attrKey);
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
        // create migration fields depending on Types
        switch ($type) {
            case ApiInterface::RAML_TYPE_STRING:
                $this->setStringRow($attrKey, $attrVal);
                break;
            case ApiInterface::RAML_TYPE_INTEGER:
                // create an auto_incremented primary key
                $this->setIntegerRow($attrKey, $attrVal, $type);
                break;
            case ApiInterface::RAML_TYPE_BOOLEAN:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_UTINYINT, $attrKey);
                break;
            case ApiInterface::RAML_TYPE_NUMBER:
                $this->setNumberRow($attrVal, $attrKey);
                break;
            case ApiInterface::RAML_ENUM:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_ENUM, $attrKey,
                    $this->getArrayParam($attrVal[ModelsInterface::MIGRATION_METHOD_ENUM]));
                break;
            case ApiInterface::RAML_DATE:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_DATE, $attrKey);
                break;
            case ApiInterface::RAML_TIME:
                $this->setRow(ModelsInterface::MIGRATION_METHOD_TIME, $attrKey);
                break;
            case ApiInterface::RAML_DATETIME:
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
        if (empty($attrVal[ApiInterface::RAML_FACETS][ApiInterface::RAML_INDEX]) === false) {
            $facets = $attrVal[ApiInterface::RAML_FACETS][ApiInterface::RAML_INDEX];
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
            foreach ($this->signedIntegerMap as $digits => $method) {
                $next = next($this->signedIntegerMap);
                if ($digits >= $max && ($next === false || ($next !== false && $max < key($this->signedIntegerMap)))) {
                    $this->setRow($method, $key);
                    break;
                }
            }
        } else {
            foreach ($this->unsignedIntegerMap as $digits => $method) {
                $next = next($this->unsignedIntegerMap);
                if ($digits >= $max && ($next === false || ($next !== false && $max < key($this->unsignedIntegerMap)))) {
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
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, ApiInterface::RAML_ID);
        $attrs = [
            strtolower($this->generator->objectName) . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID => $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ID]],
            $relationEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID                          => $this->generator->types[$this->generator->types[ucfirst($relationEntity)][ApiInterface::RAML_PROPS][ApiInterface::RAML_ID]],
        ];
        foreach ($attrs as $attrKey => $attrVal) {
            $this->setRowContent($attrVal, $attrVal[ApiInterface::RAML_TYPE], $attrKey);
        }
        $this->setRow(ModelsInterface::MIGRATION_METHOD_TIMESTAMPS, '', null, null, false);
    }

    private function getEntityAttributes()
    {
        $attrsArray = [ApiInterface::RAML_ID => $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ID]]] +
            $this->generator->types[$this->generator->objectProps[ApiInterface::RAML_ATTRS]][ApiInterface::RAML_PROPS];

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
        if ($type === ApiInterface::RAML_TYPE_INTEGER && empty($attrVal[ApiInterface::RAML_INTEGER_MAX]) === false) {
            if ($attrVal[ApiInterface::RAML_INTEGER_MAX] > ModelsInterface::ID_MAX_INCREMENTS) {
                $this->setRow(ModelsInterface::MIGRATION_METHOD_BIG_INCREMENTS, $attrKey);

                return;
            }
        }
        $this->setRow(ModelsInterface::MIGRATION_METHOD_INCREMENTS, $attrKey);
    }

    /**
     * Creates migration file with time mask
     *
     * @param string $migrationName
     */
    protected function createMigrationFile(string $migrationName): void
    {
        $migrationMask = date(self::PATTERN_TIME) . self::$counter;

        self::$counter += 2; // 2-step pace for pivots
        $file = $this->generator->formatMigrationsPath() . $migrationMask . PhpInterface::UNDERSCORE .
            $migrationName . PhpInterface::PHP_EXT;

        // if migration file with the same name occasionally exists we do not override it
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}