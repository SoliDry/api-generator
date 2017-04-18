<?php
namespace rjapi\extension;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
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

    public function getAllTreeEntities(SqlOptions $sqlOptions): Collection
    {
        return Collection::make($this->buildSubTree($this->getAllEntities($sqlOptions)));
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

    private function buildSubTree(Collection $data, int $id = 0)
    {
        $tree = [];
        foreach($data as $k => $child) {
            if($child->parent_id === $id) { // child found
                // clear found children to free stack
                unset($data[$k]);
                $child->children = $this->buildSubTree($data, $child->id);
                $tree[] = $child;
            }
        }

        return $tree;
    }
}