<?php

namespace rjapi\extension;


use rjapi\helpers\Json;

/**
 * Class FsmTrait
 *
 * @package rjapi\extension
 *
 * @property ApiController entity
 */
trait FsmTrait
{
    /**
     * @param array $jsonProps JSON input properties
     * @throws \rjapi\exceptions\AttributesException
     */
    protected function checkFsmCreate(array &$jsonProps) : void
    {
        $stateMachine = new StateMachine($this->entity);
        $stateField   = $stateMachine->getField();
        if (empty($jsonProps[$stateField])) {
            $stateMachine->setInitial($stateField);
            $jsonProps[$stateField] = $stateMachine->getInitial();
        } else {
            foreach ($jsonProps as $k => $v) {
                if ($stateMachine->isStatedField($k) === true) {
                    $stateMachine->setStates($k);
                    if ($stateMachine->isInitial($v) === false) {
                        // the field is under state machine rules and it is not initial state
                        Json::outputErrors(
                            [
                                [
                                    JSONApiInterface::ERROR_TITLE  => 'This state is not an initial.',
                                    JSONApiInterface::ERROR_DETAIL => 'The state - \'' . $v . '\' is not an initial.',
                                ],
                            ]
                        );
                    }
                }
            }
        }
    }

    /**
     *
     * @param array $jsonProps
     * @param $model
     * @throws \rjapi\exceptions\AttributesException
     */
    protected function checkFsmUpdate(array $jsonProps, $model) : void
    {
        $stateMachine = new StateMachine($this->entity);
        $field        = $stateMachine->getField();
        $stateMachine->setStates($field);
        if (empty($jsonProps[$field]) === false
            && $stateMachine->isStatedField($field) === true
            && $stateMachine->isTransitive($model->$field, $jsonProps[$field]) === false
        ) {
            // the field is under state machine rules and it is not transitive in this direction
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE  => 'State can`t be changed through this way.',
                        JSONApiInterface::ERROR_DETAIL => 'The state of a field/column - \'' . $field . '\' can`t be changed from: \'' . $model->$field . '\', to: \'' . $jsonProps[$field] . '\'',
                    ],
                ]
            );
        }
    }
}