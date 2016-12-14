<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 28/10/2016
 * Time: 10:56
 */

namespace rjapi\blocks;

use Raml\Method;

abstract class FormRequestModel
{
    private function setProps(Method $method)
    {
    }

    private function constructRules(Method $method)
    {
    }

    private function setRequired(Method $method, array $attrs)
    {
    }

    private function setTypesAndConstraints(Method $method, array $attrs)
    {
    }

    private function createFormFiles(Method $method)
    {
    }

    protected function setProperty($attrVal)
    {
        if(isset($attrVal['type']))
        {
            $this->sourceCode .= ', "' . $attrVal['type'] . '"';
        }
        if(isset($attrVal['enum']))
        {
            $this->sourceCode .= ', "in", "range" => ["' . implode('", "', $attrVal['enum']) . '"]';
        }
        if(isset($attrVal['pattern']))
        {
            $this->sourceCode .= ', "pattern" => "' . $attrVal['pattern'] . '"';
        }
        if(isset($attrVal['minLength']))
        {
            $this->sourceCode .= ', "min" => "' . $attrVal['minLength'] . '"';
        }
        if(isset($attrVal['minLength']))
        {
            $this->sourceCode .= ', "max" => "' . $attrVal['maxLength'] . '"';
        }
        if(isset($attrVal['minimum']))
        {
            $this->sourceCode .= ', "min" => "' . $attrVal['minimum'] . '"';
        }
        if(isset($attrVal['maximum']))
        {
            $this->sourceCode .= ', "max" => "' . $attrVal['maximum'] . '"';
        }
        if(isset($attrVal['errorMessage']))
        {
            $this->sourceCode .= ', "message" => "' . $attrVal['errorMessage'] . '"';
        }
    }
}