<?php

namespace rjapi\blocks;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\helpers\Classes;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;

trait MigrationsTrait
{
    public function openSchema(string $entity)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . ModelsInterface::MIGRATION_SCHEMA
            . PhpInterface::DOUBLE_COLON . ModelsInterface::MIGRATION_CREATE
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES . strtolower($entity) . PhpInterface::QUOTES
            . PhpInterface::COMMA . PhpInterface::SPACE . PhpInterface::PHP_FUNCTION
            . PhpInterface::OPEN_PARENTHESES . Classes::getName(Blueprint::class) . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN
            . ModelsInterface::MIGRATION_TABLE . PhpInterface::CLOSE_PARENTHESES . PhpInterface::SPACE;

        $this->sourceCode .= PhpInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeSchema()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::CLOSE_BRACE . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SEMICOLON . PHP_EOL;
    }

    public function createSchema(string $method, string $entity)
    {
        $this->sourceCode .= $this->setTabs(2) . Classes::getName(Schema::class) . PhpInterface::DOUBLE_COLON
            . $method . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES . $entity
            . PhpInterface::QUOTES . PhpInterface::CLOSE_PARENTHESES . PhpInterface::SEMICOLON . PHP_EOL;
    }

    public function setRow(string $method, string $property = null, $opts = null, array $build = null)
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4 . PhpInterface::TAB_PSR4
            . PhpInterface::DOLLAR_SIGN . ModelsInterface::MIGRATION_TABLE
            . PhpInterface::ARROW . $method . PhpInterface::OPEN_PARENTHESES
            . (($property === null) ? '' : PhpInterface::QUOTES . $property
                . PhpInterface::QUOTES) . (($opts === null) ? '' : PhpInterface::COMMA . PhpInterface::SPACE . $opts)
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