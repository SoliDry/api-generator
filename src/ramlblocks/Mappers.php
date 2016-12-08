<?php

namespace rjapi\extension\yii2\raml\ramlblocks;

use rjapi\extension\json\api\db\BaseActiveDataMapper;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Mappers extends Models
{
    use ContentManager;
    /** @var TypesController $generator */
    private   $generator  = null;
    protected $sourceCode = '';

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
        $this->setNamespace($this->generator->modelsFormDir .
                            TypesController::BACKSLASH . $this->generator->mappersDir);
        $baseMapper     = BaseActiveDataMapper::class;
        $baseMapperName = StringHelper::basename($baseMapper);

        $this->setUse($baseMapper);
        $this->startClass(
            TypesController::FORM_BASE
            . TypesController::MAPPER_PREFIX . $this->generator->objectName, $baseMapperName
        );
        $this->endClass();

        $file = FileManager::getModulePath($this->generator, true) . $this->generator->mappersDir . TypesController::SLASH
                . TypesController::FORM_BASE . TypesController::MAPPER_PREFIX .
                $this->generator->objectName . TypesController::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode);
    }
}