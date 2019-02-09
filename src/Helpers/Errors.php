<?php

namespace SoliDry\Helpers;

use SoliDry\Extension\JSONApiInterface;

class Errors
{
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