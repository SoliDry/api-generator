<?php
namespace SoliDry\Extension;

use SoliDry\Exceptions\AttributesException;
use SoliDry\Types\ConfigInterface;
use SoliDry\Types\ErrorsInterface;

/**
 * Class BitMask
 * @package SoliDry\Extension
 */
class BitMask
{
    /**
     * @var array
     */
    private array $entity;

    private $field;
    private $isEnabled;

    /**
     * SpellCheck constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->entity    = $params;
        $this->field     = key($this->entity);
        $this->isEnabled = empty($this->entity[$this->field][ConfigInterface::ENABLED]) ? false : true;
    }

    /**
     * @return boolean
     */
    public function isEnabled(): bool
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

    /**
     * @return mixed
     * @throws AttributesException
     */
    public function getFlags()
    {
        if(empty($this->entity[$this->field][ConfigInterface::FLAGS])) {
            throw new AttributesException(ErrorsInterface::JSON_API_ERRORS[ErrorsInterface::HTTP_CODE_FSM_FLAGS], ErrorsInterface::HTTP_CODE_FSM_FLAGS);
        }
        return $this->entity[$this->field][ConfigInterface::FLAGS];
    }

    /**
     * Whether mask should be hidden or not
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return empty($this->entity[$this->field][ConfigInterface::HIDE_MASK]) ? false : true;
    }
}