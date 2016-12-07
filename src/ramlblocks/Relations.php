<?php
namespace rjapi\extension\yii2\raml\ramlblocks;

use tass\extension\json\api\forms\BaseFormResourceIn;
use rjapi\extension\yii2\raml\controllers\TypesController;
use yii\console\Controller;
use yii\helpers\StringHelper;

class Relations extends Models
{
    /** @var TypesController $generator */
    private   $generator  = null;
    protected $sourceCode = '';

    private $additionalProps = [];

    public function __construct(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState(Controller $generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        foreach($this->generator->types as $typeKey => $type)
        {
            if(strpos($typeKey, TypesController::CUSTOM_TYPES_RELATIONSHIPS) !== false)
            {
                $object           = str_replace(TypesController::CUSTOM_TYPES_RELATIONSHIPS, '', $typeKey);
                $this->sourceCode = TypesController::PHP_OPEN_TAG . PHP_EOL;

                $this->sourceCode .= TypesController::PHP_NAMESPACE . ' ' . $this->generator->appDir .
                                     TypesController::BACKSLASH . $this->generator->modulesDir .
                                     TypesController::BACKSLASH . $this->generator->version .
                                     TypesController::BACKSLASH . $this->generator->modelsFormDir .
                                     TypesController::BACKSLASH . $this->generator->formsDir .
                                     TypesController::SEMICOLON . PHP_EOL . PHP_EOL;

                $fullRelation     = BaseFormResourceIn::class;
                $baseRelationName = StringHelper::basename($fullRelation);

                $this->sourceCode .= TypesController::PHP_USE . ' ' . $fullRelation . TypesController::SEMICOLON .
                                     PHP_EOL . PHP_EOL;

                $this->sourceCode .= TypesController::PHP_CLASS . ' ' . TypesController::FORM_BASE .
                                     TypesController::FORM_PREFIX . $object .
                                     TypesController::FORM_IN . ' ' . TypesController::PHP_EXTENDS . ' '
                                     . $baseRelationName . ' ' . TypesController::OPEN_BRACE . PHP_EOL;

                $this->additionalProps = [
                    'id' => [
                        'required' => true,
                        'type'     => 'integer'
                    ],
                ];

                $objectAttrs = $object . TypesController::CUSTOM_TYPES_ATTRIBUTES;
                $this->setProps($objectAttrs);
                $this->constructRules($objectAttrs);

                if(!empty($type[TypesController::RAML_PROPS][TypesController::RAML_DATA][TypesController::RAML_PROPS]
                [TypesController::RAML_RELATIONSHIPS])
                )
                {
                    $this->constructRelations(
                        $type[TypesController::RAML_PROPS][TypesController::RAML_DATA]
                        [TypesController::RAML_PROPS][TypesController::RAML_RELATIONSHIPS]
                    );
                }
                $this->sourceCode .= PHP_EOL . TypesController::CLOSE_BRACE . PHP_EOL;
                $fileFormIn = $this->generator->rootDir . $this->generator->modulesDir . TypesController::SLASH .
                              $this->generator->version . TypesController::SLASH . $this->generator->modelsFormDir
                              . TypesController::SLASH . $this->generator->formsDir . TypesController::SLASH .
                              TypesController::FORM_BASE . TypesController::FORM_PREFIX .
                              $object . TypesController::FORM_IN . TypesController::PHP_EXT;
                FileManager::createFile($fileFormIn, $this->sourceCode);
            }
        }
    }

    /**
     * @internal param Method $method
     *
     * @internal param array $methods
     */
    private function setProps($objectAttrs)
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= TypesController::TAB_PSR4 . 'public ' . TypesController::DOLLAR_SIGN . $prop .
                                     TypesController::SPACE
                                     . TypesController::EQUALS . TypesController::SPACE .
                                     TypesController::PHP_TYPES_NULL . TypesController::SEMICOLON . PHP_EOL;
            }
        }

        // properties creation
        foreach($this->generator->types[$objectAttrs][TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            $this->sourceCode .= TypesController::TAB_PSR4 . 'public ' . TypesController::DOLLAR_SIGN . $attrKey .
                                 TypesController::SPACE
                                 . TypesController::EQUALS . TypesController::SPACE .
                                 TypesController::PHP_TYPES_NULL . TypesController::SEMICOLON . PHP_EOL;
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function constructRules($objectAttrs)
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . 'public function rules() ' . TypesController::OPEN_BRACE .
                             PHP_EOL;

        // attrs validation
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . 'return ' .
                             TypesController::OPEN_BRACKET . PHP_EOL;
        // gather required fields
        $this->setRequired($objectAttrs);
        // gather types and constraints
        $this->setTypesAndConstraints($objectAttrs);

        $this->sourceCode .= PHP_EOL . TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                             TypesController::CLOSE_BRACKET . TypesController::SEMICOLON . PHP_EOL;
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::CLOSE_BRACE;
    }

    /**
     * @internal param Method $method
     * @internal param $attrs
     */
    private function setRequired($objectAttrs)
    {
        $keysCnt = 0;
        $reqKeys = '';

        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                if((bool) $propVal['required'] === true)
                {
                    if($keysCnt > 0)
                    {
                        $reqKeys .= ', ';
                    }
                    $reqKeys .= '"' . $prop . '"';
                    ++$keysCnt;
                }
            }
        }

        foreach($this->generator->types[$objectAttrs][TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            if(is_array($attrVal))
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
            $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                                 TypesController::OPEN_BRACKET . TypesController::OPEN_BRACKET
                                 . $reqKeys . TypesController::CLOSE_BRACKET;
            $this->sourceCode .= ', "required"';
            $this->sourceCode .= TypesController::CLOSE_BRACKET;
            $this->sourceCode .= ', ' . PHP_EOL;
        }
    }

    /**
     * @internal param Method $method
     * @internal param array $attrs
     */
    private function setTypesAndConstraints($objectAttrs)
    {
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                                     TypesController::TAB_PSR4 . TypesController::OPEN_BRACKET . '"' . $prop . '" ';
                $this->setProperty($propVal);
                $this->sourceCode .= TypesController::CLOSE_BRACKET;
                $this->sourceCode .= ', ' . PHP_EOL;
            }
        }

        $attrsCnt = count($this->generator->types[$objectAttrs][TypesController::RAML_PROPS]);
        foreach($this->generator->types[$objectAttrs][TypesController::RAML_PROPS] as $attrKey => $attrVal)
        {
            --$attrsCnt;
            // determine attr
            if(is_array($attrVal))
            {
                $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                                     TypesController::TAB_PSR4 . TypesController::OPEN_BRACKET . '"' . $attrKey .
                                     '" ';
                $this->setProperty($attrVal);
                $this->sourceCode .= TypesController::CLOSE_BRACKET;
                if($attrsCnt > 0)
                {
                    $this->sourceCode .= ', ' . PHP_EOL;
                }
            }
        }
    }

    private function constructRelations($relationTypes)
    {
        $this->sourceCode .= PHP_EOL . PHP_EOL;
        $this->sourceCode .= TypesController::TAB_PSR4 . 'public function relations()' . TypesController::COLON .
                             ' ' . TypesController::PHP_TYPES_ARRAY . ' ' . TypesController::OPEN_BRACE . PHP_EOL;

        // attrs validation
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . 'return ' .
                             TypesController::OPEN_BRACKET . PHP_EOL;

        $rels = explode('|', str_replace('[]', '', $relationTypes));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(TypesController::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(!empty($rels[$k + 1]))
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->sourceCode .= PHP_EOL . TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                             TypesController::CLOSE_BRACKET . TypesController::SEMICOLON . PHP_EOL;

        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::CLOSE_BRACE;
    }

    private function setRelations($relationTypes)
    {
        $this->sourceCode .= TypesController::TAB_PSR4 . TypesController::TAB_PSR4 . TypesController::TAB_PSR4 .
                             '"' . $relationTypes . '",';
    }
}