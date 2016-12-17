<?php

namespace rjapi\blocks;

use rjapi\extension\BaseModel;
use rjapi\helpers\Classes;
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
        $this->endClass();

        $file = $this->generator->formatEntitiesPath() .
            PhpEntitiesInterface::SLASH .
            $this->generator->objectName . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode);
    }
}