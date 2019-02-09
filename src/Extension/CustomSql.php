<?php
namespace SoliDry\Extension;

use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;

class CustomSql
{
    private $entity    = [];
    private $isEnabled;

    public function __construct(string $entity)
    {
        $this->entity = ConfigHelper::getNestedParam(ConfigInterface::CUSTOM_SQL, MigrationsHelper::getTableName($entity));
        $this->isEnabled = $this->entity[ConfigInterface::ENABLED];
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }
    
    public function getQuery()
    {
        return $this->entity[ConfigInterface::QUERY];
    }

    public function getBindings()
    {
        return $this->entity[ConfigInterface::BINDINGS];
    }
}