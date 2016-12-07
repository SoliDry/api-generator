<?php
namespace rjapi\extension\yii2\raml\blocks;

use rjapi\extension\yii2\raml\controllers\SchemaController;
use yii\console\Controller;

class OutModels extends Models
{
    /** @var SchemaController generator */
    private $generator  = null;
    private $sourceCode = '';

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
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
            if(strtolower($method->getType()) === SchemaController::HTTP_METHOD_GET)
            {
                $this->sourceCode = SchemaController::PHP_OPEN_TAG . PHP_EOL;

                $this->sourceCode .= 'namespace ' . $this->generator->appDir . SchemaController::BACKSLASH . $this->generator->modulesDir . SchemaController::BACKSLASH . $this->generator->version . SchemaController::BACKSLASH . $this->generator->modelsFormDir .
                                     SchemaController::BACKSLASH . $this->generator->formsDir . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

                $this->sourceCode .= 'use tass\extension\json\api\forms\\' . SchemaController::DEFAULT_MODEL_OUT . SchemaController::SEMICOLON . PHP_EOL . PHP_EOL;

                $this->sourceCode .= 'class ' . SchemaController::FORM_BASE . SchemaController::FORM_PREFIX . $this->generator->controller
                                     . SchemaController::FORM_OUT . ' extends ' . SchemaController::DEFAULT_MODEL_OUT . ' ' . SchemaController::OPEN_BRACE . PHP_EOL;

                $this->setProps($method);
                $this->constructRules($method);
                $this->constructRelations($method);
                $this->createFormFiles();
            }
        }
    }

    /**
     * @param \Raml\Method $method
     */
    private function setProps($method)
    {
        // get any response - 200, 201, 400 but one
        $responses = $method->getResponses();
        foreach($responses as $code => $resp)
        {
            $bodies     = $resp->getBodies();
            $properties = $this->generator->getMethodProperties($bodies);
            if($properties === null)
            {
                continue;
            }

            // properties creation
            foreach($properties as $propKey => $propVal)
            {
                if($propKey !== 'type' && $propKey !== 'required' && is_array($propVal))
                {
                    $this->sourceCode .= SchemaController::TAB_PSR4 . 'public ' . SchemaController::DOLLAR_SIGN . $propKey . SchemaController::SPACE
                                         . SchemaController::EQUALS . SchemaController::SPACE . SchemaController::PHP_TYPES_NULL . SchemaController::SEMICOLON . PHP_EOL;
                }
            }
        }
        $this->sourceCode .= PHP_EOL;

        // set relation props
        $resp = $method->getResponse(SchemaController::RESPONSE_CODE_200);
        if(empty($resp))
        {
            $resp = $method->getResponse(SchemaController::RESPONSE_CODE_201); // if no 200 over there try to get 201
        }

        if(!empty($resp))
        {
            $bodies     = $resp->getBodies();
            $properties = $this->generator->getMethodProperties($bodies, true);

            // properties creation
            foreach($properties as $propKey => $propVal)
            {
                if($propKey !== 'type' && $propKey !== 'required' && is_array($propVal))
                {
                    $this->sourceCode .= SchemaController::TAB_PSR4 . 'public ' . SchemaController::DOLLAR_SIGN . $propKey . SchemaController::SPACE
                                         . SchemaController::EQUALS . SchemaController::SPACE . SchemaController::PHP_TYPES_NULL . SchemaController::SEMICOLON . PHP_EOL;
                }
            }

            $this->sourceCode .= PHP_EOL;
        }
    }

    /**
     * @param \Raml\Method $method
     *
     * @throws AttributesExceptio
     */
    private function constructRules($method)
    {
        $this->sourceCode .= SchemaController::TAB_PSR4 . 'public function rules()' . SchemaController::SPACE . SchemaController::OPEN_BRACE . PHP_EOL;

        $resp = $method->getResponse(SchemaController::RESPONSE_CODE_200);
        if(empty($resp))
        {
            $resp = $method->getResponse(SchemaController::RESPONSE_CODE_201); // if no 200 over there try to get 201
        }

        if(!empty($resp))
        {
            $bodies     = $resp->getBodies();
            $properties = $this->generator->getMethodProperties($bodies);

            // attrs validation
            $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . 'return ' . SchemaController::OPEN_BRACKET . PHP_EOL;
            // gather required fields
            $this->setRequired($properties);

            // gather types and constraints
            $this->setTypesAndConstraints($properties);

            $this->sourceCode .= PHP_EOL . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACKET . SchemaController::SEMICOLON . PHP_EOL;
        }

        $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACE;
    }

    /**
     * @param $attrs
     *
     * @internal param \Raml\Method $method
     */
    private function setRequired($attrs)
    {
        $keysCnt = 0;
        $reqKeys = '';
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
     * @param array $attrs
     *
     * @internal param \Raml\Method $method
     */
    private function setTypesAndConstraints($attrs)
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

//        $parameters = $method->getQueryParameters();
//        if(!empty($parameters))
//        {
//            $this->sourceCodeOut .= ', ' . PHP_EOL;
//            $paramsCnt = count($parameters);
//            /** @var \Raml\NamedParameter $param */
//            foreach($parameters as $param)
//            {
//                --$paramsCnt;
//                $this->sourceCodeOut .= ApiGenerator::TAB_PSR4 . ApiGenerator::TAB_PSR4 . ApiGenerator::TAB_PSR4 . ApiGenerator::OPEN_BRACKET . '"' . $param->getKey() . '" ';
//                $this->sourceCodeOut .= ', "' . $param->getType() . '"';
//                // TODO: make default patterns separately
//                if(!empty($param->getMinimum()))
//                {
//                    $this->sourceCodeOut .= ', "min" => "' . $param->getMinimum() . '"';
//                }
//                if(!empty($param->getMaximum()))
//                {
//                    $this->sourceCodeOut .= ', "max" => "' . $param->getMaximum() . '"';
//                }
//                if(!empty($param->getMinLength()))
//                {
//                    $this->sourceCodeOut .= ', "min" => "' . $param->getMinLength() . '"';
//                }
//                if(!empty($param->getMaxLength()))
//                {
//                    $this->sourceCodeOut .= ', "max" => "' . $param->getMaxLength() . '"';
//                }
//                $this->sourceCodeOut .= ApiGenerator::CLOSE_BRACKET;
//                if($paramsCnt > 0)
//                {
//                    $this->sourceCodeOut .= ', ' . PHP_EOL;
//                }
//            }
//        }
    }

    /**
     * @param \Raml\Method $method
     *
     * @throws \rjapi\extension\yii2\raml\exception\AttributesException
     */
    private function constructRelations($method)
    {
        $this->sourceCode .= PHP_EOL . PHP_EOL;
        $this->sourceCode .= SchemaController::TAB_PSR4 . 'public function relations()' . SchemaController::COLON . ' ' . SchemaController::PHP_TYPES_ARRAY . ' ' . SchemaController::OPEN_BRACE . PHP_EOL;

        $resp = $method->getResponse(SchemaController::RESPONSE_CODE_200);
        if(empty($resp))
        {
            $resp = $method->getResponse(SchemaController::RESPONSE_CODE_201); // if no 200 over there try to get 201
        }

        if(!empty($resp))
        {
            $bodies     = $resp->getBodies();
            $properties = $this->generator->getMethodProperties($bodies, true);

            // attrs validation
            $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . 'return ' . SchemaController::OPEN_BRACKET . PHP_EOL;

            $this->setRelations($properties);

            $this->sourceCode .= PHP_EOL . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACKET . SchemaController::SEMICOLON . PHP_EOL;
        }

        $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::CLOSE_BRACE;
        $this->sourceCode .= PHP_EOL . SchemaController::CLOSE_BRACE . PHP_EOL;
    }

    private function setRelations($attrs)
    {
        $cntAttrs = count($attrs);
        foreach($attrs as $attrKey => $attrVal)
        {
            --$cntAttrs;
            // determine attr
            if($attrKey !== 'type' && $attrKey !== 'required' && is_array($attrVal))
            {
                $this->sourceCode .= SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . SchemaController::TAB_PSR4 . '"' . $attrKey . '",';
                if($cntAttrs > 0)
                {
                    $this->sourceCode .= PHP_EOL;
                }
            }
        }
    }

    private function createFormFiles()
    {
        $fileFormOut = $this->generator->rootDir . $this->generator->modulesDir . SchemaController::SLASH . $this->generator->version . SchemaController::SLASH . $this->generator->modelsFormDir
                       . SchemaController::SLASH . $this->generator->formsDir . SchemaController::SLASH . SchemaController::FORM_BASE . SchemaController::FORM_PREFIX . $this->generator->controller
                       . SchemaController::FORM_OUT . SchemaController::PHP_EXT;

        $fpFormOut = fopen($fileFormOut, 'w');
        fwrite($fpFormOut, $this->sourceCode);
        fclose($fpFormOut);
    }

}