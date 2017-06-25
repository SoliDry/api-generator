<?php
/**
 * Created by Arthur Kushman.
 * User: arthur
 * Date: 24.06.17
 * Time: 17:30
 */

namespace rjapi\extension;


use rjapi\exception\AttributesException;
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
     * @param array $params
     */
    public function __construct(string $entity, array $params)
    {
        $this->entity    = $params;
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

    public function getFlags()
    {
        if(empty($this->entity[$this->field])) {
            throw new AttributesException('Flags should be preset for bit mask.');
        }
        return $this->entity[$this->field];
    }

    public function isHidden() {
        return empty($this->entity[ConfigInterface::HIDE_MASK]) ? false : true;
    }
}