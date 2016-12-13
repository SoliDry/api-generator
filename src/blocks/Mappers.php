<?php

namespace rjapi\blocks;

use rjapi\extension\json\api\db\BaseActiveDataMapper;
use rjapi\controllers\YiiRJApiGenerator;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Mappers extends Models
{
    use ContentManager;
    /** @var YiiRJApiGenerator $generator */
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
                            YiiRJApiGenerator::BACKSLASH . $this->generator->mappersDir);
        $baseMapper     = BaseActiveDataMapper::class;
        $baseMapperName = StringHelper::basename($baseMapper);

        $this->setUse($baseMapper);
        $this->startClass(
            YiiRJApiGenerator::FORM_BASE
            . YiiRJApiGenerator::MAPPER_PREFIX . $this->generator->objectName, $baseMapperName
        );
        $this->endClass();

        $file = FileManager::getModulePath($this->generator, true) . $this->generator->mappersDir . YiiRJApiGenerator::SLASH
                . YiiRJApiGenerator::FORM_BASE . YiiRJApiGenerator::MAPPER_PREFIX .
                $this->generator->objectName . YiiRJApiGenerator::PHP_EXT;
        FileManager::createFile($file, $this->sourceCode);
    }
}