<?php

namespace SoliDry\Extension;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;
use SoliDry\Helpers\SqlOptions;

/**
 * Class BaseModelTrait
 * @package SoliDry\Extension
 *
 * @property ApiController modelEntity
 * @property CustomSql customSql
 */
trait BaseModelTrait
{
    /**
     * @param int|string $id
     * @param array $data
     *
     * @return mixed
     */
    private function getEntity($id, array $data = ModelsInterface::DEFAULT_DATA)
    {
        $obj = \call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [ApiInterface::RAML_ID, $id]
        );

        if ($data[0] !== PhpInterface::ASTERISK) {
            // add id to output it in json-api entity
            $data[] = ModelsInterface::ID;
        }

        return $obj->first($data);
    }

    /**
     * Gets all or custom entities
     *
     * @param SqlOptions $sqlOptions
     * @return \Illuminate\Support\Collection|mixed
     */
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
     * @param int|string $id
     *
     * @return mixed
     */
    private function getModelEntity($modelEntity, $id)
    {
        $obj = \call_user_func_array(
            PhpInterface::BACKSLASH . $modelEntity . PhpInterface::DOUBLE_COLON
            . ModelsInterface::MODEL_METHOD_WHERE, [ApiInterface::RAML_ID, $id]
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
        return \call_user_func_array(
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
        $limit        = $sqlOptions->getLimit();
        $page         = $sqlOptions->getPage();
        $data         = $sqlOptions->getData();
        $orderBy      = $sqlOptions->getOrderBy();
        $filter       = $sqlOptions->getFilter();
        $defaultOrder = [];
        $order        = [];
        $first        = true;
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
        $to   = $limit * $page;

        /** @var Builder $obj */
        $obj = \call_user_func_array(
            PhpInterface::BACKSLASH . $this->modelEntity . PhpInterface::DOUBLE_COLON .
            ModelsInterface::MODEL_METHOD_ORDER_BY,
            $defaultOrder
        );

        // it can be empty if nothing more then 1st passed
        $obj->order = $order;

        return $obj->where($filter)->take($to)->skip($from)->get($data);
    }

    /**
     * Selects a collection of items based on custom sql
     *
     * @param CustomSql $customSql
     * @return \Illuminate\Support\Collection
     */
    private function getCustomSqlEntities(CustomSql $customSql): \Illuminate\Support\Collection
    {
        $result     = DB::select($customSql->getQuery(), $customSql->getBindings());
        $collection = [];
        foreach ($result as $item) {
            $class        = PhpInterface::BACKSLASH . $this->modelEntity;
            $collection[] = (new $class())->fill((array)$item);
        }
        return collect($collection);
    }

    /**
     * Collects all tree elements
     *
     * @param SqlOptions $sqlOptions
     * @return Collection
     */
    public function getAllTreeEntities(SqlOptions $sqlOptions) : Collection
    {
        return Collection::make($this->buildTree($this->getAllEntities($sqlOptions)));
    }

    /**
     * Builds the tree based on children/parent axiom
     *
     * @param Collection $data
     * @param int|string $id
     * @return array
     */
    private function buildTree(Collection $data, $id = 0): array
    {
        $tree = [];
        foreach ($data as $k => $child) {
            if ($child->parent_id === $id) { // child found
                // clear found children to free stack
                unset($data[$k]);
                $child->children = $this->buildTree($data, $child->id);
                $tree[]          = $child;
            }
        }

        return $tree;
    }

    /**
     * Collects all tree elements
     *
     * @param SqlOptions $sqlOptions
     * @param int|string $id
     * @return array
     */
    public function getSubTreeEntities(SqlOptions $sqlOptions, $id) : array
    {
        return $this->buildSubTree($this->getAllEntities($sqlOptions), $id);
    }

    /**
     * Builds the sub-tree for top most ancestor
     *
     * @param Collection $data
     * @param int|string $searchId
     * @param int|string $id
     * @param bool $isParentFound
     * @return array
     */
    private function buildSubTree(Collection $data, $searchId, $id = 0, bool $isParentFound = false) : array
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
                $tree[]          = $child;
            }
            if (true === $isParentFound && 0 === $id) {
                return $tree;
            }
        }

        return $tree;
    }
}