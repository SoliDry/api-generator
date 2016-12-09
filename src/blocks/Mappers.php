<?php

namespace rjapi\blocks;

use rjapi\extension\json\api\db\BaseActiveDataMapper;
use rjapi\controllers\YiiTypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Mappers extends Models
{
    use ContentManager;
    /** @var YiiTypesController $generator */
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
                            YiiTypesController::BACKSLASH . $this->generator->mappersDir);
        $baseMapper     = BaseActiveDataMapper::class;
        $baseMapperName = StringHelper::basename($baseMapper);

        $this->setUse($baseMapper);
        $this->startClass(
            YiiTypesController::FORM_BASE
            . YiiTypesController::MAPPER_PREFIX . $this->generator->objectName, $baseMapperName
        );
        $this->endClass();

        $file = FileManager::getModulePath($this->generator, true) . $this->generator->mappersDir . YiiTypesController::SLASH
                . YiiTypesController::FORM_BASE . YiiTypesController::MAPPER_PREFIX .
                $this->generator->objectName . YiiTypesController::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode);
    }
}