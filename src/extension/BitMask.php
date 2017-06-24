<?php
/**
 * Created by Arthur Kushman.
 * User: arthur
 * Date: 24.06.17
 * Time: 17:30
 */

namespace rjapi\extension;


use rjapi\helpers\ConfigHelper;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;

class BitMask
{
    private $entity    = [];
    private $field     = null;
    private $isEnabled = false;
    /**
     * SpellCheck constructor.
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->entity    = ConfigHelper::getNestedParam(ConfigInterface::BIT_MASK, MigrationsHelper::getTableName($entity));
        $this->field     = key($this->entity);
        $this->isEnabled = empty($this->entity[ConfigInterface::ENABLED]) ? false : true;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return mixed|null
     */
    public function getField()
    {
        return $this->field;
    }
}