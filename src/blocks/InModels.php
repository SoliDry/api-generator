<?php
namespace rjapi\extension\yii2\raml\blocks;

use Raml\Method;
use rjapi\extension\yii2\raml\controllers\SchemaController;
use yii\console\Controller;

class InModels extends Models
{
    /** @var SchemaController generator */
    private $generator  = null;
    private $sourceCode = '';
    private $methods    = null;

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
        $this->methods = new Methods();
    }

    public function setCodeState(Controller $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param \Raml\Method[] $methods
     */
    public function createFormModel($methods)
    {
        foreach($methods as $method)
        {
            $this->sourceCode = SchemaController::PHP_OPEN_TAG . PHP_EOL;

            $this->sourceCode .= SchemaController::PHP_NAMESPACE . ' ' . $this->generator->appDir . SchemaController::BACKSLASH . $this->generator->modulesDir . SchemaController::BACKSLASH . $this->generator->version . SchemaController::BACKSLASH . $this->generator->modelsFormDir .
                                 SchemaController::BACKSLASH . $this->generator->formsDir . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

            $this->sourceCode .= SchemaController::PHP_USE . ' tass\extension\json\api\forms\\' . SchemaController::DEFAULT_MODEL_IN . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

            $this->sourceCode .= SchemaController::PHP_CLASS . ' ' . SchemaController::FORM_PREFIX . $this->generator->controller . SchemaController::FORM_ACTION . $this->generator->actions[strtolower($method->getType())]
                                 . SchemaController::FORM_IN . ' ' . SchemaController::PHP_EXTENDS . ' ' . SchemaController::DEFAULT_MODEL_IN . ' ' . SchemaController::OPEN_BRACE . PHP_EOL;

            $this->setProps($method);
            $this->constructRules($method);

            $this->sourceCode .= PHP_EOL . SchemaController::CLOSE_BRACE . PHP_EOL;
            $this->createFormFiles($method);
        }
    }

    /**
     * @param \Raml\Method $method
     *
     * @throws AttributesException
     * @internal param \Raml\Method[] $methods
     */
    private function setProps($method)
    {
        $parameters = $method->getQueryParameters();

        if(!empty($parameters))
        {
            /** @var \Raml\NamedParameter $param */
            foreach($parameters as $param)
            {
                $this->sourceCode .= SchemaController::TAB_PSR4 . 'public ' . SchemaController::DOLLAR_SIGN . $param->getKey() . SchemaController::SEMICOLON . PHP_EOL;
            }
        }

        $bodies = $method->getBodies();
        $props  = $this->generator->getMethodProperties($bodies);
        if($props !== null)
        {
            // properties creation
            foreach($props as $attrKey => $attrVal)
            {
                if($attrKey !== 'type' && $attrKey !== 'required' && is_array($attrVal))
                {
                    $this->sourceCode .= SchemaController::TAB_PSR4 . 'public ' . SchemaController::DOLLAR_SIGN . $attrKey . SchemaController::SPACE
                                         . SchemaController::EQUALS . SchemaController::SPACE . SchemaController::PHP_TYPES_NULL . SchemaController::SEMICOLON . PHP_EOL;
                }
            }

            if(!empty($this->uriNamedParams))
            {
                /** @var \Raml\NamedParameter $uriParam */
                foreach($this->uriNamedParams as $uriParam)
                {
                    $this->sourceCode .= SchemaController::TAB_PSR4 . 'public ' . SchemaController::DOLLAR_SIGN . $uriParam->getKey() . SchemaController::SEMICOLON . PHP_EOL;
                }
            }

            $this->sourceCode .= PHP_EOL;
        }
    }

    /**
     * @param \Raml|Method $method
     *
     * @throws \rjapi\extension\yii2\raml\exception\AttributesException
     */
    private function constructRules($method)
    {
        $this->sourceCode .= SchemaController::TAB_PSR4 . 'public function rules() ' . SchemaController::OPEN_BRACE . PHP_EOL;

        $bodies = $method->getBodies();
        $props  = $this->generator->getMethodProperties($bodies);
        // attrs validation
        $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . 'return ' . SchemaController::OPEN_BRACKET . PHP_EOL;
        // gather required fields
        $this->setRequired($method, $props);
        // gather types and constraints
        $this->setTypesAndConstraints($method, $props);

        $this->sourceCode .= PHP_EOL . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACKET . SchemaController::SEMICOLON . PHP_EOL;
        $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACE;
    }

    /**
     * @param \Raml\Method $method
     * @param              $attrs
     */
    private function setRequired($method, $attrs)
    {
        $keysCnt = 0;
        $reqKeys = '';
        if($attrs !== null)
        {
            foreach($attrs as $attrKey => $attrVal)
            {
                // determine attr
                if($attrKey !== 'type' && $attrKey !== 'required' && is_array($attrVal))
                {
                    if(isset($attrVal['required']) && (bool) $attrVal['required'] === true)
                    {
                        if($keysCnt > 0)
                        {
                            $reqKeys .= ', ';
                        }
                        $reqKeys .= '"' . $attrKey . '"';
                        ++$keysCnt;
                    }
                }
            }
        }

        $parameters = $method->getQueryParameters();
        if(!empty($parameters))
        {
            /** @var \Raml\NamedParameter $param */
            foreach($parameters as $param)
            {
                if($param->isRequired())
                {
                    if($keysCnt > 0)
                    {
                        $reqKeys .= ', ';
                    }
                    $reqKeys .= '"' . $param->getDisplayName() . '"';
                    ++$keysCnt;
                }
            }
        }

        if($keysCnt > 0)
        {
            $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::OPEN_BRACKET . SchemaController::OPEN_BRACKET
                                 . $reqKeys . SchemaController::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= SchemaController::CLOSE_BRACKET;
            $this->sourceCode .= ', ' . PHP_EOL;
        }
    }

    /**
     * @param \Raml\Method $method
     * @param array        $attrs
     */
    private function setTypesAndConstraints($method, $attrs)
    {
        if($attrs !== null)
        {
            $attrsCnt = count($attrs);
            foreach($attrs as $attrKey => $attrVal)
            {
                --$attrsCnt;
                // determine attr
                if($attrKey !== 'type' && $attrKey !== 'required' && is_array($attrVal))
                {
                    $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::OPEN_BRACKET . '"' . $attrKey . '" ';
                    if(isset($attrVal['type']))
                    {
                        $this->sourceCode .= ', "' . $attrVal['type'] . '"';
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
                    $this->sourceCode .= SchemaController::CLOSE_BRACKET;
                    if($attrsCnt > 0)
                    {
                        $this->sourceCode .= ', ' . PHP_EOL;
                    }
                }
            }
        }

        $parameters = $method->getQueryParameters();
        if(!empty($parameters))
        {
            if($attrs !== null)
            {
                $this->sourceCode .= ', ' . PHP_EOL;
            }
            $paramsCnt = count($parameters);
            /** @var \Raml\NamedParameter $param */
            foreach($parameters as $param)
            {
                --$paramsCnt;
                $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::OPEN_BRACKET . '"' . $param->getKey() . '" ';
                $this->sourceCode .= ', "' . $param->getType() . '"';
                // TODO: make default patterns separately
//                $this->sourceCode .= ', "pattern" => "' . $param->getMatchPattern() . '"';
                if(!empty($param->getMinimum()))
                {
                    $this->sourceCode .= ', "min" => "' . $param->getMinimum() . '"';
                }
                if(!empty($param->getMaximum()))
                {
                    $this->sourceCode .= ', "max" => "' . $param->getMaximum() . '"';
                }
                if(!empty($param->getMinLength()))
                {
                    $this->sourceCode .= ', "min" => "' . $param->getMinLength() . '"';
                }
                if(!empty($param->getMaxLength()))
                {
                    $this->sourceCode .= ', "max" => "' . $param->getMaxLength() . '"';
                }
                $this->sourceCode .= SchemaController::CLOSE_BRACKET;
                if($paramsCnt > 0)
                {
                    $this->sourceCode .= ', ' . PHP_EOL;
                }
            }
        }

        if(!empty($this->uriNamedParams))
        {
            $this->sourceCode .= ', ' . PHP_EOL;
            $paramsCnt = count($this->uriNamedParams);
            /** @var \Raml\NamedParameter $uriParam */
            foreach($this->uriNamedParams as $uriParam)
            {
                --$paramsCnt;
                $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::OPEN_BRACKET . '"' . $uriParam->getKey() . '" ';
                $this->sourceCode .= ', "' . $uriParam->getType() . '"';
                // TODO: make default patterns separately
//                $this->sourceCode .= ', "pattern" => "' . $uriParam->getMatchPattern() . '"';
                if(!empty($uriParam->getMinimum()))
                {
                    $this->sourceCode .= ', "min" => "' . $uriParam->getMinimum() . '"';
                }
                if(!empty($uriParam->getMaximum()))
                {
                    $this->sourceCode .= ', "max" => "' . $uriParam->getMaximum() . '"';
                }
                if(!empty($uriParam->getMinLength()))
                {
                    $this->sourceCode .= ', "min" => "' . $uriParam->getMinLength() . '"';
                }
                if(!empty($uriParam->getMaxLength()))
                {
                    $this->sourceCode .= ', "max" => "' . $uriParam->getMaxLength() . '"';
                }
                $this->sourceCode .= SchemaController::CLOSE_BRACKET;
                if($paramsCnt > 0)
                {
                    $this->sourceCode .= ', ' . PHP_EOL;
                }
            }
        }
    }

    /**
     * @param \Raml\Method $method
     */
    private function createFormFiles($method)
    {
        $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . SchemaController::SLASH . $this->generator->version . SchemaController::SLASH . $this->generator->modelsFormDir
                      . SchemaController::SLASH . $this->generator->formsDir . SchemaController::SLASH . SchemaController::FORM_PREFIX . $this->generator->controller
                      . SchemaController::FORM_ACTION . $this->generator->actions[strtolower($method->getType())] . SchemaController::FORM_IN . SchemaController::PHP_EXT;

        $fpFormIn = fopen($fileFormIn, 'w');
        fwrite($fpFormIn, $this->sourceCode);
        fclose($fpFormIn);
    }

}