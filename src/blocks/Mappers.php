<?php

namespace rjapi\blocks;

use rjapi\helpers\Classes;
use rjapi\RJApiGenerator;

class Mappers extends Models
{
    use ContentManager;
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
        $this->setNamespace($this->generator->modelsFormDir .
                            PhpEntitiesInterface::BACKSLASH . $this->generator->mappersDir);
        $baseMapper     = BaseActiveDataMapper::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper);
        $this->startClass(
            DefaultInterface::FORM_BASE
            . DefaultInterface::MAPPER_PREFIX . $this->generator->objectName, $baseMapperName
        );
        $this->endClass();

        $file = FileManager::getModulePath($this->generator, true) . $this->generator->mappersDir . PhpEntitiesInterface::SLASH
                . DefaultInterface::FORM_BASE . DefaultInterface::MAPPER_PREFIX .
                $this->generator->objectName . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode);
    }
}