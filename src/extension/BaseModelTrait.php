<?php
namespace rjapi\extension;

use rjapi\blocks\ModelsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;

trait BaseModelTrait
{
    /**
     * @param int $id
     *
     * @return mixed
     */
    private function getEntity(int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [RamlInterface::RAML_ID, $id]
        );

        return $obj->first();
    }

    /**
     * @param string $modelEntity
     * @param int $id
     *
     * @return mixed
     */
    private function getModelEntity($modelEntity, int $id)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [RamlInterface::RAML_ID, $id]
        );

        return $obj->first();
    }

    /**
     * @param string $modelEntity
     * @param array $params
     *
     * @return mixed
     */
    private function getModelEntities($modelEntity, array $params)
    {
        return call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, $params
        );
    }

    /**
     * @param int $count
     * @param int $page
     *
     * @return mixed
     */
    private function getAllEntities(int $count = ModelsInterface::DEFAULT_LIMIT, int $page = ModelsInterface::DEFAULT_PAGE)
    {
        $from = ($count * $page) - $count;
        $to = $count * $page;
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            [RamlInterface::RAML_ID, ModelsInterface::SQL_DESC]
        );

        return $obj->take($to)->skip($from)->get();
    }
}