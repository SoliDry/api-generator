<?php

namespace SoliDryTest\Unit\Helpers;


use SoliDry\Extension\JSONApiInterface;
use SoliDry\Helpers\Errors;
use SoliDryTest\Unit\TestCase;

class ErrorsTest extends TestCase
{

    private const ENTITY = 'article';
    private const ID     = 123;

    /**
     * @test
     */
    public function it_tests_error_model_not_found()
    {
        $error = (new Errors())->getModelNotFound(self::ENTITY, self::ID);
        $this->assertArraySubset([
            [
                JSONApiInterface::ERROR_TITLE => 'Database object '
                    . self::ENTITY . ' with $id = ' . self::ID .
                    ' - not found.',
            ],
        ], $error);
    }
}