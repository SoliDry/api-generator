<?php
namespace rjapi\extension;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
use rjapi\helpers\SqlOptions;

trait BaseModelTrait
{
    private $tree = [];

    /**
     * @param int $id
     * @param array $data
     *
     * @return mixed
     */
    private function getEntity(int $id, array $data = ModelsInterface::DEFAULT_DATA)
    {
        $obj = call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON
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
            PhpInterface::BACKSLASH . $modelEntity . PhpInterface::DOUBLE_COLON
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
            PhpInterface::BACKSLASH . $modelEntity . PhpInterface::DOUBLE_COLON
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
        $page = $sqlOptions->getPage();
        $data = $sqlOptions->getData();
        $orderBy = $sqlOptions->getOrderBy();
        $filter = $sqlOptions->getFilter();
        $defaultOrder = [];
        $order = [];
        $first = true;
        foreach($orderBy as $column => $value) {
            if($first === true) {
                $defaultOrder = [$column, $value];
            }
            else {
                $order[] = [ModelsInterface::COLUMN    => $column,
                            ModelsInterface::DIRECTION => $value];
            }
            $first = false;
        }
        $from = ($limit * $page) - $limit;
        $to = $limit * $page;
        /** @var Builder $obj */
        $obj = call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            $defaultOrder
        );
        // it can be empty if nothing more then 1st passed
        $obj->order = $order;

        return $obj->where($filter)->take($to)->skip($from)->get($data);
    }

    public function getAllTreeEntities(SqlOptions $sqlOptions)
    {
        // getting parents
        $parents = $this->getTreeParents($sqlOptions);
        $children = $this->getTreeChildren($sqlOptions);

        foreach($parents as $parent) {
            $this->tree[] = [$parent => $this->buildSubTree($children, $parent->id)];
        }
        return $this->tree;
    }

    public function getTreeParents(SqlOptions $sqlOptions): Collection
    {
        $limit = $sqlOptions->getLimit();
        $page = $sqlOptions->getPage();
        $data = $sqlOptions->getData();
        $orderBy = $sqlOptions->getOrderBy();
        $filter = $sqlOptions->getFilter();
        // add parents clause to filter
        array_push($filter, [ModelsInterface::PARENT_ID, '=', 0]);
        $defaultOrder = [];
        $order = [];
        $first = true;
        foreach($orderBy as $column => $value) {
            if($first === true) {
                $defaultOrder = [$column, $value];
            }
            else {
                $order[] = [ModelsInterface::COLUMN    => $column,
                            ModelsInterface::DIRECTION => $value];
            }
            $first = false;
        }
        $from = ($limit * $page) - $limit;
        $to = $limit * $page;
        /** @var Builder $obj */
        $obj = call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            $defaultOrder
        );
        // it can be empty if nothing more then 1st passed
        $obj->order = $order;

        return $obj->where($filter)->take($to)->skip($from)->get($data);
    }

    public function getTreeChildren(SqlOptions $sqlOptions): Collection
    {
        $filter = $sqlOptions->getFilter();
        $data = $sqlOptions->getData();
        $orderBy = $sqlOptions->getOrderBy();

        // add children clause to filter
        array_push($filter, [ModelsInterface::PARENT_ID, '>', 0]);
        $defaultOrder = [];
        $order = [];
        $first = true;
        foreach($orderBy as $column => $value) {
            if($first === true) {
                $defaultOrder = [$column, $value];
            }
            else {
                $order[] = [ModelsInterface::COLUMN    => $column,
                            ModelsInterface::DIRECTION => $value];
            }
            $first = false;
        }
        /** @var Builder $obj */
        $obj = call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            $defaultOrder
        );
        // it can be empty if nothing more then 1st passed
        $obj->order = $order;

        return $obj->where($filter)->get($data);
    }

    private function buildSubTree($childrenData, int $id, int $prevId = 0)
    {
        $tree = [];
        foreach($childrenData as $k => $child) {
            if($child->parent_id === $id) { // child found
                if($prevId === $id) { // the same level
                    $tree[$id] = $child;
                } else { // going deeper
                    $tree[$id][$child->id] = $child;
                }
                $prevId = $id;
                $this->buildSubTree($childrenData, $child->id, $prevId);
            }
        }

        return $tree;
    }
}