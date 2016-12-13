<?php
namespace rjapi\blocks;

use rjapi\controllers\YiiRJApiGenerator;
use rjapi\extension\json\api\db\DataObjectTrait;
use rjapi\helpers\Classes;
use yii\console\Controller;
use yii\db\ActiveRecord;

class Containers
{
    use ContentManager;

    /** @var YiiRJApiGenerator generator */
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
        $this->setUse(ActiveRecord::class);
        $this->setUse(DataObjectTrait::class);

        $this->startClass(
            $this->generator->objectName . DefaultInterface::CONTAINER_POSTFIX,
            ModelsInterface::YII_ACTIVE_RECORD
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_ACTIVE_RECORD')
        );

        $this->setUse(Classes::getName(DataObjectTrait::class), true);
        
        // fill with methods
        $this->startMethod(
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_METHOD_TABLE_NAME'),
            ModelsInterface::YII_METHOD_TABLE_NAME,
            PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_STRING, true
        );
        $this->methodReturn(strtolower($this->generator->objectName), true);
        $this->endMethod();

        $this->sourceCode .= PHP_EOL . PHP_EOL;

        $this->startMethod(
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_METHOD_TABLE_NAME'),
            ModelsInterface::YII_METHOD_RULES,
            PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_STRING
        );
        $this->methodReturn('[]');
        $this->endMethod();

        $this->sourceCode .= PHP_EOL . PHP_EOL;

        $this->startMethod(
//            constant('ModelsInterface::' . strtoupper($this->generator->frameWork) . '_METHOD_TABLE_NAME'),
            ModelsInterface::YII_METHOD_CONTAINERS,
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