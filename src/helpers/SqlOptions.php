<?php

namespace rjapi\helpers;

use rjapi\types\ModelsInterface;

class SqlOptions
{
    public $id      = 0;
    public $limit   = ModelsInterface::DEFAULT_LIMIT;
    public $page    = ModelsInterface::DEFAULT_PAGE;
    public $orderBy = [];
    public $data    = ModelsInterface::DEFAULT_DATA;
    public $filter  = [];

    /**
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit) : void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getPage() : int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page) : void
    {
        $this->page = $page;
    }

    /**
     * @return array
     */
    public function getOrderBy() : array
    {
        return $this->orderBy;
    }

    /**
     * @param array $orderBy
     */
    public function setOrderBy($orderBy) : void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data) : void
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
    public function getFilter() : array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter($filter) : void
    {
        $this->filter = $filter;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     */
    public function setId($id) : void
    {
        $this->id = $id;
    }
}