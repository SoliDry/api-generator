<?php
namespace rjapi\blocks;


use rjapi\helpers\Classes;
use rjapi\helpers\Config as conf;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;

trait EntitiesTrait
{
    public function getMiddlewareEntity(string $version, string $object)
    {
        return DirsInterface::MODULES_DIR . PhpInterface::BACKSLASH . strtoupper($version) .
        PhpInterface::BACKSLASH . DirsInterface::HTTP_DIR .
        PhpInterface::BACKSLASH .
        DirsInterface::MIDDLEWARE_DIR . PhpInterface::BACKSLASH .
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