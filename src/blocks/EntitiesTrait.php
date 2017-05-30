<?php
namespace rjapi\blocks;

use rjapi\extension\ApiController;
use rjapi\helpers\Classes;
use rjapi\helpers\ConfigHelper as conf;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class EntitiesTrait
 *
 * @package rjapi\blocks
 * @property ApiController entity
 * @property ApiController middleWare
 * @property ApiController props
 * @property ApiController model
 * @property ApiController modelEntity
 */
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

    /**
     * Gets the relations of entity or null
     * @param string $objectName
     *
     * @return mixed
     */
    private function getRelationType(string $objectName)
    {
        if (empty($this->generator->types[$objectName][RamlInterface::RAML_PROPS]
                  [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
        ) {
            return trim(
                $this->generator->types[$objectName][RamlInterface::RAML_PROPS]
                [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
            );
        }

        return null;
    }
}