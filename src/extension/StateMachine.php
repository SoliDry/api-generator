<?php
namespace rjapi\extension;

use rjapi\exceptions\AttributesException;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\ConfigInterface;

class StateMachine
{
    private $machine = [];
    private $states  = [];
    private $initial = null;
    // state field taken from table in config
    private $field = null;

    /**
     * StateMachine constructor.
     * @param string $entity
     */
    public function __construct(string $entity)
    {
        $this->machine = ConfigHelper::getNestedParam(ConfigInterface::STATE_MACHINE, MigrationsHelper::getTableName($entity));
        $this->field   = key($this->machine);
    }

    /**
     * @param string $field
     * @return bool
     */
    public function isStatedField(string $field) : bool
    {
        return empty($this->machine[$field]) === false
            && $this->machine[$field][ConfigInterface::ENABLED] === true
            && empty($this->machine[$field][ConfigInterface::STATES]) === false;
    }

    /**
     * @param mixed $from
     * @param mixed $to
     * @return bool
     */
    public function isTransitive($from, $to): bool
    {
        return $from === $to || in_array($to, $this->states[$from], true);
    }

    public function isInitial($state): bool
    {
        return empty($this->states[ConfigInterface::INITIAL]) === false
        && in_array($state, $this->states[ConfigInterface::INITIAL]);
    }

    /**
     * @return mixed
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * @param string $field
     * @throws AttributesException
     */
    public function setStates(string $field) : void
    {
        if(empty($this->machine[$field][ConfigInterface::STATES])) {
            throw new AttributesException('There should be "states" element filled in with FSM.');
        }
        $this->states = $this->machine[$field][ConfigInterface::STATES];
    }

    /**
     * @param string $field
     * @throws AttributesException
     */
    public function setInitial(string $field) : void
    {
        if(empty($this->machine[$field][ConfigInterface::STATES][ConfigInterface::INITIAL][0])) {
            throw new AttributesException('There should be an initial value for: "' . $field . '" field."');
        }
        $this->initial = $this->machine[$field][ConfigInterface::STATES][ConfigInterface::INITIAL][0];
    }

    /**
     * @return mixed|null
     */
    public function getField()
    {
        return $this->field;
    }
}