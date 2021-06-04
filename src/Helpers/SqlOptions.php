<?php

namespace SoliDry\Helpers;

use SoliDry\Extension\BaseFormRequest;
use SoliDry\Types\ModelsInterface;

/**
 * Class SqlOptions
 * @package SoliDry\Helpers
 *
 * @property BaseFormRequest formRequest
 */
class SqlOptions
{
    /**
     * @var int
     */
    public int $id      = 0;

    /**
     * @var int
     */
    public int $limit   = ModelsInterface::DEFAULT_LIMIT;

    /**
     * @var int
     */
    public int $page    = ModelsInterface::DEFAULT_PAGE;

    /**
     * @var array
     */
    public array $orderBy = [];

    /**
     * @var array|string[]
     */
    public array $data    = ModelsInterface::DEFAULT_DATA;

    /**
     * @var array
     */
    public array $filter  = [];

    public $formRequest;

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
        if(\in_array(ModelsInterface::ID, $this->data, true) === false)
        {
            $this->data[] = ModelsInterface::ID;
        }

        // this fix cases where oneToMany/oneToOne relationships need binding by entity_id
        $rules = $this->formRequest->rules();
        foreach ($rules as $key => $val) {

            if (mb_strpos($key, '_id') !== false) {
                if (\in_array($key, $this->data, true) === false) {
                    $this->data[] = $key;
                }
            }
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

    /**
     * @param BaseFormRequest $formRequest
     */
    public function setFormRequest(BaseFormRequest $formRequest): void
    {
        $this->formRequest = $formRequest;
    }
}