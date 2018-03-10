<?php

namespace rjapi\blocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\helpers\Classes;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;

trait MigrationsTrait
{
    /**
     * @param string $entity
     * @param string $schemaMethod
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
     * @param string      $method
     * @param string|null $property
     * @param null        $opts
     * @param array|null  $build
     * @param bool        $quoteProperty
     */
    public function setRow(string $method, string $property = '', $opts = null, array $build = null, $quoteProperty = true) : void
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4
            . PhpInterface::DOLLAR_SIGN . ModelsInterface::MIGRATION_TABLE
            . PhpInterface::ARROW . $method . PhpInterface::OPEN_PARENTHESES;
        if (true === $quoteProperty) {
            $this->sourceCode .= PhpInterface::QUOTES . $property . PhpInterface::QUOTES;
        } else { // smth like array
            $this->sourceCode .= $property;
        }
        $this->sourceCode .= (($opts === null) ? '' : PhpInterface::COMMA . PhpInterface::SPACE . $opts)
        . PhpInterface::CLOSE_PARENTHESES;
        if($build !== null)
        {
            foreach($build as $method => $param)
            {
                $this->sourceCode .= PhpInterface::ARROW . $method . PhpInterface::OPEN_PARENTHESES
                    . $param . PhpInterface::CLOSE_PARENTHESES;
            }
        }
        $this->sourceCode .= PhpInterface::SEMICOLON . PHP_EOL;
    }
}