<?php
namespace SoliDry\Extension;

use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\Types\ConfigInterface;

/**
 * Class CustomSql
 * @package SoliDry\Extension
 */
class CustomSql
{
    private $entity    = [];
    private $isEnabled;

    /**
     * CustomSql constructor.
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entity = ConfigHelper::getNestedParam(ConfigInterface::CUSTOM_SQL, MigrationsHelper::getTableName($entity));
        $this->isEnabled = $this->entity[ConfigInterface::ENABLED];
    }

    /**
     * Whether custom sql is enabled or not on this entity
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Gets the exact query from config for this entity
     *
     * @return mixed
     */
    public function getQuery()
    {
        return $this->entity[ConfigInterface::QUERY];
    }

    /**
     * Gets bindings for query
     *
     * @return mixed
     */
    public function getBindings()
    {
        return $this->entity[ConfigInterface::BINDINGS];
    }
}