<?php
namespace rjapi\extension;

use rjapi\helpers\ConfigHelper;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;

class StateMachine
{
    private $machine = [];
    private $states  = [];

    /**
     * StateMachine constructor.
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->machine = ConfigHelper::getNestedParam(ConfigInterface::STATE_MACHINE, MigrationsHelper::getTableName($entity));
    }

    /**
     * @param string $field
     * @return bool
     */
    public function isStatedField(string $field)
    {
        if(empty($this->machine[$field]) === false
            && $this->machine[$field][ConfigInterface::ENABLED] === true
            && empty($this->machine[$field][ConfigInterface::STATES]) === false)
        {
            $this->states = $this->machine[$field][ConfigInterface::STATES];
            return true;
        }
        return false;
    }

    /**
     * @param mixed $from
     * @param mixed $to
     * @return bool
     */
    public function isTransitive($from, $to): bool
    {
        return in_array($to, $this->states[$from]);
    }

    public function isInitial($state): bool
    {
        return empty($this->states[ConfigInterface::INITIAL]) === false
        && in_array($state, $this->states[ConfigInterface::INITIAL]);
    }
}