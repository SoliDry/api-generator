<?php
namespace rjapi\helpers;


use rjapi\types\PhpInterface;

class MethodOptions
{
    private $modifier = PhpInterface::PHP_MODIFIER_PUBLIC;
    private $name = '';
    private $returnType = '';
    private $isStatic = false;
    private $params = [];

    /**
     * @return string
     */
    public function getModifier()
    {
        return $this->modifier;
    }

    /**
     * @param string $modifier
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return boolean
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /**
     * @param boolean $isStatic
     */
    public function setIsStatic($isStatic)
    {
        $this->isStatic = $isStatic;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}