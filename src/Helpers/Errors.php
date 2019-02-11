<?php

namespace SoliDry\Helpers;

use SoliDry\Extension\JSONApiInterface;

/**
 * Class Errors
 * @package SoliDry\Helpers
 */
class Errors
{
    /**
     * @param string $entity
     * @param $id
     * @return array
     */
    public function getModelNotFound(string $entity, $id) : array
    {
        return [
            [
                JSONApiInterface::ERROR_TITLE => 'Database object ' . $entity . ' with $id = ' . $id .
                    ' - not found.',
            ],
        ];
    }
}