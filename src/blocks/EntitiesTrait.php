<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 04.01.17
 * Time: 0:06
 */

namespace rjapi\blocks;


trait EntitiesTrait
{
    public function getMiddleware(string $version, string $object)
    {
        return DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . strtoupper($version) .
        PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
        PhpEntitiesInterface::BACKSLASH .
        DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
        $object .
        DefaultInterface::MIDDLEWARE_POSTFIX;
    }
}