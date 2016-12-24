<?php

namespace rjapi\blocks;

use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Migrations extends MigrationsAbstract
{
    use ContentManager, MigrationsTrait;
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setTag();
        $this->openSchema($this->generator->objectName);
        $this->setRows();
        $this->closeSchema();

        $file = FileManager::getMigrationsPath() .
                ModelsInterface::MIGRATION_CREATE . PhpEntitiesInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE
            . PhpEntitiesInterface::UNDERSCORE . strtolower($this->generator->objectName) . PhpEntitiesInterface::PHP_EXT;
        // if migration file with the same name ocasionally exists we do not override it
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if($isCreated)
        {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}