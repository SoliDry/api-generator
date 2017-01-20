<?php

namespace rjapi\blocks;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\helpers\MethodOptions;
use rjapi\helpers\MigrationsHelper;
use rjapi\RJApiGenerator;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;

class Migrations extends MigrationsAbstract
{
    use ContentManager, MigrationsTrait, EntitiesTrait;
    /** @var RJApiGenerator $generator */
    protected $generator  = null;
    protected $sourceCode = '';

    private $className = '';
    private $tableName = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
        $this->tableName = MigrationsHelper::getTableName($this->generator->objectName);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setContent();
        $migrationMask = date(self::PATTERN_TIME, time()) . mt_rand(10, 99);
        $migrationName = ModelsInterface::MIGRATION_CREATE . PhpInterface::UNDERSCORE .
                         $this->tableName .
                         PhpInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE;
        if(FileManager::migrationNotExists($this->generator, $migrationName))
        {
            $file = $this->generator->formatMigrationsPath() . $migrationMask . PhpInterface::UNDERSCORE .
                    $migrationName . PhpInterface::PHP_EXT;
            // if migration file with the same name ocasionally exists we do not override it
            $isCreated = FileManager::createFile($file, $this->sourceCode);
            if($isCreated)
            {
                Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
            }
        }
    }

    /**
     *  Creates pivot table for ManyToMany relations if needed
     */
    public function createPivot()
    {
        $middlewareEntity = $this->getMiddlewareEntity($this->generator->version, $this->className);
        $middleWare       = new $middlewareEntity();
        if(method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $middleWare->relations();
            foreach($relations as $relationEntity)
            {
                $entityFile = $this->generator->formatEntitiesPath()
                              . PhpInterface::SLASH .
                              $this->generator->objectName .
                              ucfirst($relationEntity) .
                              PhpInterface::PHP_EXT;
                if(file_exists($entityFile))
                {
                    $this->setPivotContent($relationEntity);
                    $migrationMask = date(self::PATTERN_TIME, time()) . mt_rand(10, 99);
                    $migrationName = ModelsInterface::MIGRATION_CREATE . PhpInterface::UNDERSCORE
                                     . $this->tableName
                                     . PhpInterface::UNDERSCORE .
                                     MigrationsHelper::getTableName($relationEntity) .
                                     PhpInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE;
                    if(FileManager::migrationNotExists($this->generator, $migrationName))
                    {
                        $file = $this->generator->formatMigrationsPath() . $migrationMask
                                . PhpInterface::UNDERSCORE . $migrationName . PhpInterface::PHP_EXT;
                        // if migration file with the same name ocasionally exists we do not override it
                        $isCreated = FileManager::createFile($file, $this->sourceCode);
                        if($isCreated)
                        {
                            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
                        }
                    }
                }
            }
        }
    }

    /**
     *  Sets the content of migration
     */
    private function setContent()
    {
        $this->setTag();

        $this->setUse(Schema::class);
        $this->setUse(Blueprint::class);
        $migrationClass = Migration::class;
        $this->setUse($migrationClass, false, true);
        // migrate up
        $this->startClass(
            ucfirst(ModelsInterface::MIGRATION_CREATE) . $this->className
            . ucfirst(ModelsInterface::MIGRATION_TABLE), Classes::getName($migrationClass)
        );
        $methodOptions = new MethodOptions();
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_UP);
        $this->startMethod($methodOptions);
        $this->openSchema($this->tableName);
        $this->setRows();
        $this->closeSchema();
        $this->endMethod();
        // migrate down
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_DOWN);
        $this->startMethod($methodOptions);
        $this->createSchema(ModelsInterface::MIGRATION_METHOD_DROP, $this->tableName);
        $this->endMethod();
        $this->endClass();
    }

    /**
     * Sets the content of pivot ManyToMany migration
     * @param string $relationEntity
     */
    private function setPivotContent(string $relationEntity)
    {
        $this->setTag();

        $this->setUse(Schema::class);
        $this->setUse(Blueprint::class);
        $migrationClass = Migration::class;
        $this->setUse($migrationClass, false, true);
        // migrate up
        $this->startClass(
            ucfirst(ModelsInterface::MIGRATION_CREATE) .
            Classes::getClassName($this->generator->objectName) .
            Classes::getClassName($relationEntity) .
            ucfirst(ModelsInterface::MIGRATION_TABLE), Classes::getName($migrationClass)
        );
        $methodOptions = new MethodOptions();
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_UP);
        $this->startMethod($methodOptions);
        // make first entity lc + underscore
        $table = MigrationsHelper::getTableName($this->generator->objectName);
        // make 2nd entity lc + underscore
        $relatedTable   = MigrationsHelper::getTableName($relationEntity);
        $combinedTables = $table . PhpInterface::UNDERSCORE . $relatedTable;
        // migrate up
        $this->openSchema($combinedTables);
        $this->setPivotRows($relationEntity);
        $this->closeSchema();
        $this->endMethod();
        // migrate down
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_DOWN);
        $this->startMethod($methodOptions);
        $this->createSchema(ModelsInterface::MIGRATION_METHOD_DROP, $combinedTables);
        $this->endMethod();
        $this->endClass();
    }
}