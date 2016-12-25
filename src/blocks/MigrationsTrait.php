<?php

namespace rjapi\blocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\helpers\Classes;

trait MigrationsTrait
{
    public function openSchema(string $entity)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 . ModelsInterface::MIGRATION_SCHEMA
            . PhpEntitiesInterface::DOUBLE_COLON . ModelsInterface::MIGRATION_CREATE
            . PhpEntitiesInterface::OPEN_PARENTHESES . PhpEntitiesInterface::QUOTES . strtolower($entity) . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::PHP_FUNCTION
            . PhpEntitiesInterface::OPEN_PARENTHESES . Classes::getName(Blueprint::class) . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::DOLLAR_SIGN
            . ModelsInterface::MIGRATION_TABLE . PhpEntitiesInterface::CLOSE_PARENTHESES . PhpEntitiesInterface::SPACE;

        $this->sourceCode .= PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeSchema()
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::CLOSE_BRACE . PhpEntitiesInterface::CLOSE_PARENTHESES
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    public function createSchema(string $method, string $entity)
    {
        $this->sourceCode .= $this->setTabs(2) . Classes::getName(Schema::class) . PhpEntitiesInterface::DOUBLE_COLON
            . $method . PhpEntitiesInterface::OPEN_PARENTHESES . PhpEntitiesInterface::QUOTES . $entity
            . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    public function setRow(string $method, $property = null, $opts = null)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4
            . PhpEntitiesInterface::DOLLAR_SIGN . ModelsInterface::MIGRATION_TABLE
            . PhpEntitiesInterface::ARROW . $method . PhpEntitiesInterface::OPEN_PARENTHESES
            . (($property === null) ? '' : PhpEntitiesInterface::QUOTES . $property
                . PhpEntitiesInterface::QUOTES) . (($opts === null) ? '' : $opts)
            . PhpEntitiesInterface::CLOSE_PARENTHESES
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }
}