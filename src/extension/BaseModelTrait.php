<?php

namespace rjapi\extension;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;
use rjapi\helpers\SqlOptions;

/**
 * Class BaseModelTrait
 * @package rjapi\extension
 *
 * @property ApiController modelEntity
 */
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

    private function getEntities(SqlOptions $sqlOptions)
    {
        /** @var CustomSql $customSql */
        if ($this->customSql->isEnabled()) {
            return $this->getCustomSqlEntities($this->customSql);
        }
        return $this->getAllEntities($sqlOptions);
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
        foreach ($orderBy as $column => $value) {
            if ($first === true) {
                $defaultOrder = [$column, $value];
            } else {
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

    private function getCustomSqlEntities(CustomSql $customSql)
    {
        $result = DB::select($customSql->getQuery(), $customSql->getBindings());
        $collection = [];
        foreach ($result as $item) {
            $class = PhpInterface::BACKSLASH . $this->modelEntity;
            $collection[] = (new $class())->fill((array)$item);
        }
        return collect($collection);
    }

    /**
     * Collects all tree elements
     * @param SqlOptions $sqlOptions
     * @return Collection
     */
    public function getAllTreeEntities(SqlOptions $sqlOptions) : Collection
    {
        return Collection::make($this->buildTree($this->getAllEntities($sqlOptions)));
    }

    /**
     * Builds the tree based on children/parent axiom
     * @param Collection $data
     * @param int $id
     * @return array
     */
    private function buildTree(Collection $data, int $id = 0)
    {
        $tree = [];
        foreach ($data as $k => $child) {
            if ($child->parent_id === $id) { // child found
                // clear found children to free stack
                unset($data[$k]);
                $child->children = $this->buildTree($data, $child->id);
                $tree[] = $child;
            }
        }

        return $tree;
    }

    /**
     * Collects all tree elements
     * @param SqlOptions $sqlOptions
     * @param int $id
     * @return array
     */
    public function getSubTreeEntities(SqlOptions $sqlOptions, int $id) : array
    {
        return $this->buildSubTree($this->getAllEntities($sqlOptions), $id);
    }

    /**
     * Builds the sub-tree for top most ancestor
     * @param Collection $data
     * @param int $searchId
     * @param int $id
     * @param bool $isParentFound
     * @return array
     */
    private function buildSubTree(Collection $data, int $searchId, int $id = 0, bool $isParentFound = false)
    {
        $tree = [];
        foreach ($data as $k => $child) {
            if ($searchId === $child->id) {
                $isParentFound = true;
            }
            if ($child->parent_id === $id && true === $isParentFound) { // child found
                // clear found children to free stack
                unset($data[$k]);
                $child->children = $this->buildSubTree($data, $searchId, $child->id, $isParentFound);
                $tree[] = $child;
            }
            if (true === $isParentFound && 0 === $id) {
                return $tree;
            }
        }

        return $tree;
    }
}