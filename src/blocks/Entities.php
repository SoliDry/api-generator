<?php

namespace rjapi\blocks;

use rjapi\extension\BaseModel;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Entities extends FormRequestModel
{
    use ContentManager;
    /** @var RJApiGenerator $generator */
    private $generator = null;
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
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->generator->objectName, $baseMapperName);

        $this->createProperty(
            DefaultInterface::PRIMARY_KEY_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            DefaultInterface::TABLE_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName), true
        );
        $this->createProperty(
            DefaultInterface::TIMESTAMPS_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC,
            'false'
        );

        $middlewareEntity = DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . Config::getModuleName() .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->generator->objectName .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleWare = new $middlewareEntity();
        if (method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $relations = $middleWare->relations();
            foreach ($relations as $k => $relationEntity) {
                $this->startMethod($relationEntity, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
                $this->methodReturn(PhpEntitiesInterface::DOLLAR_SIGN . PhpEntitiesInterface::PHP_THIS
                    . PhpEntitiesInterface::ARROW . ModelsInterface::MODEL_METHOD_HAS_MANY
                    . PhpEntitiesInterface::OPEN_PARENTHESES . ucfirst($relationEntity)
                    . PhpEntitiesInterface::DOUBLE_COLON . PhpEntitiesInterface::PHP_CLASS
                    . PhpEntitiesInterface::CLOSE_PARENTHESES);
                $this->endMethod();
            }
        }

        $this->endClass();

        $file = $this->generator->formatEntitiesPath() .
            PhpEntitiesInterface::SLASH .
            $this->generator->objectName . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if ($isCreated) {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }
}