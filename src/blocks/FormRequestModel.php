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

    protected function setProperty($attrKey, $attrVal, $attrCnt)
    {
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::TAB_PSR4 .
                             PhpEntitiesInterface::TAB_PSR4
                             . PhpEntitiesInterface::QUOTES . $attrKey . PhpEntitiesInterface::QUOTES
                             . PhpEntitiesInterface::SPACE
                             . PhpEntitiesInterface::DOUBLE_ARROW .
                             PhpEntitiesInterface::SPACE;

        $this->sourceCode .= PhpEntitiesInterface::QUOTES;
        $cnt = count($attrVal);
        foreach($attrVal as $k => $v)
        {
            --$cnt;
            if($k === RamlInterface::RAML_REQUIRED && (bool) $v === false)
            {
                continue;
            }
            if($k === RamlInterface::RAML_REQUIRED && (bool) $v === true)
            {
                $this->sourceCode .= RamlInterface::RAML_REQUIRED;
            }
//            if($k === RamlInterface::RAML_DESCRIPTION)
//            {
//                $this->setComment($v);
//            }
            if($k === RamlInterface::RAML_TYPE)
            {
                $this->sourceCode .= $v;
            }
            if($k === RamlInterface::RAML_ENUM)
            {
                $this->sourceCode .= 'in:' . implode(',', $v);
            }
            if($k === RamlInterface::RAML_PATTERN)
            {
                $this->sourceCode .= 'regex:' . $v;
            }
            if($k === RamlInterface::RAML_STRING_MIN)
            {
                $this->sourceCode .= 'min:' . $v;
            }
            if($k === RamlInterface::RAML_STRING_MAX)
            {
                $this->sourceCode .= 'max:' . $v;
            }
            if($k === RamlInterface::RAML_INTEGER_MIN)
            {
                $this->sourceCode .= 'min:' . $v;
            }
            if($k === RamlInterface::RAML_INTEGER_MAX)
            {
                $this->sourceCode .= 'max:' . $v;
            }
//            if(isset($attrVal['errorMessage']))
//            {
//                $this->sourceCode .= ', "message" => "' . $attrVal['errorMessage'] . '"';
//            }
            if($cnt > 0 && $k !== RamlInterface::RAML_DESCRIPTION)
            {
                $this->sourceCode .= PhpEntitiesInterface::PIPE;
            }
        }
        $this->sourceCode .= PhpEntitiesInterface::QUOTES . PhpEntitiesInterface::COMMA;
        if($attrCnt > 0)
        {
            $this->sourceCode .= PHP_EOL;
        }
    }
}