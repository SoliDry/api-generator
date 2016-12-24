<?php

namespace rjapi\blocks;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Migrations extends MigrationsAbstract
{
    use ContentManager, MigrationsTrait;
    /** @var RJApiGenerator $generator */
    protected $generator = null;
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

        $this->setUse(Schema::class);
        $this->setUse(Blueprint::class);
        $migrationClass = Migration::class;
        $this->setUse($migrationClass, false, true);
        // migrate up
        $this->startClass(ucfirst(ModelsInterface::MIGRATION_CREATE) . $this->generator->objectName
            . ucfirst(ModelsInterface::MIGRATION_TABLE), Classes::getName($migrationClass));
        $this->startMethod(ModelsInterface::MIGRATION_METHOD_UP, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
        $this->openSchema($this->generator->objectName);
        $this->setRows();
        $this->closeSchema();
        $this->endMethod();
        // migrate down
        $this->startMethod(ModelsInterface::MIGRATION_METHOD_DOWN, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
        $this->openSchema($this->generator->objectName);
        $this->setRow(ModelsInterface::MIGRATION_METHOD_DROP, strtolower($this->generator->objectName));
        $this->closeSchema();
        $this->endMethod();
        $this->endClass();
        
        $migrationMask = date('d_m_Y_Hi', time()) . mt_rand(10, 999);

        $file = FileManager::getMigrationsPath() . $migrationMask . PhpEntitiesInterface::UNDERSCORE .
            ModelsInterface::MIGRATION_CREATE . PhpEntitiesInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE
            . PhpEntitiesInterface::UNDERSCORE . strtolower($this->generator->objectName) . PhpEntitiesInterface::PHP_EXT;
        // if migration file with the same name ocasionally exists we do not override it
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if ($isCreated) {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}