<?php
namespace rjapi\blocks;

use rjapi\controllers\YiiTypesController;
use yii\console\Controller;

class Containers
{
    use ContentManager;

    /** @var YiiTypesController generator */
    private $generator  = null;
    private $sourceCode = '';

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace($this->generator->containersDir);
        $this->startClass(
            $this->generator->objectName . DefaultInterface::CONTAINER_POSTFIX,
            ModelsInterface::YII_ACTIVE_RECORD
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_ACTIVE_RECORD')
        );
        // fill with methods
        $this->startMethod(
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_METHOD_TABLE_NAME'),
            ModelsInterface::YII_ACTIVE_RECORD,
            PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_STRING, true
        );
        $this->methodReturn($this->generator->objectName);
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