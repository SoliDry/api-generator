<?php
namespace rjapi\blocks;


use rjapi\helpers\Classes;
use rjapi\helpers\Config as conf;

trait EntitiesTrait
{
    public function getMiddlewareEntity(string $version, string $object)
    {
        return DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . strtoupper($version) .
        PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
        PhpEntitiesInterface::BACKSLASH .
        DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
        $object .
        DefaultInterface::MIDDLEWARE_POSTFIX;
    }

    protected function setEntities()
    {
        $this->entity      = Classes::cutEntity(Classes::getObjectName($this), DefaultInterface::CONTROLLER_POSTFIX);
        $middlewareEntity  = $this->getMiddlewareEntity(conf::getModuleName(), $this->entity);
        $this->middleWare  = new $middlewareEntity();
        $this->props       = get_object_vars($this->middleWare);
        $this->modelEntity = Classes::getModelEntity($this->entity);
        $this->model       = new $this->modelEntity();
    }
}