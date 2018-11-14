<?php

namespace rjapi\blocks;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use rjapi\controllers\BaseCommand;
use rjapi\extension\BaseFormRequest;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\helpers\MethodOptions;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\CustomsInterface;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;

class Migrations extends MigrationsAbstract
{
    use EntitiesTrait;
    /** @var BaseCommand $generator */
    protected $generator;
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

    /**
     * @uses \rjapi\blocks\EntitiesTrait::reset
     */
    public function create()
    {
        $migrationName = ModelsInterface::MIGRATION_CREATE . PhpInterface::UNDERSCORE .
                         $this->tableName .
                         PhpInterface::UNDERSCORE . ModelsInterface::MIGRATION_TABLE;
        $attrKey = $this->generator->objectName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES;

        $isFileExist = FileManager::migrationNotExists($this->generator, $migrationName);
        $isAdding = (true === $this->generator->isMerge && empty($this->generator->diffTypes[$attrKey]) === false);
        if(true === $isFileExist)
        {
            $this->setContent();
        } else if (true === $isAdding) {
            $migrationName = str_replace(ModelsInterface::MIGRATION_TABLE_PTTRN, $this->tableName,
                ModelsInterface::MIGRATION_ADD_COLUMN);
            // file exists and it is merge op - add columns/indices for this table
            $columnName = key($this->generator->diffTypes[$attrKey]);
            $migrationName = str_replace(ModelsInterface::MIGRATION_COLUMN_PTTRN, $columnName, $migrationName);
            $this->resetContent($this->generator->diffTypes[$attrKey], $columnName);
        }
        if (true === $isFileExist || true === $isAdding) {
            $this->createMigrationFile($migrationName);
        }
    }

    /**
     *  Creates pivot table for ManyToMany relations if needed
     */
    public function createPivot()
    {
        $formRequestEntity = $this->getFormRequestEntity($this->generator->version, $this->className);
        /** @var BaseFormRequest $formRequest **/
        $formRequest       = new $formRequestEntity();
        if(method_exists($formRequest, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $formRequest->relations();
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
                    $migrationMask = date(self::PATTERN_TIME) . random_int(10, 99);
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
     * @param array  $attrs         columns
     * @param string $columnName    1st column
     */
    private function resetContent(array $attrs, string $columnName)
    {
        $this->setTag();
        $this->setUse(Schema::class);
        $this->setUse(Blueprint::class);
        $migrationClass = Migration::class;
        $this->setUse($migrationClass, false, true);
        $className = str_replace(ModelsInterface::MIGRATION_COLUMN_PTTRN, Classes::getClassName($columnName),
            ModelsInterface::MIGRATION_ADD_COLUMN_CLASS);
        $className = str_replace(ModelsInterface::MIGRATION_TABLE_PTTRN, $this->className, $className);
        $this->startClass($className, Classes::getName($migrationClass));
        // migrate up
        $methodOptions = new MethodOptions();
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_UP);
        $this->startMethod($methodOptions);
        $this->openSchema($this->tableName, ModelsInterface::MIGRATION_TABLE);
        $this->setAddRows($attrs);
        $this->closeSchema();
        $this->endMethod();
        // migrate down
        $methodOptions = new MethodOptions();
        $methodOptions->setName(ModelsInterface::MIGRATION_METHOD_DOWN);
        $this->startMethod($methodOptions);
        $this->openSchema($this->tableName, ModelsInterface::MIGRATION_TABLE);
        $this->dropRows($attrs);
        $this->closeSchema();
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