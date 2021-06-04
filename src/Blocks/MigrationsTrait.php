<?php

namespace SoliDry\Blocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SoliDry\Exceptions\AttributesException;
use SoliDry\Helpers\Classes;
use SoliDry\Types\ErrorsInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

trait MigrationsTrait
{
    /**
     * @param string $entity
     * @param string $schemaMethod
     * @throws \ReflectionException
     */
    public function openSchema(string $entity, $schemaMethod = ModelsInterface::MIGRATION_CREATE) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . ModelsInterface::MIGRATION_SCHEMA
            . PhpInterface::DOUBLE_COLON . $schemaMethod
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES . strtolower($entity) . PhpInterface::QUOTES
            . PhpInterface::COMMA . PhpInterface::SPACE . PhpInterface::PHP_FUNCTION
            . PhpInterface::OPEN_PARENTHESES . Classes::getName(Blueprint::class) . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN
            . ModelsInterface::MIGRATION_TABLE . PhpInterface::CLOSE_PARENTHESES . PhpInterface::SPACE;

        $this->sourceCode .= PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeSchema() : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACE . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Creates table migration schema
     *
     * @param string $method
     * @param string $entity
     * @throws \ReflectionException
     */
    public function createSchema(string $method, string $entity) : void
    {
        $this->sourceCode .= $this->setTabs(2) . Classes::getName(Schema::class) . PhpInterface::DOUBLE_COLON
            . $method . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES . $entity
            . PhpInterface::QUOTES . PhpInterface::CLOSE_PARENTHESES . PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Writes row to migration with type and params
     *
     * @param string $method
     * @param string|null $property
     * @param null $opts
     * @param array|null $build
     * @param bool $quoteProperty
     */
    public function setRow(string $method, string $property = '', $opts = null, array $build = null, $quoteProperty = true) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4
            . PhpInterface::DOLLAR_SIGN . ModelsInterface::MIGRATION_TABLE
            . PhpInterface::ARROW . $method . PhpInterface::OPEN_PARENTHESES;
        if ($quoteProperty === true) {
            $this->sourceCode .= PhpInterface::QUOTES . $property . PhpInterface::QUOTES;
        } else { // smth like array
            $this->sourceCode .= $property;
        }
        $this->sourceCode .= (($opts === null) ? '' : PhpInterface::COMMA . PhpInterface::SPACE . $opts)
            . PhpInterface::CLOSE_PARENTHESES;
        if ($build !== null) {
            foreach ($build as $m => $param) {
                $this->sourceCode .= PhpInterface::ARROW . $m . PhpInterface::OPEN_PARENTHESES
                    . $param . PhpInterface::CLOSE_PARENTHESES;
            }
        }

        $this->sourceCode .= PhpInterface::SEMICOLON . PHP_EOL;
    }

    /**
     * Sets an int type for table row
     *
     * @param string $attrKey
     * @param array $attrVal
     * @param $type
     */
    private function setIntegerRow(string $attrKey, array $attrVal, $type) : void
    {
        if ($attrKey === ApiInterface::RAML_ID) {
            $this->setId($attrVal, $attrKey, $type);
        } else {
            $min = empty($attrVal[ApiInterface::RAML_INTEGER_MIN]) ? null : $attrVal[ApiInterface::RAML_INTEGER_MIN];
            $max = empty($attrVal[ApiInterface::RAML_INTEGER_MAX]) ? null : $attrVal[ApiInterface::RAML_INTEGER_MAX];
            $this->setIntegerDigit($attrKey, $max, $min < 0);
        }
    }

    /**
     * Sets string type for table row
     *
     * @param string $attrKey
     * @param array $attrVal
     */
    private function setStringRow(string $attrKey, array $attrVal) : void
    {
        $length = empty($attrVal[ApiInterface::RAML_STRING_MAX]) ? null : $attrVal[ApiInterface::RAML_STRING_MAX];
        $build = empty($attrVal[ApiInterface::RAML_KEY_DEFAULT]) ? null
            : [ApiInterface::RAML_KEY_DEFAULT => $this->quoteParam($attrVal[ApiInterface::RAML_KEY_DEFAULT])];
        $this->setRow(ModelsInterface::MIGRATION_METHOD_STRING, $attrKey, $length, $build);
    }

    /**
     * @param array $attrVal
     * @param string $attrKey
     */
    private function setNumberRow(array $attrVal, string $attrKey): void
    {
        if (empty($attrVal[ApiInterface::RAML_TYPE_FORMAT]) === false
            && ($attrVal[ApiInterface::RAML_TYPE_FORMAT] === ModelsInterface::MIGRATION_METHOD_DOUBLE
                || $attrVal[ApiInterface::RAML_TYPE_FORMAT] === ModelsInterface::MIGRATION_METHOD_FLOAT)
        ) {
            $max = empty($attrVal[ApiInterface::RAML_INTEGER_MAX]) ? ModelsInterface::DEFAULT_DOUBLE_M : $attrVal[ApiInterface::RAML_INTEGER_MAX];
            $min = empty($attrVal[ApiInterface::RAML_INTEGER_MIN]) ? ModelsInterface::DEFAULT_DOUBLE_D : $attrVal[ApiInterface::RAML_INTEGER_MIN];

            $this->setRow($attrVal[ApiInterface::RAML_TYPE_FORMAT], $attrKey, $max . PhpInterface::COMMA
                . PhpInterface::SPACE . $min);
        }
    }

    /**
     * Sets foreign key on table row
     *
     * @param array $facets
     * @param string $attrKey
     * @param $k
     * @throws AttributesException
     */
    private function setForeignIndex(array $facets, string $attrKey, $k) : void
    {
        if (empty($facets[ModelsInterface::INDEX_REFERENCES]) || empty($facets[ModelsInterface::INDEX_ON])) {
            throw new AttributesException(ErrorsInterface::CONSOLE_ERRORS[ErrorsInterface::CODE_FOREIGN_KEY], ErrorsInterface::CODE_FOREIGN_KEY);
        }

        $build = [
            ModelsInterface::INDEX_REFERENCES => $this->quoteParam($facets[ModelsInterface::INDEX_REFERENCES]),
            ModelsInterface::INDEX_ON => $this->quoteParam($facets[ModelsInterface::INDEX_ON]),
        ];

        if (empty($facets[ModelsInterface::INDEX_ON_DELETE]) === false) {
            $build[ModelsInterface::INDEX_ON_DELETE] = $this->quoteParam($facets[ModelsInterface::INDEX_ON_DELETE]);
        }

        if (empty($facets[ModelsInterface::INDEX_ON_UPDATE]) === false) {
            $build[ModelsInterface::INDEX_ON_UPDATE] = $this->quoteParam($facets[ModelsInterface::INDEX_ON_UPDATE]);
        }

        $this->setRow(ModelsInterface::INDEX_TYPE_FOREIGN, $attrKey, $this->quoteParam($k), $build);
    }

    /**
     * Creates composite index via facets
     * @param array $attrVal
     * @throws AttributesException
     */
    private function setCompositeIndex(array $attrVal): void
    {
        if (empty($attrVal[ApiInterface::RAML_FACETS][ApiInterface::RAML_COMPOSITE_INDEX]) === false) {

            $facets = $attrVal[ApiInterface::RAML_FACETS][ApiInterface::RAML_COMPOSITE_INDEX];
            if (empty($facets[ModelsInterface::INDEX_TYPE_FOREIGN]) === false) {
                if (empty($facets[ModelsInterface::INDEX_REFERENCES]) || empty($facets[ModelsInterface::INDEX_ON])) {
                    throw new AttributesException(ErrorsInterface::CONSOLE_ERRORS[ErrorsInterface::CODE_FOREIGN_KEY], ErrorsInterface::CODE_FOREIGN_KEY);
                }

                $build = [
                    ModelsInterface::INDEX_REFERENCES => $this->getArrayParam($facets[ModelsInterface::INDEX_REFERENCES]),
                    ModelsInterface::INDEX_ON => $this->quoteParam($facets[ModelsInterface::INDEX_ON]),
                ];

                if (empty($facets[ModelsInterface::INDEX_ON_DELETE]) === false) {
                    $build[ModelsInterface::INDEX_ON_DELETE] = $this->quoteParam($facets[ModelsInterface::INDEX_ON_DELETE]);
                }

                if (empty($facets[ModelsInterface::INDEX_ON_UPDATE]) === false) {
                    $build[ModelsInterface::INDEX_ON_UPDATE] = $this->quoteParam($facets[ModelsInterface::INDEX_ON_UPDATE]);
                }

                $this->setRow(ModelsInterface::INDEX_TYPE_FOREIGN, $this->getArrayParam($facets[ModelsInterface::INDEX_TYPE_FOREIGN]), null, $build, false);
            } else {
                foreach ($facets as $k => $v) {
                    $this->setRow($k, $this->getArrayParam($v), null, null, false);
                }
            }
        }
    }
}