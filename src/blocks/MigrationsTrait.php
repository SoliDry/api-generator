<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 20/12/2016
 * Time: 17:03
 */

namespace rjapi\blocks;

use Illuminate\Database\Schema\Blueprint;
use rjapi\helpers\Config;

trait MigrationsTrait
{
    public function openSchema(string $entity)
    {
        $this->sourceCode .= ModelsInterface::MIGRATION_SCHEMA . PhpEntitiesInterface::DOUBLE_COLON . ModelsInterface::MIGRATION_CREATE
            . PhpEntitiesInterface::OPEN_PARENTHESES . PhpEntitiesInterface::QUOTES . $entity . PhpEntitiesInterface::QUOTES
            . PhpEntitiesInterface::COMMA . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::PHP_FUNCTION
            . PhpEntitiesInterface::OPEN_PARENTHESES . Blueprint::class . PhpEntitiesInterface::DOLLAR_SIGN
            . ModelsInterface::MIGRATION_TABLE . PhpEntitiesInterface::CLOSE_PARENTHESES . PhpEntitiesInterface::SPACE;

        $this->sourceCode .= PhpEntitiesInterface::OPEN_BRACE . PHP_EOL;
    }

    public function closeSchema()
    {
        $this->sourceCode .= PhpEntitiesInterface::CLOSE_BRACE . PhpEntitiesInterface::CLOSE_PARENTHESES
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }

    public function setRow(string $method, string $property)
    {
        $this->sourceCode .= PhpEntitiesInterface::DOLLAR_SIGN . ModelsInterface::MIGRATION_TABLE
            . PhpEntitiesInterface::ARROW . $method . PhpEntitiesInterface::OPEN_PARENTHESES . PhpEntitiesInterface::QUOTES
            . $property . PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::CLOSE_PARENTHESES
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL;
    }
}