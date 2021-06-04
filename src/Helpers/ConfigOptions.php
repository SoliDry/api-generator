<?php
namespace SoliDry\Helpers;

/**
 * Class ConfigOptions
 * @package SoliDry\Helpers
 */
class ConfigOptions
{
    /**
     * @var string
     */
    private string $calledMethod = '';

    // query params
    private $queryLimit;
    private $querySort;
    private $queryPage;
    private $queryAccessToken;
    // jwt
    private $jwtIsEnabled;
    private $jwtTable;
    private $isJwtAction;
    // state machine
    /**
     * @var bool
     */
    private bool $isStateMachine = false;
    // spell check
    /**
     * @var bool
     */
    private bool $spellCheck = false;
    // bit mask
    /**
     * @var bool
     */
    private bool $isBitWise = false;

    // cache settings
    /**
     * @var bool
     */
    private bool $isCached = false;

    /**
     * @var bool
     */
    private bool $isXFetch = false;

    /**
     * @var float
     */
    private float $cacheBeta = 1.0;

    /**
     * @var int
     */
    private int $cacheTtl = 0;

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

    /**
     * @param bool $isStateMachine
     */
    public function setStateMachine(bool $isStateMachine)
    {
        $this->isStateMachine = $isStateMachine;
    }

    /**
     * @return bool
     */
    public function isStateMachine() : bool
    {
        return $this->isStateMachine;
    }

    /**
     * @return boolean
     */
    public function isSpellCheck() : bool
    {
        return $this->spellCheck;
    }

    /**
     * @param boolean $spellCheck
     */
    public function setSpellCheck($spellCheck) : void
    {
        $this->spellCheck = $spellCheck;
    }

    /**
     * @param bool $isBitwise
     */
    public function setBitMask($isBitwise) : void
    {
        $this->isBitWise = $isBitwise;
    }

    public function isBitMask() : bool
    {
        return $this->isBitWise;
    }

    /**
     * @return bool
     */
    public function isCached() : bool
    {
        return $this->isCached;
    }

    /**
     * @param bool $isCached
     */
    public function setIsCached(bool $isCached) : void
    {
        $this->isCached = $isCached;
    }

    /**
     * @return bool
     */
    public function isXFetch() : bool
    {
        return $this->isXFetch;
    }

    /**
     * @param bool $isXFetch
     */
    public function setIsXFetch(bool $isXFetch) : void
    {
        $this->isXFetch = $isXFetch;
    }

    /**
     * @return int
     */
    public function getCacheTtl() : int
    {
        return $this->cacheTtl;
    }

    /**
     * @param int $cacheTtl
     */
    public function setCacheTtl(int $cacheTtl) : void
    {
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @return float
     */
    public function getCacheBeta() : float
    {
        return $this->cacheBeta;
    }

    /**
     * @param float $cacheBeta
     */
    public function setCacheBeta(float $cacheBeta) : void
    {
        $this->cacheBeta = $cacheBeta;
    }

    /**
     * @return string
     */
    public function getCalledMethod(): string
    {
        return $this->calledMethod;
    }

    /**
     * @param string $calledMethod
     */
    public function setCalledMethod(string $calledMethod)
    {
        $this->calledMethod = $calledMethod;
    }
}