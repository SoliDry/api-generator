<?php
namespace rjapi\extension;

use rjapi\blocks\ModelsInterface;
use rjapi\blocks\PhpEntitiesInterface;
use rjapi\blocks\RamlInterface;
use rjapi\helpers\SqlOptions;

trait BaseModelTrait
{
    /**
     * @param int $id
     * @param array $data
     *
     * @return mixed
     */
    private function getEntity(int $id, array $data = ModelsInterface::DEFAULT_DATA)
    {
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [RamlInterface::RAML_ID, $id]
        );

        return $obj->first($data);
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
     * Get rows from particular Entity
     *
     * @param SqlOptions $sqlOptions
     *
     * @return mixed
     */
    private function getAllEntities(SqlOptions $sqlOptions)
    {
        $limit = $sqlOptions->getLimit();
        $page = $sqlOptions->getData();
        $sort = $sqlOptions->getSort();
        $data = $sqlOptions->getData();
//        $orderBy = $sqlOptions->getOrderBy();
        
        $from = ($limit * $page) - $limit;
        $to = $limit * $page;
        $obj = call_user_func_array(
            PhpEntitiesInterface::BACKSLASH . $this->modelEntity . PhpEntitiesInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            [RamlInterface::RAML_ID, $sort]
        );

        return $obj->take($to)->skip($from)->get($data);
    }
}