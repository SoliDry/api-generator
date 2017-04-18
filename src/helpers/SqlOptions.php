<?php

namespace rjapi\helpers;

use rjapi\types\ModelsInterface;

class SqlOptions
{
    public $sort    = null;
    public $limit   = null;
    public $page    = null;
    public $orderBy = [];
    public $data    = ModelsInterface::DEFAULT_DATA;
    public $filter  = [];
    public $isTree  = false;

    /**
     * @param null $sort
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * @return null
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param null $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param null $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return array
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param array $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        // id must be there anyway
        $this->data = $data;
        if(in_array(ModelsInterface::ID, $this->data) === false)
        {
            $this->data[] = ModelsInterface::ID;
        }
    }

    /**
     * @return array
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param bool $isTree
     */
    public function setIsTree(bool $isTree)
    {
        $this->isTree = $isTree;
    }

    /**
     * @return bool
     */
    public function getIsTree(): bool
    {
        return $this->isTree;
    }
}