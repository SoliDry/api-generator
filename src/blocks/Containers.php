<?php
namespace rjapi\blocks;

use rjapi\RJApiGenerator;
use rjapi\helpers\Classes;
use yii\db\ActiveRecord;

class Containers implements ModelsInterface
{
    use ContentManager;

    /** @var RJApiGenerator generator */
    private $generator  = null;
    private $sourceCode = '';

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
        $this->setNamespace($this->generator->containersDir);
        $this->setUse(ActiveRecord::class);

        $this->startClass(
            $this->generator->objectName . DefaultInterface::CONTAINER_POSTFIX,
            constant('self::' . strtoupper($this->generator->frameWork) . '_ACTIVE_RECORD')
        );



        // fill with methods
        $this->startMethod(
            constant('self::' . strtoupper($this->generator->frameWork) . '_METHOD_TABLE_NAME'),
            PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_STRING
        );
        $this->methodReturn('[]');
        $this->endMethod();

        $this->endClass();
        $fileController = $this->generator->formatContainersPath()
                          . PhpEntitiesInterface::SLASH
                          . $this->generator->objectName
                          . DefaultInterface::CONTAINER_POSTFIX
                          . PhpEntitiesInterface::PHP_EXT;
        FileManager::createFile($fileController, $this->sourceCode);
    }
}