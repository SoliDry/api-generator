<?php
namespace rjapi\helpers;

class ConfigOptions
{
    // query params
    private $queryLimit;
    private $querySort;
    private $queryPage;
    private $queryAccessToken;
    // jwt
    private $jwtIsEnabled;
    private $jwtTable;
    private $isJwtAction;

    /**
     * @return mixed
     */
    public function getQueryLimit()
    {
        return $this->queryLimit;
    }

    /**
     * @param mixed $queryLimit
     */
    public function setQueryLimit($queryLimit)
    {
        $this->queryLimit = $queryLimit;
    }

    /**
     * @return mixed
     */
    public function getQuerySort()
    {
        return $this->querySort;
    }

    /**
     * @param mixed $querySort
     */
    public function setQuerySort($querySort)
    {
        $this->querySort = $querySort;
    }

    /**
     * @return mixed
     */
    public function getQueryPage()
    {
        return $this->queryPage;
    }

    /**
     * @param mixed $queryPage
     */
    public function setQueryPage($queryPage)
    {
        $this->queryPage = $queryPage;
    }

    /**
     * @return mixed
     */
    public function getQueryAccessToken()
    {
        return $this->queryAccessToken;
    }

    /**
     * @param mixed $queryAccessToken
     */
    public function setQueryAccessToken($queryAccessToken)
    {
        $this->queryAccessToken = $queryAccessToken;
    }

    /**
     * @return mixed
     */
    public function getJwtIsEnabled()
    {
        return $this->jwtIsEnabled;
    }

    /**
     * @param mixed $jwtIsEnabled
     */
    public function setJwtIsEnabled($jwtIsEnabled)
    {
        $this->jwtIsEnabled = $jwtIsEnabled;
    }

    /**
     * @return mixed
     */
    public function getJwtTable()
    {
        return $this->jwtTable;
    }

    /**
     * @param mixed $jwtTable
     */
    public function setJwtTable($jwtTable)
    {
        $this->jwtTable = $jwtTable;
    }

    /**
     * @return bool
     */
    public function getIsJwtAction()
    {
        return $this->isJwtAction;
    }

    /**
     * @param bool $isJwtAction
     */
    public function setIsJwtAction(bool $isJwtAction)
    {
        $this->isJwtAction = $isJwtAction;
    }
}