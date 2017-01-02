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
        $this->createSchema(ModelsInterface::MIGRATION_METHOD_DROP, strtolower($this->generator->objectName));
        $this->endMethod();
        $this->endClass();

        $migrationMask = date('d_m_Y_Hi', time()) . mt_rand(10, 99);

        $file = $this->generator->formatMigrationsPath() . $migrationMask . PhpEntitiesInterface::UNDERSCORE .
            ModelsInterface::MIGRATION_CREATE . PhpEntitiesInterface::UNDERSCORE . strtolower($this->generator->objectName) .
            PhpEntitiesInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE . PhpEntitiesInterface::PHP_EXT;
        // if migration file with the same name ocasionally exists we do not override it
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if ($isCreated) {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    /**
     *  Creates pivot table for ManyToMany relations if needed
     */
    public function createPivot()
    {
        $middlewareEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . strtoupper($this->generator->version) .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->generator->objectName .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleWare = new $middlewareEntity();

        if (method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $relations = $middleWare->relations();

            foreach ($relations as $relationEntity) {
                $entityFile = $this->generator->formatEntitiesPath()
                    . PhpEntitiesInterface::SLASH . $this->generator->objectName . ucfirst($relationEntity) .
                    PhpEntitiesInterface::PHP_EXT;

                if (file_exists($entityFile)) {
                    $this->setTag();

                    $this->setUse(Schema::class);
                    $this->setUse(Blueprint::class);
                    $migrationClass = Migration::class;
                    $this->setUse($migrationClass, false, true);
                    // migrate up
                    $this->startClass(ucfirst(ModelsInterface::MIGRATION_CREATE) . $this->generator->objectName
                        . ucfirst($relationEntity) . ucfirst(ModelsInterface::MIGRATION_TABLE), Classes::getName($migrationClass));
                    $this->startMethod(ModelsInterface::MIGRATION_METHOD_UP, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
                    $this->openSchema($this->generator->objectName
                        . PhpEntitiesInterface::UNDERSCORE . $relationEntity);
                    $this->setPivotRows($relationEntity);
                    $this->closeSchema();
                    $this->endMethod();
                    // migrate down
                    $this->startMethod(ModelsInterface::MIGRATION_METHOD_DOWN, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
                    $this->createSchema(ModelsInterface::MIGRATION_METHOD_DROP, strtolower($this->generator->objectName));
                    $this->endMethod();
                    $this->endClass();

                    $migrationMask = date('d_m_Y_Hi', time()) . mt_rand(10, 99);

                    $file = $this->generator->formatMigrationsPath() . $migrationMask . PhpEntitiesInterface::UNDERSCORE .
                        ModelsInterface::MIGRATION_CREATE . PhpEntitiesInterface::UNDERSCORE . strtolower($this->generator->objectName)
                        . PhpEntitiesInterface::UNDERSCORE . $relationEntity .
                        PhpEntitiesInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE . PhpEntitiesInterface::PHP_EXT;
                    // if migration file with the same name ocasionally exists we do not override it
                    $isCreated = FileManager::createFile($file, $this->sourceCode);
                    if ($isCreated) {
                        Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
                    }
                }
            }
        }
    }
}