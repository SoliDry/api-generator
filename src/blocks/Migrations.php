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

        $file = FileManager::getModulePath($this->generator, true) .
                RoutesInterface::ROUTES_FILE_NAME . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode, true);
        if($isCreated)
        {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}